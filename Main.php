<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Feast;

use Feast\Enums\ResponseCode;
use Feast\Exception\Error404Exception;
use Feast\Exception\ServerFailureException;
use Feast\Exception\ThrottleException;
use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\ControllerInterface;
use Feast\Interfaces\ErrorLoggerInterface;
use Feast\Interfaces\MainInterface;
use Feast\Interfaces\RequestInterface;
use Feast\Interfaces\ResponseInterface;
use Feast\Interfaces\RouterInterface;
use Feast\ServiceContainer\NotFoundException;
use Feast\ServiceContainer\ServiceContainer;
use Feast\ServiceContainer\ServiceContainerItemInterface;

/**
 * @since 1.0
 * @version 1.0
 *
 * Main entry point of the Application.
 */
class Main implements MainInterface
{

    final public const RUN_AS_WEBAPP = 'webapp';
    final public const RUN_AS_CLI = 'cli';
    final public const FRAMEWORK_ROOT = __DIR__;

    private array $plugins = [];
    private ErrorLoggerInterface $logger;
    protected ?ControllerInterface $ranController = null;
    protected string $routePath = '';
    protected bool $isJson = false;

    /**
     * Main constructor.
     *
     * @param ServiceContainer $serviceContainer
     * @param string $runAs
     * @throws NotFoundException
     */
    public function __construct(protected ServiceContainer $serviceContainer, protected string $runAs)
    {
        /** @var ConfigInterface $config */
        $config = $this->serviceContainer->get(ConfigInterface::class);
        ini_set('display_errors', $config->getSetting('showerrors') ? 'true' : 'false');
        $this->logger = $this->serviceContainer->get(ErrorLoggerInterface::class);
    }

