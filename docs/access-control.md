[Back to Index](index.md)

# Access Control via Environment

Sometimes, you may wish to allow or disallow access to a web route based on the current
application [environment](config.md#configuring-feast). FEAST contains an `AccessControl` [attribute](https://www.php.net/manual/en/language.attributes.overview.php) to allow this
level of control.

A controller may be annotated with the `#[AccessControl]` attribute as follows:

```php
<?php
class Controller extends HttpController
{
    #[AccessControl(onlyEnvironments: ['dev'])]
    public function allowedPathForEnvGet(
        ?string $name = null
    ): void {
        echo 'Success!';
    }

    #[AccessControl(disabledEnvironments: ['production'])]
    public function DeniedPathForProdGet(
        ?string $name = null
    ): void {
        echo 'Success!';
    }
}
```

In the example above, the first controller is only accessible in `dev`. The second is accessible in every environment
except `production`. 

