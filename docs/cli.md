[Back to Index](index.md)

# The FEAST CLI

FEAST has many built in powerful tools for working with your application. These tools range from file generation to
running jobs to setting maintenance mode. Below is a list of all the tools, their options, and their usage.

[feast:create](#feastcreate)

[feast:migration](#feastmigration)

[feast:cache](#feastcache)

[feast:template](#feasttemplate)

[feast:job](#feastjob)

[feast:maintenance](#feastmaintenance)

[feast:serve](#feastserve)

[help](#help)

## feast:create

The `feast:create` CLI group allows you to easily create many different types of classes automatically. This saves time
on both file creation and bug fixing. When you install FEAST, predefined templates are stored in `bin/templates`. You
may modify these templates for any custom boilerplate code.

### feast:create:action

```
php famine feast:create:action --type={(get-post-put-delete-patch)} --module={module} --noview={true|false} {controller} {action}
```

The `feast:create:action` command allows you to create a Controller/Action/View file in a single command. The controller
name must be a valid class name. If a module is passed in, the module must be a valid namespace (match against valid
class names).

The action can be passed in with dashes in all lowercase or with camelcase. The following naming rules will be applied:

* View file name: the action name with a dash between words and all lowercase.
* Action method: the action name in camelcase concatenated with the HTTP verb.
* Default URL: /controllername/action-name

If a module is passed in, the url will be prefixed with /modulename

Example:

```
Controller name: TestIng
Action name: sunny-day
Module: Welcome
HTTP verb: get

Controller class: /Modules/Welcome/Controllers/TestIngcontroller
Action method: sunnyDayGet
View file: /Modules/Welcome/Views/TestIng/sunny-day.phtml
url: /welcome/testIng/sunny-day

Controller name: TestIng
Action name: sunny-day
Module: {null}
HTTP verb: post

Controller class: /Controllers/TestIngcontroller
Action method: sunnyDayPost
View file: /Views/TestIng/sunny-day.phtml
url: /testIng/sunny-day
```

### feast:create:cli-action

```php
php famine feast:create:cli-action {controller} {action}
```

The `feast:create:cli-action` is a shortcut for `feast:create:action --module=CLI --noview=true --type=get` See
[feast:create:action](#feastcreateaction) for more info.

### feast:create:cron-job

```
php famine feast:create:cron-job {name}
```

Create a Cron Job class at `/Jobs/Cron/{name}`. The name must be a valid PHP classname. This cron job can be scheduled
in the file `scheduled_jobs.php`

### feast:create:form

```
php famine feast:create:form {name}
```

Create a Form class at `/Form/{name}`. The name must be a valid PHP classname. See [Forms](forms.md) for more
information.

### feast:create:form-filter

```
php famine feast:create:form-filter {name}
```

Create a Form Filter class at `/Form/Filter/{name}`. The name must be a valid PHP classname.

Form Filters allow you to modify incoming input on forms. Several built in filters are provided in `/Feast/Form/Filter`
See [Forms](forms.md) for more information.

### feast:create:form-validator

```
php famine feast:create:form-validator {name}
```

Create a Form Validator class at `/Form/Validator/{name}`. The name must be a valid PHP classname.

Form Validators allow you to validate incoming input on forms. Several built in validators are provided
in `/Feast/Form/Validator`
See [Forms](forms.md) for more information.

### feast:create:model

```
php famine feast:create:model --connection={connection} --model={model} --overwrite={true|false} {table-name}
```

Create a Model and corresponding mapper via database introspection. Three classes are created.

1. `Model\Generated\{Model}` - This class extends from `Feast\BaseModel` and contains all the properties from the
   database and should not be modified directly.
2. `Model\{Model}` - This class extends from the generated model. Any custom logic should go here, and will not be
   overwritten even if the table is re-inspected.
3. `Mapper\{Model}Mapper` - This class extends from `Feast\BaseMapper` and contains Primary Key and database connection
   information to allow simple dynamic SQL through the FEAST Query engine.

If a model name is not passed in, the table name becomes the base name of the model class. By default, the `Default`
connection is used.

If overwrite is not explicitly set to true, and the Mapper exists, it will not be overwritten. The generated model will
always be overwritten. The extended model will never be overwritten.

See [Working with Databases](models.md) for more information.

### feast:create:plugin

```
php famine feast:create:plugin {name}
```

Create a Plugin class at `/Plugins`. The name must be a valid PHP classname.

Plugins allow writing Pre and post dispatch hooks. See [Plugins](plugin.md) for more information.

### feast:create:queueable-job

```
php famine feast:create:queueable-job {name}
```

Create a Queueable Job class at `/Jobs/Queueable/{name}`. The name must be a valid PHP classname. These jobs can be
queued and run on a queue worker. See [Queues](queues.md) for more information.

### feast:create:service

```
php famine feast:create:service {name}
```

Create a Service class at `/Services/{name}`. The name must be a valid PHP classname. Service classes allow working with
HTTP requests in a simple, object-oriented manner. See [Services](services.md) for more information.

[Back to Top](#the-feast-cli)

## feast:migration

The `feast:migration` CLI group allows you to work with database migrations. These migrations allow for consistent
databases both across environments and across deployments.

### feast:migration:create

```
php famine feast:migration:create --file={file-suffix} migration-name
```

Create a Migration file. The file will begin with `Migration` followed by a generated sequential number, followed by
either your supplied file suffix or a random string. See [Working with Migrations](models.md#working-with-migrations)
for more information.

### feast:migration:up

```
php famine feast:migration:up {name}
```

Run the specified migration. The name includes the numeric prefix but does not include `migration`. For example, to
run `migration1_migrations.php`, the command is `php famine feast:migration:up 1_migrations`.
See [Working with Migrations](models.md#working-with-migrations) for more information.

### feast:migration:down

```
php famine feast:migration:down {name}
```

Reverse the specified migration. The name includes the numeric prefix but does not include `migration`. For example, to
run `migration1_migrations.php`, the command is `php famine feast:migration:down 1_migrations`. To reverse a migration,
the migration must define the correct behavior in the `down` method.
See [Working with Migrations](models.md#working-with-migrations) for more information.

### feast:migration:run-all

```
php famine feast:migration:run-all
```

Run all un-ran migrations up. This includes migrations that the `down` command has explicitly been called on.
See [Working with Migrations](models.md#working-with-migrations) for more information.

[Back to Top](#the-feast-cli)

## feast:cache

The `feast:cache` CLI group allows caching certain configuration details to speed up requests by reducing
pre-processing. Currently, config, routing, and basic database introspection information are supported.

It is **strongly** encouraged to use the cache in production but not required in development.

### feast:cache:config-clear

```
php famine feast:cache:config-clear
```

This command will delete the config cache and resume building on each request.

### feast:cache:config-generate

```
php famine feast:cache:config-generate
```

This command will re-generate the config cache. No changes to the configuration files will take effect unless the cache
is cleared or re-generated again.

### feast:cache:route-clear

```
php famine feast:cache:route-clear
```

This command will delete the routing cache and resume building on each request.

### feast:cache:routing-generate

```
php famine feast:cache:routing-generate
```

This command will re-generate the routing cache. No changes to the URL routing information will take effect unless the
cache is cleared or re-generated again.

### feast:cache:dbinfo-clear

```
php famine feast:cache:dbinfo-clear
```

This command will delete the database info cache, instead enabling table introspection on each request.

### feast:cache:dbinfo-generate

```
php famine feast:cache:dbinfo-generate
```

This command will re-generate the database info cache. No changes to the database schema will be detected unless the
cache is cleared or regenerated again. If you run any migrations, you MUST regenerate if using the cache.

[Back to Top](#the-feast-cli)

## feast:template

FEAST comes with several built in templates that are used to generate code. These files are not installed by default,
but are available to the CLI tool at all times. If you wish to modify any of the templates, you should first install
them, rather than editing directly. The `feast:template` group allows installation of the built in template files FEAST
uses with the [feast:create](#feastcreate) and [feast:migration:create](#feastmigrationcreate). The following actions
are available.

### feast:template:install-action

```
php famine feast:template:install-action
```

This command installs the `action` template file to `bin/templates/Action.php.txt`.

### feast:template:install-cli-action
```
php famine feast:template:install-cli-action
```

This command installs the `cliAction` template file to `bin/templates/CliAction.php.txt`.

### feast:template:install-controller
```
php famine feast:template:install-controller
```

This command installs the `controller` template file to `bin/templates/Controller.php.txt`.

### feast:template:install-cron-job
```
php famine feast:template:install-cron-job
```

This command installs the `cronJob` template file to `bin/templates/CronJob.php.txt`.

### feast:template:install-filter
```
php famine feast:template:install-filter
```

This command installs the `filter` template file to `bin/templates/Filter.php.txt`.

### feast:template:install-form
```
php famine feast:template:install-form
```

This command installs the `form` template file to `bin/templates/Form.php.txt`.

### feast:template:install-mapper
```
php famine feast:template:install-mapper
```

This command installs the `mapper` template file to `bin/templates/Mapper.php.txt`.

### feast:template:install-migration
```
php famine feast:template:install-migration
```

This command installs the `migration` template file to `bin/templates/Migration.php.txt`.

### feast:template:install-model
```
php famine feast:template:install-model
```

This command installs the `model` template file to `bin/templates/Model.php.txt`.

### feast:template:install-model-generated
```
php famine feast:template:install-action
```

This command installs the `modelGenerated` template file to `bin/templates/ModelGenerated.php.txt`.

### feast:template:install-plugin
```
php famine feast:template:install-plugin
```

This command installs the `plugin` template file to `bin/templates/Plugin.php.txt`.

### feast:template:install-queueable-job
```
php famine feast:template:install-queueable-job
```

This command installs the `queueableJob` template file to `bin/templates/QueueableJob.php.txt`.

### feast:template:install-service
```
php famine feast:template:install-service
```

This command installs the `service` template file to `bin/templates/Service.php.txt`.

### feast:template:install-validator
```
php famine feast:template:install-validator
```

This command installs the `action` template file to `bin/templates/Action.php.txt`.

### feast:template:install-all
```
php famine feast:template:install-all
```

This command installs all template files.

## feast:job

The `feast:job` group allows interacting with both scheduled (or cron) jobs and queueable jobs.

### feast:job:listen

```
php famine feast:job:listen --keepalive={true|false} {queues}
```

Listen for available jobs on the specified queues. Queues are separated by the pipe character. Keep Alive defaults to
true. If no jobs are available, the worker will sleep for 10 seconds before checking again. All jobs are processed by
the worker in a first-in, first-out manner. If a job fails, it is placed back into the queue and retried up to the
maximum number of tries.

### feast:job:run-one

```
php famine feast:job:run-one {job}
```

Manually run the specified job by Job ID. The job will re-run even if the maximum try count has been reached. This
allows debugging a job manually to look for what errors occurred.

### feast:job:run-cron

```
php famine feast:job:run-cron
```

Run all scheduled job items. See [Queues](queues.md) for more information.

```
php famine feast:job:run-cron-tem {job}
```

Run the specified job item. The job will run even if it is not inside its assigned schedule.

See [Queues](queues.md) for more information.

[Back to Top](#the-feast-cli)

## feast:maintenance

The `feast:maintenance` CLI group allows taking your website offline quickly in the event of needed maintenance.

### feast:maintenance:start

```
php famine feast:maintenance:start
```

Start maintenance mode. CLI tools will still work, but scheduled jobs will be halted and cron jobs will only run if
`evenInMaintenanceMode()` has been specified for the job. The Maintenance view will generate a static file that will be
served to all requests.

### feast:maintenance:start

```
php famine feast:maintenance:stop
```

Stop maintenance mode. Scheduled jobs and cron jobs will resume, and the maintenance static file will be replaced with a
javascript based redirect to the home page.

[Back to Top](#the-feast-cli)

## feast:serve

### feast:serve:serve

```
php famine feast:serve:serve --hostname={hostname} --port={port-number} --workers={worker-count}
```

Run PHP's built-in development server. By default, listens on localhost, port 8000, with a single worker.

[Back to Top](#the-feast-cli)

## help

```
php famine help {command}
```

View usage info and parameter information for a command.

[Back to Top](#the-feast-cli)