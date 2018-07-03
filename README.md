# PSR-7 response sender

This library is a very simple [PSR-15](https://www.php-fig.org/psr/psr-15/) controller router [middleware](https://www.php-fig.org/psr/psr-15/#22-psrhttpservermiddlewareinterface) written in PHP 7.1.

## Usage

```php
<?php
use CodeInc\Psr15RouterMiddleware\RouterMiddleware;
use CodeInc\Psr15RouterMiddleware\AbstractController;
use CodeInc\Psr7Responses\HtmlResponse;
use Psr\Http\Message\ResponseInterface;

class HomePage extends AbstractController 
{
    public static function getUriPath():string { return '/'; }   
    public function process():ResponseInterface { return new HtmlResponse("<h1>Hello world!</h1>"); }
}
class AnotherPage extends AbstractController
{
    public static function getUriPath():string { return '/another-page.html'; }   
    public function process():ResponseInterface { return new HtmlResponse("<h1>Another page</h1>"); }
}
class NotFoundPage extends AbstractController
{
    public static function getUriPath():string { return '/error404.html'; }   
    public function process():ResponseInterface { return new HtmlResponse("<h1>Page not found</h1>"); }
}

$router = new RouterMiddleware();
$router->registerControllerClass(HomePage::class);
$router->registerControllerClass(AnotherPage::class);
$router->setNotFoundControllerClass(NotFoundPage::class);
```

## Installation

This library is available through [Packagist](https://packagist.org/packages/codeinc/psr15-router-middleware) and can be installed using [Composer](https://getcomposer.org/): 

```bash
composer require codeinc/psr15-router-middleware
```

## License 
This library is published under the MIT license (see the [`LICENSE`](LICENSE) file).


