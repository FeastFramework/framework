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

namespace Feast\Router;

use Exception;
use Feast\Attributes\Param;
use Feast\Attributes\Path;
use Feast\Enums\ParamType;
use Feast\Enums\RequestMethod;
use Feast\Exception\Error404Exception;
use Feast\Exception\NotFoundException;
use Feast\Exception\RouteException;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\RequestInterface;
use Feast\Interfaces\RouterInterface;
use Feast\Main;
use Feast\NameHelper;
use Feast\ServiceContainer;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;
use ReflectionException;
use ReflectionMethod;
use stdClass;

class Router implements ServiceContainerItemInterface, RouterInterface
{
    use DependencyInjected;

    private stdClass $routes;
    private string $controller = '';
    private string $action = '';
    private string $controllerName = '';
    private string $actionName = '';
    private string $module = 'Default';
    private string $routeName = '';
    private ?string $redirectPath = null;
    private bool $reloop = false;
    private bool $isInternal = false;
    private array $routeNameMap = [];
    private bool $fromCache = false;

    public function __construct(private string $runAs = Main::RUN_AS_WEBAPP)
    {
        $this->checkInjected();
        $routes = new stdClass();
        $routes->GET = new stdClass();
        $routes->PATCH = new stdClass();
        $routes->DELETE = new stdClass();
        $routes->PUT = new stdClass();
        $routes->POST = new stdClass();

        $this->routes = $routes;
    }

    /**
     * Update the "runAs". Needed to properly route in case of cached router.
     *
     * @param string $runAs
     */
    public function setRunAs(string $runAs): void
    {
        $this->runAs = $runAs;
    }

    /**
     * Get the "runAs".
     *
     * @return string
     */
    public function getRunAs(): string
    {
        return $this->runAs;
    }

    /**
     * Get route based on the passed in arguments.
     *
     * @param string $arguments
     * @return void
     * @throws ServiceContainer\NotFoundException
     */
    public function buildRouteForRequestUrl(string $arguments): void
    {
        $arguments = $this->cleanArguments($arguments);
        if ($this->checkAndBuildNamedRoute($arguments)) {
            return;
        }
        $queryString = explode('/', $arguments);

        $queryString = $this->setUpRoutingAndGetQueryString($queryString);
        $queryStringCount = count($queryString);
        $request = di(RequestInterface::class);
        for ($i = 2; $i < $queryStringCount - 1; $i += 2) {
            $request->setArgument($queryString[$i], str_replace('{slash}', '/', $queryString[$i + 1]));
        }
    }

    /**
     * Get route for CLI based on the passed in arguments.
     *
     * @param string $arguments
     * @return void
     * @throws ServiceContainer\NotFoundException
     * @throws ReflectionException
     */
    public function buildCliArguments(string $arguments): void
    {
        $arguments = $this->cleanArguments($arguments);
        $queryString = explode('/', $arguments);
        $queryString[0] ??= 'index';
        $queryString[1] ??= 'index';

        $queryString = $this->setUpRoutingAndGetQueryString($queryString);
        array_shift($queryString);
        array_shift($queryString);
        try {
            $class = new \ReflectionClass($this->getControllerFullyQualifiedName());
        } catch (ReflectionException) {
            throw new NotFoundException('Controller ' . $this->getControllerName() . ' does not exist');
        }
        try {
            $method = $class->getMethod($this->getActionMethodName());
        } catch (ReflectionException) {
            throw new NotFoundException('Action ' . $this->getActionMethodName() . ' does not exist');
        }
        $this->setCliArgumentsOnRequest($method, $queryString);
    }

    protected function buildNamedRoute(
        string $arguments,
        RouteData $namedRoute
    ): void {
        $matches = $this->getArgumentsFromRegex($namedRoute, $arguments);
        $this->module = $namedRoute->module;
        $this->controllerName = $namedRoute->controller;
        $this->actionName = $namedRoute->action;
        $this->controller = NameHelper::getController($namedRoute->controller);
        $this->action = NameHelper::getDefaultAction($namedRoute->action);
        $this->routeName = $namedRoute->name;

        $isVariadic = $this->checkActionIsVariadic();
        $this->assignArguments($namedRoute->arguments, $matches, false, $isVariadic);
    }

