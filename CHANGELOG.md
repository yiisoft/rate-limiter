# Yii Rate Limiter Change Log

## 2.0.1 under development

- Chg #21: Update `yiisoft/http` dependency (devanych)

## 2.0.0 August 15, 2021

- Enh #19: Introduce `LimitPolicyInterface`, `StorageInterface`, `TimerInterface`. Rename `Middleware` to `LimitRequestsMiddleware` (kafkiansky, samdark)

## 1.0.1 June 08, 2021

- Bug #14: Throw exception on call `getCacheKey()` in counter without the specified ID (vjik)

## 1.0.0 June 07, 2021

- Initial release.
