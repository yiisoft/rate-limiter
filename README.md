<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://github.com/yiisoft.png" height="100px">
    </a>
    <h1 align="center">Yii RateLimiter</h1>
    <br>
</p>

RateLimiter helps to prevent abuse by limiting the number of requests that could be me made consequentially.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/rate-limiter/v/stable.png)](https://packagist.org/packages/yiisoft/rate-limiter)
[![Total Downloads](https://poser.pugx.org/yiisoft/rate-limiter/downloads.png)](https://packagist.org/packages/yiisoft/rate-limiter)
[![Build Status](https://travis-ci.com/yiisoft/rate-limiter.svg?branch=master)](https://travis-ci.com/yiisoft/rate-limiter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/rate-limiter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/rate-limiter/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/rate-limiter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/rate-limiter/?branch=master)

Installation
------------

- The minimum required PHP version is PHP 7.4.


## General usage

```php

use Yiisoft\Yii\RateLimiter\Middleware;
use Yiisoft\Yii\RateLimiter\Counter;
use Yiisoft\Cache\ArrayCache;
use Nyholm\Psr7\Factory\Psr17Factory;

$cache = new ArrayCache();
$counter = new Counter(2, 5, $cache);
$responseFactory = new Psr17Factory();

$middleware = new Middleware($counter, $responseFactory);

```