    /**
     * @param RouteData $namedRoute
     * @param string $arguments
     * @return array<array-key,string>
     */
    protected function getArgumentsFromRegex(RouteData $namedRoute, string $arguments): array
    {
        $matches = [];
        preg_match($namedRoute->pattern, $arguments, $matches);
        array_shift($matches);
        $return = [];
        foreach ($matches as $val) {
            $val = explode('/', $val);
            $return = array_merge($return, $val);
        }
        return $return;
    }

    /**
     * @param string $route
     * @param string $requestMethod
     * @param array<string> $arguments
     * @param array $queryString
     * @param string|null $module
     * @param string $controller
     * @param string $action
     * @return string
     * @throws ServerFailureException
     */
    protected function getPathFromArguments(
        string $route,
        string $requestMethod,
        array $arguments,
        array $queryString,
        ?string $module,
        string $controller,
        string $action
    ): string {
        if ($route !== '') {
            $defaultArguments = $this->getDefaultArguments($route, $requestMethod) ?? [];
            $routeData = $this->getRouteData($route, $requestMethod);
            $path = $routeData->routePath;
            /**
             * @var string $key
             * @var ?string $val
             */
            foreach ($defaultArguments as $key => $val) {
                $argument = (!empty($arguments[$key]) ? $this->getArgument(
                    $arguments[$key]
                ) : ($this->getArgument($val)));
                $path = str_replace(':' . $key, $argument, $path);
                unset($arguments[$key]);
            }
            $arguments = array_merge($arguments, $queryString);
            if (count($arguments) !== 0) {
                $path .= '?' . http_build_query($arguments);
            }
        } else {
            $path = $module ? $module . DIRECTORY_SEPARATOR : '';
            $path .= $controller . DIRECTORY_SEPARATOR . $action;
            if (count($arguments) != 0) {
                /**
                 * @var string $key
                 * @var string $val
                 */
                foreach ($arguments as $key => $val) {
                    $path .= '/' . $key . '/' . $val;
                }
            }
            if (!empty($queryString)) {
                $path .= '?' . http_build_query($queryString);
            }
        }
        while (str_ends_with($path, '/index')) {
            $path = substr($path, 0, -6);
        }
        if (str_ends_with($path, 'index')) {
            $path = substr($path, 0, -5);
        }
        if (str_ends_with($path, '/',)) {
            $path = substr($path, 0, -1);
        }
        return $path;
    }

    protected function cleanArguments(string $arguments): string
    {
        if (str_ends_with($arguments, '/')) {
            $arguments = substr($arguments, 0, -1);
        }
        if (str_starts_with($arguments, '/')) {
            $arguments = substr($arguments, 1);
        }
        if (empty($arguments)) {
            $arguments = 'index/index';
        }
        return $arguments;
    }

    /**
     * @param array<string> $queryString
     * @return array<string>
     */
    protected function setUpRoutingAndGetQueryString(array $queryString): array
    {
        if (($this->runAs === Main::RUN_AS_CLI || $queryString['0'] !== 'CLI') && is_dir(
                APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . ucfirst($queryString['0'])
            )) {
            $moduleName = ucfirst($queryString[0]);
        }

        if (isset($moduleName)) {
            array_shift($queryString);
        }
        while (count($queryString) < 2) {
            $queryString[] = 'index';
        }
        $this->module = (string)($moduleName ?? 'Default');
        $this->isInternal = false;
        if ($queryString[0] === 'feast') {
            $this->isInternal = true;
            array_shift($queryString);
            $queryString[1] ??= 'index';
        }
        $this->controllerName = $queryString[0];
        $this->actionName = $queryString[1];
        $this->controller = NameHelper::getController($queryString[0]);
        $this->action = NameHelper::getDefaultAction($queryString[1]);
        return $queryString;
    }

