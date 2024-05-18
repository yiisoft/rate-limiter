# Yii Rate Limiter Middleware Upgrading Instructions

This file contains notes that highlight changes that could break your application when you upgrade it from one major version to another.

## 2.0.0

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
