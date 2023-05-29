[Back to Index](index.md)

# Services
FEAST simplifies working with HTTP requests and APIs. It includes a Request class for both Curl or file_get_contents based requests.
In addition, you may add your own Request class by implementing `\Feast\Interfaces\HttpRequestInterface`
The chosen class is specified in your configuration file as `service.class` and is used in all Service classes.

[The HttpRequestInterface](#the-httprequestinterface)

[The Builder Methods](#the-builder-methods)

[Request Metadata Methods](#request-metadata-methods)

[Response Methods](#response-methods)

[Sample Service class request usage](#sample-service-class-request-usage
)
# The HttpRequestInterface
The HttpRequestInterface contains methods for working with web requests. This class has a fluent interface on the builder methods.

[Back to Top](#services)

## The Builder Methods
1. `get` - This method initializes a GET request and takes a URL as an argument.
2. `post` - This method initializes a POST request and takes a URL as an argument.
3. `put` - This method initializes a PUT request and takes a URL as an argument.
4. `patch` - This method initializes a PATH request and takes a URL as an argument.
5. `delete` - This method initializes a DELETE request and takes a URL as an argument.
6. `postJson` - This method initializes a POST request, sets the content type to `application/json` and takes a URL as an argument.
7. `putJson` - This method initializes a PUT request, sets the content type to `application/json` and takes a URL as an argument.
8. `patchJson` - This method initializes a PATCH request, sets the content type to `application/json` and takes a URL as an argument.
9. `addCookie` - This method takes a name and value of a cookie to be used on the request.
10. `addArgument` - This method takes a name, value and a boolean flag of whether the argument should be treated as an array.
11. `addArguments` - This method takes a pre-built array of arguments to be used on the request. Only allows simple key => value mappings.
12. `clearArguments` - Clears all arguments.
13. `setReferer` - Set the referer [sic] for the request.
14. `setUserAgent` - Set the user agent for the request.
15. `authenticate` - This method takes a username and password to be used to authenticate the request.
16. `addHeader` - This method takes a header name and value to be sent on the request.
17. `makeRequest` - This method executes the request.

[Back to Top](#services)

## Request Metadata Methods
1. `getReferer` - Get the referer [sic] for the request.
2. `getCookies` - Get the cookies for the request as an array.
3. `getContentType` - Get the content type of the request.
4. `getHeaders` - Get all the headers as an array.
5. `getResponseCode` - Get the HTTP response code for the last request.

[Back to Top](#services)

## Response Methods
1. `getResponse` - Gets the `\Feast\Response` object associated with the request.
2. `getResponseAsString` - Gets the response as a string
3. `getResponseAsXml` - Gets the response as a SimpleXMLElement.
4. `getResponseAsJson` - Get the response as a stdClass from a JSON string.
5. `getResponseCode` - Get the HTTP response code for the response.

[Back to Top](#services)

# Sample Service class request usage
```php
$this->httpRequest->postJson(self::URL . '/subscribers');
$this->httpRequest->addArguments($data);
$this->httpRequest->authenticate($this->apiKey, '');
$this->httpRequest->makeRequest();
$response = $this-httpRequest->getResponseAsJson();
```

[Back to Top](#services)