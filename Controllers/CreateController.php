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

namespace Feast\Controllers;

use Feast\Attributes\Action;
use Feast\Attributes\Param;
use Feast\BaseModel;
use Feast\Database\TableDetails;
use Feast\Enums\ParamType;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\DatabaseFactoryInterface;
use Feast\NameHelper;

class CreateController extends WriteTemplateController
{

    #[Action(usage: '--type={(get-post-put-delete-patch)} --module={module} --noview={true|false} {controller} {action}', description: 'Create a new controller action from the template file.')]
    #[Param(type: 'string', name: 'controller', description: 'Name of controller to create action in.' . "\n" . '  Controller file is created if it does not exist.')]
    #[Param(type: 'string', name: 'action', description: 'Name of action to create.' . "\n" . '  Creates associated view file unless noview is true')]
    #[Param(type: 'string', name: 'type', description: 'HTTP Methods for Controller Action, dash separated. Defaults to get', paramType: ParamType::FLAG)]
    #[Param(type: 'string', name: 'module', description: 'Optional - Module to create controller in (CLI for command line)', paramType: ParamType::FLAG)]
    #[Param(type: 'bool', name: 'noview', description: 'True to skip creating view file', paramType: ParamType::FLAG)]
    public function actionGet(
        ?string $controller = null,
        ?string $action = null,
        string $type = 'get',
        ?string $module = null,
        bool $noview = false
    ): void {
        if ($controller === null || $action === null) {
            $this->help('feast:create:action');
            return;
        }
        if ($this->validateRulesOrPrintError($controller, 'controller') === false) {
            return;
        }

        if ($this->validateRulesOrPrintError(lcfirst(NameHelper::getName($action)), 'action') === false) {
            return;
        }

        if (is_string($module) && $this->validateRulesOrPrintError($module, 'module') === false) {
            return;
        }

        $path = APPLICATION_ROOT;
        $namespace = 'Controllers';
        if (isset($module)) {
            $path .= 'Modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;
            $namespace = 'Modules\\' . $module . '\\Controllers';
        }
        $path .= 'Controllers' . DIRECTORY_SEPARATOR;
        $this->buildControllerDirectoryIfNotExists($path);

        $path .= ucfirst($controller) . 'Controller.php';
        $this->createControllerFileIfNotExists($path, $controller, $namespace, $module);
        $this->buildAction($path, $controller, $action, $module, $type);
        if ($noview === false && $module !== 'CLI') {
            $this->createViewFileIfNotExists($controller, $module, $action);
        }
    }

    #[Action(usage: '{controller} {action}', description: 'Create a new CLI controller action from the template file.')]
    #[Param(type: 'string', name: 'controller', description: 'Name of controller to create action in.' . "\n" . '  Controller file is created if it does not exist.')]
    #[Param(type: 'string', name: 'action', description: 'Name of action to create.')]
    public function cliActionGet(
        ?string $controller = null,
        ?string $action = null,

    ): void {
        if ($controller === null || $action === null) {
            $this->help('feast:create:cli-action');
            return;
        }
        $this->actionGet($controller, $action, module: 'CLI');
    }

    #[Action(usage: '{name}', description: 'Create a cron job class from the template file.')]
    #[Param(type: 'string', name: 'name', description: 'Name of job to create')]
    public function cronJobGet(
        ?string $name = null
    ): void {
        if ($name === null) {
            $this->help('feast:create:cron-job');
            return;
        }
        if ($this->validateRulesOrPrintError($name) === false) {
            return;
        }

        $file = APPLICATION_ROOT . 'Jobs' . DIRECTORY_SEPARATOR . 'Cron' . DIRECTORY_SEPARATOR . ucfirst(
                $name
            ) . '.php';

        $this->writeSimpleTemplate($name, 'CronJob', $file);
    }

    #[Action(usage: '{name}', description: 'Create a form class from the template file.')]
    #[Param(type: 'string', name: 'name', description: 'Name of form to create')]
    public function formGet(
        ?string $name = null
    ): void {
        if ($name === null) {
            $this->help('feast:create:form');
            return;
        }
        if ($this->validateRulesOrPrintError($name) === false) {
            return;
        }

        $file = APPLICATION_ROOT . 'Form' . DIRECTORY_SEPARATOR . ucfirst($name) . '.php';

        $this->writeSimpleTemplate($name, 'Form', $file);
    }

