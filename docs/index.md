![FEAST Framework](logo.png)

# FEAST Framework

[What is FEAST?](#what-is-feast)

[Requirements](#requirements)

[Getting started](#getting-started)

[Getting moving](#getting-moving)

[Advanced Features](#advanced-features)

[Scheduled and Queued Jobs](#scheduled-and-queued-jobs)

[Extras](#extras)

## What is FEAST?

FEAST is a radically different PHP Framework that was built from the ground up to be an alternative to the
dependency-heavy frameworks that exist already. Its goal is a light-weight footprint that just lets you get stuff done.

FEAST was designed, architected, and crafted with its name in mind.

### What's in a name?

#### Fast

FEAST is performant and maintains a very small stack trace. FEAST also has native caching for configuration and routing
to speed up requests. But that's not all we mean when we say FEAST is fast. FEAST is fast to develop in. FEAST was
designed to reduce the need to write excessive boilerplate code.

#### Easy

FEAST was designed to get things done with very little learning. Routing, class creation, and running cli tools or
scheduled jobs are all simplified in FEAST for the developer who wants to get things done.

#### Agile

FEAST was designed to be able to adapt to change quickly. FEAST is also designed to let you adapt to change quickly.
FEAST has tools for database introspection and class/method creation that take away the pain of starting anew.

#### Slim

FEAST is extremely lightweight with no external dependencies to get up and running. By having no external dependencies,
it is easier for FEAST to be...

#### Tested and Trans Fat Free

FEAST was crafted to be testable. FEAST uses [Psalm](https://github.com/vimeo/psalm) static type analysis and no code is
merged if it does not pass 100% static type analysis with zero errors on errorLevel 1. In addition, FEAST boasts 100%
line coverage in [PHPUnit](https://github.com/sebastianbergmann/phpunit).

### What makes FEAST unique?

FEAST is radically different from every other PHP framework to date. FEAST has been carefully designed from the ground
up with the following principles always in mind.

1. No mandatory 3rd party code dependencies - FEAST took inspiration from many sources, but was written completely from
   scratch.
2. Cutting edge - FEAST is designed to be used with (and indeed requires)
   the latest minor release of PHP. FEAST will leverage new features of the language whenever possible.
3. Predictable versions - FEAST will release a new major release with each PHP version and will require that new
   version. The current version of FEAST runs on PHP 8.0. Previous major releases will still issue bug fixes for 2 prior
   PHP minor releases, so that "cutting edge" does not become a barrier to entry.

## Requirements

The current release of FEAST requires >=PHP 8.1 and (if you wish to use the Curl service classes) PHP 8.1-curl. In
addition, PHP 8.1-bcmath is recommended.

The v1.x line of FEAST requires >=PHP 8.0. The same recommendations above apply

FEAST currently works with the MySQL and PostgreSQL database management systems as well as with SQLite for simple
queries.

The release plan for FEAST can be found [here](release-schedule.md).

## Getting started

[The Five-Minute Quickstart](install.md)

[Configuring FEAST](config.md)

[Routing](routing.md)

[Access Control via Environment](access-control.md)

[Your First Controller](first-controller.md)

## Getting moving

[Working with Controllers](controller.md)

[Working with Views](view.md)

[Service Container and Dependency Injection](service-container.md)

[The FEAST CLI](cli.md)

[The Logger](logger.md)

## Advanced Features

[Working with Databases](models.md)

[Working with Dates](date.md)

[Working with Plugins](plugin.md)

[Collections](collections.md)

[JSON Files](json.md)

[CSV Files](csv.md)

[Services](services.md)

[Forms](forms.md)

[Sessions](sessions.md)

## Scheduled and Queued Jobs

[Queueable Jobs](queues.md)

[Scheduled Jobs](cron-jobs.md)

## Extras

[Throttle Plugin](throttle.md)

[Contributors](contributors.md)