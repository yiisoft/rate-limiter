# Yii Rate Limiter Change Log

## 3.0.1 under development

- no changes in this release.

## 3.0.0 July 26, 2023

- New #43: Add APCu counters storage (@jiaweipan)
- Chg #21: Update `yiisoft/http` dependency (@devanych)
- Chg #31: Raise `yiisoft/cache` dependency version to `^3.0` (@samdark)
- Chg #34: Raise `psr/http-message` dependency version to `^1.1|^2.0` (@samdark)
- Enh #25: Raise minimum `PHP` version to `8.0` (@terabytesoftw)
- Enh #26: Add `psr/simple-cache` dependency version `^3.0` support (@vjik)
- Enh #41: Adapt package to concurrent requests, for this `StorageInterface` method `save()` split to
  `saveIfNotExists()` and `saveCompareAndSwap()` (@jiaweipan)

## 2.0.0 August 15, 2021

- Enh #19: Introduce `LimitPolicyInterface`, `StorageInterface`, `TimerInterface`. Rename `Middleware` to
  `LimitRequestsMiddleware` (@kafkiansky, @samdark)

## 1.0.1 June 08, 2021

- Bug #14: Throw exception on call `getCacheKey()` in counter without the specified ID (@vjik)

## 1.0.0 June 07, 2021

- Initial release.