    protected function checkAndBuildNamedRoute(string $arguments): bool
    {
        $queryString = explode('/', $arguments);
        $queryString[0] ??= 'index';
        $queryString[1] ??= 'index';

        if (($this->runAs === Main::RUN_AS_CLI || $queryString['0'] !== 'CLI') && is_dir(
                APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $queryString['0']
            )) {
            array_shift($queryString);
            $queryString[1] ??= 'index';
        }
        $requestMethod = $this->getCurrentRequestMethod();
        $checkString = implode('/', $queryString);
        $namedRoute = $this->getNamedRouteForUrlPath($requestMethod, $checkString);
        if ($namedRoute !== null) {
            $this->buildNamedRoute($arguments, $namedRoute);
            return true;
        }

        return false;
    }

    protected function getNamedRouteForUrlPath(string $requestMethod, string $checkString): ?RouteData
    {
        /** @var stdClass $routeGroup */
        $routeGroup = $this->routes->$requestMethod;
        /** @var RouteData $routeData */
        foreach ((array)$routeGroup as $routeData) {
            $arguments = [];
            if (preg_match_all($routeData->pattern, $checkString, $arguments)) {
                return $routeData;
            }
        }
        return null;
    }

    /**
     * @param ReflectionMethod $method
     * @param array<string> $queryString
     * @throws ServiceContainer\NotFoundException
     */
    protected function setCliArgumentsOnRequest(
        ReflectionMethod $method,
        array $queryString
    ): void {
        $parameterList = [];
        $flagList = [];
        foreach ($method->getAttributes(Param::class) as $parameter) {
            /** @var Param $instance */
            $instance = $parameter->newInstance();
            if ($instance->paramType === ParamType::PARAM) {
                $parameterList[] = $instance->name;
            } else {
                $flagList[$instance->name] = $instance->name;
            }
        }

        $request = di(RequestInterface::class);
        foreach ($queryString as $param) {
            if (str_starts_with($param, '--') && str_contains($param, '=')) {
                [$key, $val] = explode('=', $param);
                $key = substr($key, 2);
                if (!isset($flagList[$key])) {
                    continue;
                }

                $request->setArgument($key, str_replace('{slash}', '/', $val));
            } else {
                if (count($parameterList) === 0) {
                    continue;
                }
                $paramKey = array_shift($parameterList);
                $request->setArgument($paramKey, str_replace('{slash}', '/', $param));
            }
        }
    }

    /**
     * @return bool
     * @throws Error404Exception
     * @throws ReflectionException|ServiceContainer\NotFoundException
     */
    private function checkActionIsVariadic(): bool
    {
        $controller = $this->getControllerFullyQualifiedName();
        $actionMethod = $this->getActionMethodName();

        if (!class_exists($controller)) {
            throw new Error404Exception('Controller ' . $this->controller . ' does not exist!');
        }
        if (!method_exists($controller, $actionMethod)) {
            throw new Error404Exception('Action ' . $actionMethod . ' does not exist!');
        }
        $controllerClass = new \ReflectionClass($controller);
        $method = $controllerClass->getMethod($actionMethod);

        return $method->isVariadic();
    }

    /**
     * Get fully qualified class name for controller class.
     *
     * @return class-string
     */
    public function getControllerFullyQualifiedName(): string
    {
        $moduleName = $this->runAs === Main::RUN_AS_CLI ? 'CLI' : $this->getModuleName();
        $controllerNamespace = '\Controllers\\';
        if ($moduleName !== 'Default') {
            $controllerNamespace = '\Modules\\' . $moduleName . '\Controllers\\';
        }
        if ($this->isInternal) {
            $controllerNamespace = '\Feast\Controllers\\';
        }

        /** @var class-string $classString */
        $classString = $controllerNamespace . $this->getControllerClass();
        return $classString;
    }