    #[Action(usage: '{name}', description: 'Create a form field filter class from the template file.')]
    #[Param(type: 'string', name: 'name', description: 'Name of filter to create')]
    public function formFilterGet(
        ?string $name = null
    ): void {
        if ($name === null) {
            $this->help('feast:create:form-filter');
            return;
        }
        if ($this->validateRulesOrPrintError($name) === false) {
            return;
        }

        $file = APPLICATION_ROOT . 'Form' . DIRECTORY_SEPARATOR . 'Filter' . DIRECTORY_SEPARATOR . ucfirst(
                $name
            ) . '.php';

        $this->writeSimpleTemplate($name, 'Filter', $file);
    }

    #[Action(usage: '{name}', description: 'Create a form field validator class from the template file.')]
    #[Param(type: 'string', name: 'name', description: 'Name of validator to create')]
    public function formValidatorGet(
        ?string $name = null
    ): void {
        if ($name === null) {
            $this->help('feast:create:form-validator');
            return;
        }
        if ($this->validateRulesOrPrintError($name) === false) {
            return;
        }

        $file = APPLICATION_ROOT . 'Form' . DIRECTORY_SEPARATOR . 'Validator' . DIRECTORY_SEPARATOR . ucfirst(
                $name
            ) . '.php';

        $this->writeSimpleTemplate($name, 'Validator', $file);
    }

    /**
     * @throws ServerFailureException
     */
    #[Action(usage: '--connection={connection} --model={model} --overwrite={true|false} {table-name}', description: 'Create a model and mapper for a database table.')]
    #[Param(type: 'string', name: 'table', description: 'Name of table to build model from')]
    #[Param(type: 'string', name: 'connection', description: 'Name of connection to use for database connection. Defaults to "default"', paramType: ParamType::FLAG)]
    #[Param(type: 'string', name: 'model', description: 'Base name for model and mapper classes. Defaults to table name', paramType: ParamType::FLAG)]
    #[Param(type: 'bool', name: 'overwrite', description: 'If set to true, overwrites existing mapper file if it exists (defaults false)', paramType: ParamType::FLAG)]
    public function modelGet(
        DatabaseFactoryInterface $databaseFactory,
        ?string $table = null,
        string $connection = 'default',
        ?string $model = null,
        bool $overwrite = false
    ): void {
        if ($table === null) {
            $this->help('feast:create:model');
            return;
        }

        $class = !empty($model) ? ucfirst($model) : str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
        if ($this->validateRulesOrPrintError($class) === false) {
            return;
        }

        $dbConnection = $databaseFactory->getConnection($connection);

        $tableInfo = $dbConnection->getDescribedTable($table);

        $this->writeModelFile($tableInfo, $class);
        $this->writeMapperfile($table, $class, $connection, $tableInfo, $overwrite);
    }

    #[Action(usage: '{name}', description: 'Create a plugin class from the template file.')]
    #[Param(type: 'string', name: 'name', description: 'Name of plugin to create')]
    public function pluginGet(
        ?string $name = null
    ): void {
        if ($name === null) {
            $this->help('feast:create:plugin');
            return;
        }
        if ($this->validateRulesOrPrintError($name) === false) {
            return;
        }

        $file = APPLICATION_ROOT . 'Plugins' . DIRECTORY_SEPARATOR . ucfirst($name) . '.php';

        $this->writeSimpleTemplate($name, 'Plugin', $file);
        $this->terminal->message(
            'To enable add the line below to your configs/config.php in the appropriate environment' . "\n"
        );
        $this->terminal->command('\'plugin.' . strtolower($name) . '\' => \\Plugins\\' . ucfirst($name) . '::class,');
    }

    #[Action(usage: '{name}', description: 'Create a queueable job class from the template file.')]
    #[Param(type: 'string', name: 'name', description: 'Name of job to create')]
    public function queueableJobGet(
        ?string $name = null
    ): void {
        if ($name === null) {
            $this->help('feast:create:queueable-job');
            return;
        }
        if ($this->validateRulesOrPrintError($name) === false) {
            return;
        }

        $file = APPLICATION_ROOT . 'Jobs' . DIRECTORY_SEPARATOR . 'Queueable' . DIRECTORY_SEPARATOR . ucfirst(
                $name
            ) . '.php';

        $this->writeSimpleTemplate($name, 'QueueableJob', $file);
    }

    #[Action(usage: '{name}', description: 'Create a service class from the template file.')]
    #[Param(type: 'string', name: 'name', description: 'Name of service to create')]
    public function serviceGet(
        ?string $name = null
    ): void {
        if ($name === null) {
            $this->help('feast:create:service');
            return;
        }
        if ($this->validateRulesOrPrintError($name) === false) {
            return;
        }

        $file = APPLICATION_ROOT . 'Services' . DIRECTORY_SEPARATOR . ucfirst($name) . '.php';

        $this->writeSimpleTemplate($name, 'Service', $file);
    }