    protected function buildCliArguments(RouterInterface $router): void
    {
        /** @var array{argv:array<string>} $_SERVER */
        $router->buildRouteForRequestUrl('CLI/index/index');

        $_SERVER['argv'][0] = str_replace(':', DIRECTORY_SEPARATOR, $_SERVER['argv'][0]);
        $path = 'CLI' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $_SERVER['argv']);
        $router->buildCliArguments($path);
    }

    protected function buildWebArguments(RouterInterface $router): void
    {
        /** @var ?string $path */
        $path = $_SERVER['REQUEST_URI'] ?? null;
        if (isset($path)) {
            $router->buildRouteForRequestUrl(parse_url($path, PHP_URL_PATH));
        } else {
            $router->buildRouteForRequestUrl('index/index');
        }

        $router->assignArguments($_GET);
        $router->assignArguments($_POST);
        $router->assignArguments($_FILES);
    }

    protected function buildInitialArguments(RouterInterface $router): void
    {
        if ($this->runAs == self::RUN_AS_WEBAPP) {
            $this->buildWebArguments($router);
        } elseif ($this->runAs == self::RUN_AS_CLI) {
            $this->buildCliArguments($router);
        }
    }

    /**
     * Main Program execution
     *
     * @throws NotFoundException
     * @throws Error404Exception
     * @throws ServerFailureException|\ReflectionException
     */
    public function main(): void
    {
        // Fetch dependencies from service container
        $view = $this->serviceContainer->get(View::class);
        $router = $this->serviceContainer->get(RouterInterface::class);
        $config = $this->serviceContainer->get(ConfigInterface::class);
        $request = $this->serviceContainer->get(RequestInterface::class);
        $response = $this->serviceContainer->get(ResponseInterface::class);

        $view->setTitle((string)$config->getSetting('title'));

        if ($config->getSetting('buildroutes') === true && $this->runAs === self::RUN_AS_WEBAPP) {
            $router->buildRoutes();
        }
        require_once(APPLICATION_ROOT . 'bootstrap.php');

        $this->loadPlugins($config, $request);

        $this->buildInitialArguments($router);
        try {
            $this->runPlugins('preDispatch', $router, $request);
            if ($this->runAs == self::RUN_AS_WEBAPP) {
                $this->isJson = $request->getArgumentString('format') == 'json';

                $redirectPath = $response->getRedirectPath();
                if (is_string($redirectPath)) {
                    header('Location:' . $redirectPath);
                    return;
                }
            }

            $this->runRequest($router, $request, $response, $view);
        } catch (Error404Exception $exception) {
            if ($this->runAs === self::RUN_AS_WEBAPP) {
                $this->handle404Exception($config, $exception);
            } else {
                throw $exception;
            }
        } catch (\Exception | ServerFailureException $exception) {
            $this->handleExceptions($exception, $response, $config);
        }
    }

    protected function runControllerLoop(
        RouterInterface $router,
        RequestInterface $request,
        ResponseInterface $response
    ): void {
        do {
            $router->forward(false);
            $controllerName = $router->getControllerClass();
            $actionName = $router->getActionMethodName();
            /** @var class-string<ControllerInterface> $controllerClass */
            $controllerClass = $router->getControllerFullyQualifiedName();
            $moduleName = $router->getModuleName();
            $this->routePath = $moduleName != 'Default' ? '/Modules/' . $moduleName . '/' : '';

            $this->checkForControllerAndAction($controllerClass, $controllerName, $actionName);
            /* @var $controller ControllerInterface */
            $arguments = $this->buildDynamicParameters($controllerClass, '__construct', $request, false);
            /**
             * @var HttpController $controller
             * @psalm-suppress UnsafeInstantiation
             */
            $controller = new $controllerClass(...$arguments);
            $allowRunning = $controller->init();

            if ($allowRunning) {
                $this->runAction($controller, $actionName, $request);
            } else {
                throw new ServerFailureException('Request not allowed.');
            }
        } while ($router->forwarded());

        $this->ranController = $controller;
        $this->isJson = $this->isJson || $this->ranController->alwaysJson(
                $router->getActionName()
            );// Mark as json if it is
        if ($this->isJson && $this->ranController->jsonAllowed()) {
            $response->setJson();
        }
    }

    /**
     * @param ControllerInterface $controller
     * @param string $actionName
     * @param RequestInterface $request
     * @throws \ReflectionException
     */
    protected function runAction(ControllerInterface $controller, string $actionName, RequestInterface $request): void
    {
        $arguments = $this->buildDynamicParameters($controller, $actionName, $request);
        // Argument gathering

        $controller->$actionName(...$arguments);
    }

    /**
     * @param ControllerInterface|Plugin|class-string $controller
     * @param string $function
     * @param RequestInterface $request
     * @param bool $buildUnknown
     * @return array
     * @throws \ReflectionException
     */
    protected function buildDynamicParameters(
        ControllerInterface|Plugin|string $controller,
        string $function,
        RequestInterface $request,
        bool $buildUnknown = true
    ): array {
        // Argument gathering
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod($function);
        $argumentCount = $method->getNumberOfParameters();

        if ($argumentCount === 0) {
            return [];
        } else {
            $arguments = $method->getParameters();

            return $this->buildArguments($arguments, $request, $buildUnknown);
        }
    }

    /**
     * @param array $arguments
     * @param RequestInterface $request
     * @param bool $buildUnknown
     * @return array
     */
    protected function buildArguments(array $arguments, RequestInterface $request, bool $buildUnknown = true): array
    {
        /** @var array<mixed> $return */
        $return = [];
        /** @var \ReflectionParameter $argument */
        foreach ($arguments as $argument) {
            $this->buildArgument($request, $argument, $buildUnknown, $return);
        }
        if (!empty($return) && is_array($return[count($return) - 1])) {
            /** @var array $arrayValues */
            $arrayValues = array_pop($return);
            /** @var string|int|bool|float|object|null $value */
            foreach ($arrayValues as $value) {
                $return[] = $value;
            }
        }

        return $return;
    }

    protected function buildArgument(
        RequestInterface $request,
        \ReflectionParameter $argument,
        bool $buildUnknown,
        array &$return
    ): void {
        /** @var ?\ReflectionNamedType $type */
        $type = $argument->getType();
        if ($type !== null) {
            $argumentType = $type->getName();
            /** @var string|int|float|bool|null|object $default */
            $default = $argument->isOptional() && $argument->isDefaultValueAvailable() ? $argument->getDefaultValue(
            ) : null;

            if (is_subclass_of($argumentType, BaseModel::class)) {
                $this->buildBaseModelArgument($argumentType, $request, $argument, $return);
            } elseif (is_subclass_of($argumentType, BaseMapper::class)) {
                $mapper = new $argumentType();
                $return[] = $mapper;
            } elseif (is_subclass_of($argumentType, ServiceContainerItemInterface::class)) {
                $return[] = $this->serviceContainer->get($argumentType);
            } elseif ($buildUnknown) {
                $this->buildUnknownArgument($argument, $argumentType, $default, $request, $return);
            }
        }
    }

    /**
     * Load all plugins
     *
     * @param ConfigInterface $config
     * @param RequestInterface $request
     * @throws \ReflectionException
     */
    protected function loadPlugins(ConfigInterface $config, RequestInterface $request): void
    {
        $plugins = [];
        if ($config->getSetting('plugin')) {
            $pluginList = (array)$config->getSetting('plugin');

            /** @var class-string<Plugin> $plugin */
            foreach ($pluginList as $plugin) {
                $arguments = $this->buildDynamicParameters($plugin, '__construct', $request, false);
                /** @psalm-suppress UnsafeInstantiation */
                $plugins[] = new $plugin(...$arguments);
            }
        }
        $this->plugins = $plugins;
    }

    /**
     * Run all plugins for the set method
     *
     * @param string $methodName
     * @param RouterInterface $router
     * @param RequestInterface $request
     * @throws \ReflectionException
     */
    protected function runPlugins(string $methodName, RouterInterface $router, RequestInterface $request): void
    {
        if ($this->runAs === self::RUN_AS_CLI) {
            $methodName = 'CLI' . ucfirst($methodName);
        }

        /** @var Plugin $plugin */
        foreach ($this->plugins as $plugin) {
            if (method_exists($plugin, $methodName)) {
                $plugin->init($router);
                $arguments = $this->buildDynamicParameters($plugin, $methodName, $request, false);
                $plugin->$methodName(...$arguments);
            }
        }
    }

    /**
     * @param class-string $argumentType
     * @param RequestInterface $request
     * @param \ReflectionParameter $argument
     * @param array $return
     * @throws \ReflectionException
     */
    protected function buildBaseModelArgument(
        string $argumentType,
        RequestInterface $request,
        \ReflectionParameter $argument,
        array &$return
    ): void {
        $reflected = new \ReflectionClass($argumentType);
        /** @var class-string<BaseMapper> $mapperName */
        $mapperName = $reflected->getConstant('MAPPER_NAME');
        /** @var BaseMapper $mapper */
        $mapper = new $mapperName();
        $argumentValue = $request->getArgumentString($argument->getName());
        if ($argumentValue === null) {
            if ($argument->allowsNull()) {
                $return[] = null;
            }
            return;
        }
        $return[] = $mapper->findByPrimaryKey($argumentValue, true);
    }

    protected function buildUnknownArgument(
        \ReflectionParameter $argument,
        string $argumentType,
        float|object|bool|int|string|null $default,
        RequestInterface $request,
        array &$return
    ): void {
        $argumentName = $argument->getName();
        if ($argument->isVariadic() || $argumentType === 'array') {
            $this->buildArrayArgument($default, $request, $argumentName, $argumentType, $return);
        } elseif ($argumentType === 'int') {
            $this->buildIntArgument($default, $request, $argumentName, $return);
        } elseif ($argumentType === 'float') {
            $this->buildFloatArgument($default, $request, $argumentName, $return);
        } elseif ($argumentType === 'bool') {
            $this->buildBoolArgument($default, $request, $argumentName, $return);
        } elseif ($argumentType === Date::class) {
            $return[] = $request->getArgumentDate($argumentName);
        } else {
            $this->buildStringArgument($default, $request, $argumentName, $return);
        }
    }

    protected function buildArrayArgument(
        float|object|bool|int|string|null $default,
        RequestInterface $request,
        string $argumentName,
        string $argumentType,
        array &$return
    ): void {
        if (!is_array($default)) {
            $default = [$default];
        }
        $return[] = $request->getArgumentArray($argumentName, $default, $argumentType);
    }

    protected function buildIntArgument(
        float|object|bool|int|string|null $default,
        RequestInterface $request,
        string $argumentName,
        array &$return
    ): void {
        if (is_int($default) === false) {
            $default = null;
        }
        $return[] = $request->getArgumentInt($argumentName, $default);
    }

    protected function buildFloatArgument(
        float|object|bool|int|string|null $default,
        RequestInterface $request,
        string $argumentName,
        array &$return
    ): void {
        if (is_float($default) === false) {
            $default = null;
        }
        $return[] = $request->getArgumentFloat($argumentName, $default);
    }

    protected function buildBoolArgument(
        float|object|bool|int|string|null $default,
        RequestInterface $request,
        string $argumentName,
        array &$return
    ): void {
        if (is_bool($default) === false) {
            $default = null;
        }
        $return[] = $request->getArgumentBool($argumentName, $default);
    }

    protected function buildStringArgument(
        float|object|bool|int|string|null $default,
        RequestInterface $request,
        string $argumentName,
        array &$return
    ): void {
        if (is_string($default) === false) {
            $default = null;
        }
        $return[] = $request->getArgumentString($argumentName, $default);
    }

    protected function handle404Exception(
        ConfigInterface $config,
        Error404Exception $exception
    ): void {
        /** @var string|null $errorUrl */
        $errorUrl = $config->getSetting('error.http404.url', 'error/fourohfour');
        if (is_string($errorUrl)) {
            header('Location:/' . $errorUrl, true, ResponseCode::HTTP_CODE_302->value);
            return;
        }
        throw $exception;
    }

    protected function handleExceptions(
        \Exception|ServerFailureException $exception,
        ResponseInterface $response,
        ConfigInterface $config
    ): void {
        if ($exception instanceof ThrottleException) {
            $response->sendResponseCode();
            $errorUrl = (string)$config->getSetting('error.throttle.url', 'error/rate-limit');
            header('Location:/' . $errorUrl);
            return;
        }
        if ($this->runAs === self::RUN_AS_WEBAPP) {
            /** @psalm-suppress MissingFile */
            include(APPLICATION_ROOT . 'Views' . DIRECTORY_SEPARATOR . 'Error' . DIRECTORY_SEPARATOR . 'server-failure.phtml');
        }
        $this->logger->exceptionHandler($exception, true);
    }

    protected function runRequest(
        RouterInterface $router,
        RequestInterface $request,
        ResponseInterface $response,
        View $view
    ): void {
        $this->runControllerLoop($router, $request, $response);

        // Run Plugins
        $this->runPlugins('postDispatch', $router, $request);
        if ($this->runAs === self::RUN_AS_WEBAPP) {
            $response->sendResponse($view, $router, $this->routePath);
        }
    }

    protected function checkForControllerAndAction(
        string $controllerClass,
        string $controllerName,
        string $actionName
    ): void {
        if (!class_exists($controllerClass)) {
            throw new Error404Exception('Controller ' . $controllerName . ' does not exist!');
        }
        if (!method_exists($controllerClass, $actionName)) {
            throw new Error404Exception('Action ' . $actionName . ' does not exist!');
        }
    }

}
