SVR DATA для Open-Admin
=========================

## Установка

```
$ composer require svr/data

$ php artisan migrate --path=vendor/svr/data/database/migrations

$ php artisan db:seed --class=Svr\Data\Seeders\DataSeeder

```
## Добавление пунктов меню.
```
$ php artisan admin:import svr-data

```

## Usage

[//]: # (See [wiki]&#40;http://open-admin.org/docs/en/extension-helpers&#41;)

License
------------

[//]: # (Licensed under [The MIT License &#40;GPL 3.0&#41;]&#40;LICENSE&#41;.)


## Permission.
Если пермиссий на роуты в БД нет (проверка по слагу), создаются через команду
```
$ php artisan migrate --path=vendor/svr/data/database/migrations
```

## Пункты меню
Устанавливаются только если отсутствуют в БД. Проверка по uri. URI должен содержать в начале `data`