    /**
     * Get the action method name for the desired action by analyzing class.
     *
     * @return string
     * @throws ServiceContainer\NotFoundException
     */
    public function getActionMethodName(): string
    {
        /** @var class-string $controllerName */
        $controllerName = $this->getControllerFullyQualifiedName();
        $actionName = substr($this->getAction(), 0, -6);
        $request = di(RequestInterface::class);
        if ($this->runAs === Main::RUN_AS_CLI || $request->isGet()) {
            $altActionName = $actionName . 'Get';
            if (method_exists($controllerName, $altActionName)) {
                return $altActionName;
            }
        } elseif ($request->isPost() && method_exists($controllerName, $actionName . 'Post')) {
            $altActionName = $actionName . 'Post';
            if (method_exists($controllerName, $altActionName)) {
                return $altActionName;
            }
        } elseif ($request->isDelete()) {
            $altActionName = $actionName . 'Delete';
            if (method_exists($controllerName, $altActionName)) {
                return $altActionName;
            }
        } elseif ($request->isPut()) {
            $altActionName = $actionName . 'Put';
            if (method_exists($controllerName, $altActionName)) {
                return $altActionName;
            }
        } elseif ($request->isPatch()) {
            $altActionName = $actionName . 'Patch';
            if (method_exists($controllerName, $altActionName)) {
                return $altActionName;
            }
        }

        return $this->getAction();
    }

    /**
     *
     * Get URL path for passed in arguments.
     *
     * @param string|null $action
     * @param string|null $controller
     * @param array<string> $arguments
     * @param array<string> $queryString
     * @param string|null $module
     * @param string $route
     * @param string|null $requestMethod
     * @return string
     * @throws Exception
     */
    public function getPath(
        ?string $action = null,
        ?string $controller = null,
        array $arguments = [],
        array $queryString = [],
        ?string $module = null,
        string $route = '',
        ?string $requestMethod = null
    ): string {
        $requestMethod ??= $this->getCurrentRequestMethod();
        if ($action === null && $route === '') {
            $action = $this->getActionName();
        }
        $action ??= 'index';
        if ($controller === null && $route === '') {
            $controller = $this->getControllerName();
        }
        $controller ??= 'index';
        if ($module === null && $route === '') {
            $module = strtolower($this->getModuleName());
        }
        if ($module !== null && ucfirst($module) === 'Default') {
            $module = null;
        }
        return $this->getPathFromArguments(
            $route,
            $requestMethod,
            $arguments,
            $queryString,
            $module,
            $controller,
            $action
        );
    }

