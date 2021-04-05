[Back to Index](index.md)

# Working with the View

FEAST uses a View class to render your pages. The view class has many helper methods to assist in your development
and also can have properties dynamically assigned for use in your template files. The template files are loaded
from the Views folder or the Views folder inside a module.

## Methods available
### Output related methods
1. `disableOutput` - This disables any rendering of the view. An HTTP response code is still sent to the browser.
2. `enableOutput` - Re-enables the rendering of the view.
3. `outputDisabled` - checks if the view should not render.
4. `outputEnabled` - checks if the view should render.
### Layout related methods
1. `disableLayout` - This disables the layout.phtml file. The view will be rendered as is.
2. `enableLayout` - Re-enables the rendering of the layout.phtml file.
3. `layoutDisabled` - checks if the layout.phtml should be excluded.
4. `layoutEnabled` - checks if the layout.phtml should be included.
5. `setLayoutFile` - This method takes a string argument of the layout file to use. Maps to the global `Views` folder or
   the module's `Views` folder if in a module.
6. `getLayoutFile` - This method returns the layout filename.
7. `emptyView` - This method resets the View object to its default state - No scripts, no css, output enabled, and enable layout.
### Javascript related methods
1. `addPreScript` - This method takes a filename and a boolean flag for whether the file should be allowed to load twice.
   Scripts passed to this method will be rendered in the `<head>` tag if using the default layout.phtml.
2. `addPreScripts` - This method takes an array of filenames and a boolean flag for whether the file should be allowed to load twice.
   Scripts passed to this method will be rendered in the `<head>` tag if using the default layout.phtml.
3. `addPreScriptSnippet` - This method takes a javascript string.
   Scripts passed to this method will be rendered in the `<head>` tag if using the default layout.phtml.
4. `getPreScripts` - This method gets all script files and script snippets that have been set up in `<script>` tag format.
   This is ran in the default layout.phtml in the `<head>` tag.
5. `addPostScript` - This method takes a filename and a boolean flag for whether the file should be allowed to load twice.
   Scripts passed to this method will be rendered before the closing of the `<body>` tag if using the default layout.phtml.
6. `addPostScripts` - This method takes an array of filenames and a boolean flag for whether the file should be allowed to load twice.
   Scripts passed to this method will be rendered before the closing of the `<body>` tag if using the default layout.phtml.
7. `addPostScriptSnippet` - This method takes a javascript string.
   Scripts passed to this method will be rendered before the closing of the `<body>` tag if using the default layout.phtml.
8. `getPostScripts` - This method gets all script files and script snippets that have been set up in `<script>` tag format.
   This is ran in the default layout.phtml before the closing of the `<body>` tag.
### CSS related methods
1. `addCssFile` - This method takes a filename as a parameter. The files are expected to live in `public/css`.
2. `addCssFiles` - This method takes an array of filenames as a parameter. The files are expected to live in `public/css`.
3. `getCss` - Gets the CSS files passed in as `link` tags. This is ran in the default layout.phtml.
### Head tag related methods
1. `setTitle` - This method takes a string as a parameter and sets the page title.
2. `getTitle` - This method takes a boolean flag for whether to return in the `<title>` tag or the raw title.
3. `setDoctype` - This method takes a string that maps to the `\Feast\Enums\DocTypes` constants. For forward compatibility
   with the next version of Feast, use the Enum class constant, not a string.
4. `getDtd` - Get the doctype declaration HTML. This is included automatically in the default layout.phtml.
5. `getDocType` - Get the DocType.
6. `setEncoding` - This method takes a string parameter of the encoding. The default is `UTF-8`
7. `getEncodingHtml` - This method gets the encoding/charset meta tag.  This is included automatically in the default layout.phtml.
8. `getEncoding` - This method returns the encoding.
### Partial view methods
There are two partial helper methods. They are similar but behave slightly different.

The first method is `partial`. This method takes a filename (in the `Views` folder), an array of variables, and whether to pass the global view 
variables to the partial. If enabled, then all variables on the view can also be used in the partial. This will render the partial.

The second method is `partialLoop`. This method takes a filename and an array or `\Feast\Collection` of array to be assigned to the view.
Each item in the array/collection is looped over and the partial is called for each.
In addition, the key of the collection/array is passed to the partial and becomes `$this->key` in the partial's phtml file.

### URL helper method
The `url` method returns a url for use in a view that is mapped from a given action/controller or named route. It takes the following
parameters.
1. `action` - String or null. If null, defaults to current.
2. `controller` - String or null. If null, defaults to current.
3. `arguments` - An array of arguments to be included in the url.
4. `queryString` - An array of arguments to be included as the query string on the URL
5. `module` - String or null. If null, defaults to current.
6. `route` - A named route.
7. `requestMethod` - String or null. The HTTP request method to search for if a named route. Defaults to current method. 
   For forward compatibility with the next version of FEAST, use the `Feast\Enums\RequestMethod` constant.