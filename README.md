<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://github.com/yiisoft.png" height="100px">
    </a>
    <h1 align="center">Yii RateLimiter Middleware</h1>
    <br>
</p>

RateLimiter helps to prevent abuse by limiting the number of requests that could be me made consequentially.

For example, you may want to limit the API usage of each user to be at most 100 API calls within a period of 10 minutes.
If too many requests are received from a user within the stated period of the time, a response with status code 429
(meaning "Too Many Requests") should be returned.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/rate-limiter/v/stable.png)](https://packagist.org/packages/yiisoft/rate-limiter)
[![Total Downloads](https://poser.pugx.org/yiisoft/rate-limiter/downloads.png)](https://packagist.org/packages/yiisoft/rate-limiter)
[![Build Status](https://travis-ci.com/yiisoft/rate-limiter.svg?branch=master)](https://travis-ci.com/yiisoft/rate-limiter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/rate-limiter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/rate-limiter/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/rate-limiter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/rate-limiter/?branch=master)

## Installation

The package could be installed with composer:

```
composer install yiisoft/rate-limiter
```

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

In the above 2 is the maximum number of increments that could be performed before increments are limited and 5 is
a period to apply limit to, in seconds.

The Counter implements [generic cell rate limit algorithm (GCRA)](https://en.wikipedia.org/wiki/Generic_cell_rate_algorithm)
that ensures that after reaching the limit further increments are distributed equally.

> Note: While it is sufficiently effective, it is preferred to use [Nginx](https://www.nginx.com/blog/rate-limiting-nginx/)
> or another webserver capabilities for rate limiting. This package allows rate-limiting in the project with deployment
> environment you cannot control such as installable CMS. 

## Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```php
./vendor/bin/phpunit
```

## Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```php
./vendor/bin/infection
```

## Static analysis

The code is statically analyzed with [Phan](https://github.com/phan/phan/wiki). To run static analysis:

```php
./vendor/bin/phan
```
