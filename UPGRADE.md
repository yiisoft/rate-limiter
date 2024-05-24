# Upgrading Instructions for Yii Rate Limiter Middleware

This file contains the upgrade notes. These notes highlight changes that could break your
application when you upgrade the package from one version to another.

> **Important!** The following upgrading instructions are cumulative. That is, if you want
> to upgrade from version A to version C and there is version B between A and C, you need
> to following the instructions for both A and B.

## Upgrade from 1.x to 2.0.0

In order to switch from version 1 to version 2 you need to update initialization code: 

```php
$cache = new ArrayCache();
$counter = new Counter(2, 5, $cache);
$middleware = new Middleware($counter, $responseFactory);
```

to

```php
$storage = new SimpleCacheStorage($cache);
$counter = new Counter($storage, 2, 5);
$middleware = new LimitRequestsMiddleware($counter, $responseFactory);
```

Check the readme for new features introduced in version 2.
