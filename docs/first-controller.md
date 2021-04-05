[Back to Index](index.md)

# Your First Controller

When FEAST installs, it creates the `/Controllers/IndexController` class and `IndexGet` method for you. The base url
of `/` will [route](routing.md) to this controller/action by default.

In addition, `/Views/layout.phtml` is created as well as `/Views/Index/index.phtml`. The layout file is designed for
your overall template and includes some boilerplate for putting in CSS/JS without having to write script tags or link
tags.

In your Index Controller class, indexGet method, you can place any business logic or calls to other business logic. In
the controller,
`$this->view` can hold any variables you need for your [View](view.md) file. If you do not call a redirect or a forward,
your associated view file will be rendered.

Your main content for your home page should go in the `Views/Index/index.phtml`. It has access to all variables assigned
to your view as well as all the functions of the View class with `$this`.