    /**
     * Write a class from a template file.
     *
     * @param string $name The name of the class that is being created
     * @param string $type The template type
     * @param string $file
     */
    protected function writeSimpleTemplate(string $name, string $type, string $file): void
    {
        if (file_exists(($file))) {
            $this->terminal->error('File ' . $file . ' already exists.');
            return;
        }
        $templateFile = $this->getTemplateFilePath($type);
        $contents = file_get_contents($templateFile);
        $contents = str_replace(
            [
                '{name}'
            ],
            [
                ucfirst($name)
            ],
            $contents
        );
        file_put_contents($file, $contents);
        $this->terminal->message($type . ' file ' . ucfirst($name) . '.php created.');
    }

    /**
     * Write model file from template
     *
     * @param TableDetails $tableInfo
     * @param string $class
     */
    protected function writeModelFile(TableDetails $tableInfo, string $class): void
    {
        $fields = '';
        $fieldInfo = $tableInfo->fields;

        foreach ($fieldInfo as $field) {
            $fields .= $field->getModelField();
        }

        $this->writeTemplateFile('Model', 'Model', false, $class, '', $fields);
        $this->writeTemplateFile(
            'ModelGenerated',
            'Model' . DIRECTORY_SEPARATOR . 'Generated',
            true,
            $class,
            '',
            $fields
        );
        $this->terminal->message('Model class created');
    }

    /**
     * Write mapper file from template
     *
     * @param string $table
     * @param string $class
     * @param string $connection
     * @param TableDetails $tableInfo
     * @param bool $overwrite
     */
    protected function writeMapperfile(
        string $table,
        string $class,
        string $connection,
        TableDetails $tableInfo,
        bool $overwrite
    ): void {
        $compoundPrimary = $tableInfo->compoundPrimary;
        $primaryKeyType = $tableInfo->primaryKeyType;
        $primaryKey = $tableInfo->primaryKey;
        if ($compoundPrimary) {
            $this->terminal->message('Cannot generate mapper - Compound primary key');
        } elseif (!empty($primaryKey) && !empty($primaryKeyType)) {
            $created = $this->writeTemplateFile(
                             'Mapper',
                             'Mapper',
                             $overwrite,
                             $class,
                             'Mapper',
                table:       $table,
                connection:  $connection,
                primaryKey:  $primaryKey,
                primaryType: $primaryKeyType,
                sequence: (string)$tableInfo->sequence
            );
            if ( $created ) {
                $this->terminal->message('Mapper class created');
            } else {
                $this->terminal->message('Mapper class already exists - not created.');
            }
        } else {
            $created = $this->writeTemplateFile(
                             'NoKeyMapper',
                             'Mapper',
                             $overwrite,
                             $class,
                             'Mapper',
                table:       $table,
                connection:  $connection,
                sequence: (string)$tableInfo->sequence
            );
            if ( $created ) {
                $this->terminal->message('Mapper class created. Save method must be manually created due to no primary key.');
            } else {
                $this->terminal->message('Mapper class already exists - not created.');
            }
        }
    }

    protected function writeTemplateFile(
        string $templateFile,
        string $path,
        bool $overwrite,
        string $class,
        string $classExtra = '',
        string $fields = '',
        string $table = '',
        string $connection = 'default',
        string $primaryKey = '',
        string $primaryType = '',
        string $sequence = ''
    ): bool {
        $file = $this->getTemplateFilePath($templateFile);
        $template = file_get_contents($file);
        if (!file_exists(APPLICATION_ROOT . $path . DIRECTORY_SEPARATOR)) {
            mkdir(APPLICATION_ROOT . $path . DIRECTORY_SEPARATOR);
        }
        if ($overwrite || !file_exists(APPLICATION_ROOT . $path . DIRECTORY_SEPARATOR . $class . $classExtra . '.php')) {
            $extraMapperInfo = $connection !== 'default' ? '    public const CONNECTION = \'' . $connection . '\';' . "\n" : '';
            if ( $sequence != '' ) {
                $extraMapperInfo .= '    public const SEQUENCE_NAME = \'' . $sequence . '\';' . "\n";
            } 
            file_put_contents(
                APPLICATION_ROOT . $path . DIRECTORY_SEPARATOR . $class . $classExtra . '.php',
                str_replace(
                    [
                        'onSave({name}',
                        'onDelete({name}',
                        '{name}',
                        '{classExtra}',
                        '{map}',
                        '{connection}',
                        '{primaryKey}',
                        '{table}',
                        '{primaryType}'
                    ],
                    [
                        'onSave(\\' . BaseModel::class . '|' . $class,
                        'onDelete(\\' . BaseModel::class . '|' . $class,
                        $class,
                        $classExtra,
                        $fields,
                        $extraMapperInfo,
                        $primaryKey,
                        $table,
                        $primaryType
                    ]
                    ,
                    trim($template) . "\n"
                )
            );
            return true;
        }
        return false;
    }