    /**
     * @param string|array<string>|null $arguments
     * @return string
     */
    private function getArgument(string|array|null $arguments): string
    {
        if (is_null($arguments)) {
            return '';
        }
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }
        /** @var string $val */
        foreach ($arguments as &$val) {
            $val = urlencode($val);
        }
        return implode('/', $arguments);
    }

    /**
     * Get controller class.
     *
     * @return string
     */
    public function getControllerClass(): string
    {
        return $this->controller;
    }

    /**
     * Get action method.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    /**
     * Get action name.
     *
     * @return string
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * Get action name in camel case (for controller actions).
     *
     * Eg: show-data = showData
     *
     * @return string
     */
    public function getActionNameCamelCase(): string
    {
        return str_replace(' ', '', lcfirst(ucwords(str_replace('-', ' ', $this->actionName))));
    }

    /**
     * Get controller name camel-case (for views and urls).
     *
     * Eg: showData = show-data
     *
     * @return string
     */
    public function getControllerNameCamelCase(): string
    {
        return str_replace(' ', '', lcfirst(ucwords(str_replace('-', ' ', $this->controllerName))));
    }

    /**
     * Get action name with dashes (for views and urls).
     *
     * Eg: showData = show-data.
     *
     * @return string
     */
    public function getActionNameDashes(): string
    {
        return strtolower(preg_replace('/([A-Z])/', '-$1', $this->actionName));
    }

    /**
     * Get module name.
     *
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->module;
    }

    /**
     * Get route name.
     *
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * Get whether the current request has been forwarded to a different controller.
     *
     * If this is true, the controller loop in Main will rerun with the new parameters.
     *
     * @return bool
     */
    public function forwarded(): bool
    {
        return $this->reloop;
    }

    /**
     * Set whether to reloop through the controller loop.
     *
     * Controller::forward will trigger this automatically.
     *
     * @param bool $loop
     * @see HttpController::forward()
     */
    public function forward(bool $loop = true): void
    {
        $this->reloop = $loop;
    }

    /**
     * Get route's default arguments.
     *
     * @param string $route
     * @param string $requestMethod
     * @return array|null
     * @throws ServerFailureException
     */
    public function getDefaultArguments(string $route, string $requestMethod): ?array
    {
        $routeData = $this->getRouteData($route, $requestMethod);

        return $routeData->arguments;
    }

    protected function getRouteData(string $route, string $requestMethod): RouteData
    {
        /** @var stdClass $namedRoute */
        $namedRoute = $this->routes->$requestMethod;
        if (empty($namedRoute->$route)) {
            throw new Error404Exception('Route ' . $route . ' does not exist', 500);
        }
        /** @var RouteData $routeData */
        $routeData = $namedRoute->$route;

        return $routeData;
    }

    /**
     * Create new route. Using the Attributes is easier.
     *
     * @param string $fullPath
     * @param string $controller
     * @param string $action
     * @param string|null $routeName
     * @param array<string|bool> $defaults
     * @param string $httpMethod
     * @param string $module
     * @throws RouteException
     * @see Path
     */
    public function addRoute(
        string $fullPath,
        string $controller,
        string $action,
        ?string $routeName = null,
        array $defaults = null,
        string $httpMethod = 'GET',
        string $module = 'Default'
    ): void {
        if ($this->isFromCache()) {
            return;
        }
        $httpMethod = strtoupper($httpMethod);

        $path = explode('/', $fullPath);
        $argumentsLength = count($path);
        $routePath = $path['0'];
        $argumentChain = [];
        for ($i = 1; $i < $argumentsLength; $i++) {
            $argument = $path[$i];
            $optional = str_starts_with($argument, '?') ? '?' : '';
            if ($optional === '?') {
                $argument = substr($argument, 1);
            }
            if (str_starts_with($argument, ':')) {
                $argument = substr($argument, 1);
                $argumentChain[$argument] = $defaults[$argument] ?? null;
                if ($i !== $argumentsLength - 1) {
                    $routePath .= '/([^/]*)';
                } else {
                    $routePath .= '/' . $optional . '(.*)';
                }
            } else {
                $routePath .= '/' . $argument;
            }
        }
        if ($routeName === null) {
            if (str_starts_with($routePath, '/')) {
                $routePath = substr($routePath, 1);
            }
            $routeName = $routePath;
        }
        if (in_array($httpMethod . $routePath, $this->routeNameMap)) {
            throw new RouteException('Route already exists. Unique route path required');
        }

        $this->routes->$httpMethod->$routeName = new RouteData(
            $module, $controller, $action, $routeName, $argumentChain, $routePath, $fullPath
        );
        $this->routeNameMap[$routeName] = $httpMethod . $routePath;
    }

    /**
     * Assign new arguments for route.
     *
     * @param array $arguments
     * @param array<string> $params
     * @param bool $clearArguments
     * @param bool $allowVariadic
     * @throws ServiceContainer\NotFoundException
     */
    public function assignArguments(
        array $arguments,
        array $params = [],
        bool $clearArguments = false,
        bool $allowVariadic = false
    ): void {
        $request = di(RequestInterface::class);
        if ($clearArguments) {
            $request->clearArguments();
        }
        $i = 0;
        /** @var array<string,string> $arguments */

        foreach ($arguments as $key => $val) {
            $request->setArgument($key, $params[$i] ?? $val);
            $i++;
        }
        if ($allowVariadic === false || !isset($key) || $i <= 1 || count($params) === $i) {
            return;
        }
        $i--;
        $value = [];
        while ($i < count($params)) {
            if ($params[$i] != '') {
                $value[] = $params[$i];
            }
            $i++;
        }
        /** @var string $key */
        $request->setArgument($key, $value);
    }

    /**
     * Dynamically build the routes from the Path attributes.
     */
    public function buildRoutes(): void
    {
        if ($this->isFromCache()) {
            return;
        }
        $this->scanRoutes(APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'Controllers');
        $directory = opendir(APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'Modules');
        while ($module = readdir($directory)) {
            if ($module != '.' && $module != '..' && $module != 'CLI') {
                $path = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'Controllers';
                if (is_dir($path)) {
                    $this->scanRoutes($path, 'Modules\\' . $module . '\\', $module);
                }
            }
        }
    }

    /**
     * Check if router instance is from cached object.
     *
     * @return bool
     */
    public function isFromCache(): bool
    {
        return $this->fromCache;
    }

    /**
     * Cache the router object.
     */
    public function cache(): void
    {
        $this->buildRoutes();
        $this->fromCache = true;
        $this->isInternal = false;
        file_put_contents(
            APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'router.cache',
            serialize($this)
        );
        $this->fromCache = false;
    }

    private function scanRoutes(string $directory, string $filePath = '', string $module = 'Default'): void
    {
        $directory = opendir($directory);
        while (false !== ($classFile = readdir($directory))) {
            if (str_ends_with($classFile, 'Controller.php')) {
                /** @var class-string $classString */
                $classString = $filePath . 'Controllers\\' . substr($classFile, 0, -4);
                $this->analyzeControllerForRoute($classString, $module);
            }
        }
    }

    /**
     * @param class-string $classString
     * @param string $module
     * @throws ReflectionException
     */
    private function analyzeControllerForRoute(string $classString, string $module): void
    {
        $class = new \ReflectionClass($classString);
        $className = explode('\\', $class->name);
        $position = count($className) - 1;
        $className = lcfirst(substr($className[$position], 0, -10));

        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $this->analyzeMethodForRoute($method, $className, $module);
        }
    }

    private function analyzeMethodForRoute(ReflectionMethod $method, string $className, string $module): void
    {
        $attribute = $method->getAttributes(Path::class)[0] ?? null;
        $methodName = $this->getFilteredMethodName($method->name);
        if ($attribute === null) {
            return;
        }
        /** @var Path $pathAttribute */
        $pathAttribute = $attribute->newInstance();
        foreach ($pathAttribute->getMethods() as $methodType) {
            /** @psalm-suppress UndefinedPropertyFetch */
            $this->addRoute(
                $pathAttribute->path,
                $className,
                $methodName,
                $pathAttribute->name,
                $pathAttribute->defaults,
                (string)$methodType->value,
                $module
            );
        }
    }

    private function getFilteredMethodName(string $methodName): string
    {
        if (str_ends_with($methodName, 'Get') || str_ends_with($methodName, 'Put')) {
            return lcfirst(substr($methodName, 0, -3));
        }
        if (str_ends_with($methodName, 'Post')) {
            return lcfirst(substr($methodName, 0, -4));
        }
        if (str_ends_with($methodName, 'Patch')) {
            return lcfirst(substr($methodName, 0, -5));
        }
        if (str_ends_with($methodName, 'Delete') || str_ends_with($methodName, 'Action')) {
            return lcfirst(substr($methodName, 0, -6));
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getCurrentRequestMethod(): string
    {
        return !empty($_SERVER['REQUEST_METHOD']) ? (string)$_SERVER['REQUEST_METHOD'] : RequestMethod::GET->value;
    }

}
