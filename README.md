## Debugbar for Phalcon 5
[![Packagist License](https://poser.pugx.org/barryvdh/laravel-debugbar/license.png)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://poser.pugx.org/barryvdh/laravel-debugbar/version.png)](https://packagist.org/packages/barryvdh/laravel-debugbar)
[![Total Downloads](https://poser.pugx.org/barryvdh/laravel-debugbar/d/total.png)](https://packagist.org/packages/barryvdh/laravel-debugbar)

This is a package to integrate [PHP Debug Bar](http://phpdebugbar.com/) with Phalcon 5.

![Debugbar 3.3 Screenshot](https://user-images.githubusercontent.com/973269/79428890-196cc680-7fc7-11ea-8229-189f5eac9009.png)

Note: Use the DebugBar only in development. It can slow the application down (because it has to gather data). So when experiencing slowness, try disabling some of the collectors.

### Installation:

Require this package with composer. It is recommended to only require the package for development.

```php
composer require barryvdh/laravel-debugbar --dev
```

Register a Provider in `index.php`

```php
$container = new \Phalcon\Di\FactoryDefault();

$container->register(new \Nin\Debugbar\ServiceProvider());
```

### Usage:

Add Message

```php
use Nin\Debugbar\Phalcon\Helper\Debugbar;

Debugbar::info($object);
Debugbar::error('Error!');
Debugbar::warning(new \Phalcon\Config\Config(['title' => 'Warning']));
```

Add start/stop timing:

```php
use Nin\Debugbar\Phalcon\Helper\Debugbar;

Debugbar::startMeasure('function', 'Function runtime');
Debugbar::stopMeasure('function');
Debugbar::measure('function', function() {
    // Do something…
});
```

Add Log Exception:

```php
use Nin\Debugbar\Phalcon\Helper\Debugbar;

try {
    //  Do something
} catch (Exception $e) {
    Debugbar::addThrowable($e);
}
```
