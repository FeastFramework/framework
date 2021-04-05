[Back to Index](index.md)

# Working with Plugins

FEAST allows you to create Plugins for pre-processing and post-processing of a request. This can be used for (among other things)
sanitizing JSON requests to remove sensitive data from the returned view, sending emails or queueing jobs, and throttling requests.

A plugin is created through the FEAST [CLI](cli.md#feastcreateplugin). To enable a plugin, simply add it to your configuration file.

Plugins have the following 4 methods that you can define.

1. `preDispatch` - This method is ran before a web request is handed off to a controller.
2. `postDispatch` - This method is ran after the web request has finished processing in the controller,
   but before the view has been rendered.
3. `CLIpreDispatch` - This method is ran before a cli request is handed off to a controller.
4. `CLIpostDispatch` - This method is ran after the cli request has finished processing in the controller.

In addition, you can define any other helper methods in your plugin. If you wish to use the plugin standalone, with no automatic
hooks, simply do not add it to your configuration file.