<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Yii Rate Limiter Middleware</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/rate-limiter/v/stable.png)](https://packagist.org/packages/yiisoft/rate-limiter)
[![Total Downloads](https://poser.pugx.org/yiisoft/rate-limiter/downloads.png)](https://packagist.org/packages/yiisoft/rate-limiter)
[![Build status](https://github.com/yiisoft/rate-limiter/workflows/build/badge.svg)](https://github.com/yiisoft/rate-limiter/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/rate-limiter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/rate-limiter/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/rate-limiter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/rate-limiter/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Frate-limiter%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/rate-limiter/master)
[![static analysis](https://github.com/yiisoft/rate-limiter/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/rate-limiter/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/rate-limiter/coverage.svg)](https://shepherd.dev/github/yiisoft/rate-limiter)

Rate limiter middleware helps to prevent abuse by limiting the number of requests that could be me made consequentially.

For example, you may want to limit the API usage of each user to be at most 100 API calls within a period of 10 minutes.
If too many requests are received from a user within the stated period of the time, a response with status code 429
(meaning "Too Many Requests") should be returned.

## Requirements

- PHP 7.4 or higher.

## Installation

The package could be installed with composer:

```shell
composer install yiisoft/rate-limiter --prefer-dist
```

## General usage

```php
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\RateLimiter\LimitRequestsMiddleware;
use Yiisoft\Yii\RateLimiter\Counter;
use Nyholm\Psr7\Factory\Psr17Factory;
use Yiisoft\Yii\RateLimiter\Policy\LimitAlways;
use Yiisoft\Yii\RateLimiter\Policy\LimitPerIp;
use Yiisoft\Yii\RateLimiter\Policy\LimitCallback;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;

/** @var StorageInterface $storage */
$storage = new RedisStore(); // in e.g

$counter = new Counter($storage, 2, 5);
$responseFactory = new Psr17Factory();

$middleware = new LimitRequestsMiddleware($counter, $responseFactory, new LimitAlways());
$middleware = new LimitRequestsMiddleware($counter, $responseFactory, new LimitPerIp());
$middleware = new LimitRequestsMiddleware($counter, $responseFactory, new LimitCallback(function (ServerRequestInterface $request): string {
    // in e.g return user id from database if authentication used.
}));
$middleware = new LimitRequestsMiddleware($counter, $responseFactory); // LimitPerIp by default
```

In the above 2 is the maximum number of increments that could be performed before increments are limited and 5 is
a period to apply limit to, in seconds.

The Counter implements [generic cell rate limit algorithm (GCRA)](https://en.wikipedia.org/wiki/Generic_cell_rate_algorithm)
that ensures that after reaching the limit further increments are distributed equally.

> Note: While it is sufficiently effective, it is preferred to use [Nginx](https://www.nginx.com/blog/rate-limiting-nginx/)
> or another webserver capabilities for rate limiting. This package allows rate-limiting in the project with deployment
> environment you cannot control such as installable CMS. 

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework with
[Infection Static Analysis Plugin](https://github.com/Roave/infection-static-analysis-plugin). To run it:

```shell
./vendor/bin/roave-infection-static-analysis-plugin
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

## License

The Yii Rate Limiter Middleware is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
