# Shiniwork is PHP Framework based on Slim 3.

## Install
`composer require shiniwolf/shiniwork`

## Architecture
```
/app
    /config
    /src
        /Controller
        /Model
        /View
       dependencies.php
       routes.php
/public
    .htaccess
    index.php
```

## Extends Slim 3
### Application
```php
<?php
    $app = new \Shiniwork\Shiniwork();
    $app->run();
```

### Controller
```php
<?php


    namespace Website\Controller;


    use Shiniwork\Controller;
    use Slim\Http\Request;
    use Slim\Http\Response;

    class Dashboard extends Controller
    {
        public function index (Request $request, Response $response)
        {
            var_dump($this->container->database);
            return $response->write('Dashboard');
        }
    }
```

## Settings
```json
{
    "database": {
        "driver": "mysql",
        "host": "localhost",
        "database": "database",
        "username": "user",
        "password": "password",
        "charset": "utf8",
        "collation": "utf8_unicode_ci",
        "prefix": ""
    },
    "jwt": {
        "secure": false,
        "path": ["/api"],
        "cookie": "cookie_name",
        "secret": "my_secret_key"
    },
    "mailer": {
        "host": "auth.smtp.fr",
        "username": "username@example.fr",
        "password": "my_password",
        "security": "ssl",
        "port": 465
    },
    "view": {
        "template_path": "../app/src/View",
        "twig": {
            "cache": "cache/twig",
            "debug": false,
            "auto_reload": false
        }
    }
}
```