    protected function buildControllerDirectoryIfNotExists(string $path): void
    {
        if (file_exists($path) === false) {
            mkdir($path, 0755, true);
            $this->terminal->message('Directory ' . $path . ' Created');
        }
    }

    protected function createControllerFileIfNotExists(
        string $path,
        string $controller,
        string $namespace,
        ?string $module
    ): void {
        if (file_exists($path) === false) {
            $file = $this->getTemplateFilePath('Controller');
            $contents = file_get_contents($file);
            $actionUse = $module === 'CLI' ? 'use Feast\Attributes\Action;' . "\n" : '';
            $cliUse = $module === 'CLI' ? 'Cli' : 'Http';

            $contents = str_replace(
                [
                    '{namespace}',
                    '{name}',
                    '{action-use}',
                    '{cli}'
                ],
                [$namespace, ucfirst($controller), $actionUse, $cliUse],
                $contents
            );

            file_put_contents($path, $contents);
            $this->terminal->message('File ' . ucfirst($controller) . 'Controller.php created');
        }
    }

    protected function buildAction(
        string $controllerFile,
        string $controllerName,
        string $action,
        ?string $module,
        string $types
    ): void {
        $types = explode('-', $types);
        $templateFile = $module === 'CLI' ? 'CliAction' : 'Action';
        $file = $this->getTemplateFilePath($templateFile);
        $template = file_get_contents($file);
        $actionName = lcfirst(NameHelper::getName($action));

        foreach ($types as $type) {
            $this->writeActionForType($controllerFile, $controllerName, $template, $actionName, $type);
        }
    }

    protected function writeActionForType(
        string $controllerFile,
        string $controllerName,
        string $template,
        string $action,
        string $type
    ): void {
        $classData = file_get_contents($controllerFile);
        if (str_contains($classData, 'function ' . $action . ucfirst($type) . '()')) {
            $this->terminal->command('Action ' . $action . ucfirst($type) . ' already exists');
            return;
        }
        $lastBracket = strrpos($classData, '}');
        if ($lastBracket !== false) {
            $this->writeTemplateForAction(
                $action,
                $type,
                $controllerName,
                $template,
                $controllerFile,
                $lastBracket,
                $classData
            );

            return;
        }
        $this->terminal->error('Error creating action ' . $action . ucfirst(($type)));
        $this->terminal->error('Controller file appears corrupted');
    }

    protected function writeTemplateForAction(
        string $action,
        string $type,
        string $controllerName,
        string $template,
        string $controllerFile,
        int $lastBracket,
        string $classData
    ): void {
        $contents = $this->getTemplateForAction($action, $type, $controllerName, $template);
        file_put_contents(
            $controllerFile,
            substr($classData, 0, $lastBracket) . $contents . substr($classData, $lastBracket)
        );
        $this->terminal->message('Action ' . $action . ucfirst($type) . ' created');
    }

    protected function getTemplateForAction(
        string $action,
        string $type,
        string $controllerName,
        string $template
    ): string {
        return str_replace(
            [
                '{action}',
                '{type}',
                '{controller}',
                '{action-call}'
            ],
            [
                $action,
                ucfirst($type),
                NameHelper::getNameWithDashes($controllerName),
                NameHelper::getNameWithDashes($action)
            ],
            $template
        );
    }

    protected function createViewFileIfNotExists(string $controller, ?string $module, string $action): void
    {
        $pathParent = APPLICATION_ROOT;
        $pathParent .= !empty($module) ? 'Modules' . DIRECTORY_SEPARATOR . $module : '';
        $pathParent .= DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR;

        $path = $pathParent . ucfirst($controller);
        if (file_exists($path) == false) {
            mkdir($path, 0755, true);
            $this->terminal->message('Directory ' . $path . ' Created');
        }
        $this->copyLayoutIfNotExists($pathParent);
        $action = NameHelper::getNameWithDashes($action);
        if (!file_exists($path . DIRECTORY_SEPARATOR . $action . '.phtml')) {
            file_put_contents($path . DIRECTORY_SEPARATOR . $action . '.phtml', '');
            $this->terminal->message('View file ' . $path . DIRECTORY_SEPARATOR . $action . '.phtml created');
        }
    }

    protected function copyLayoutIfNotExists(string $pathParent): void
    {
        if (file_exists($pathParent . 'layout.phtml') === false) {
            copy(
                APPLICATION_ROOT . 'bin' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'layout.phtml',
                $pathParent . 'layout.phtml'
            );
            $this->terminal->message('Layout file ' . $pathParent . 'layout.phtml created');
        }
    }

}
