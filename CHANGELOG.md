# Yii Rate Limiter Change Log

## 3.0.0 under development

- Enh: Add composer require checker into CI
- New #43: Add APCu counters storage (@jiaweipan)
- Enh #41: Adapt package to concurrent requests, for this `StorageInterface` method `save()` split to
  `saveIfNotExists()` and `saveCompareAndSwap()` (@jiaweipan)
- Enh #25: Raise minimum `PHP` version to `8.0` (@terabytesoftw)
- Chg #21: Update `yiisoft/http` dependency (@devanych)

## 2.0.0 August 15, 2021

- Enh #19: Introduce `LimitPolicyInterface`, `StorageInterface`, `TimerInterface`. Rename `Middleware` to
  `LimitRequestsMiddleware` (@kafkiansky, @samdark)

## 1.0.1 June 08, 2021

- Bug #14: Throw exception on call `getCacheKey()` in counter without the specified ID (@vjik)

## 1.0.0 June 07, 2021

- Initial release.
