# Slot Service API

Тестовое задание бронирования слотов.
Сервис предоставляет API-эндпоинты для просмотра доступности слотов,
создания идемпотентных «холдов», их атомарного подтверждения и отмены с
корректным ведением кеша.

## Требования

-   PHP 8.2+
-   Composer
-   MySQL 8+
-   Redis (используется для блокировок кеша)

## Установка

``` bash
cp .env.example .env
composer install
php artisan key:generate
# Настройте параметры базы данных в .env
php artisan migrate
```

## Запуск приложения

Создаем слоты
``` bash
php artisan slots:create
```

Запуск сервера
``` bash
php artisan serve
```

Генерация документации
``` bash
php artisan openapi:generate
```


API по умолчанию будет доступно по адресу:\
`http://127.0.0.1:8000`

## Обзор API

Для Post запросов требуется заголовок `Idempotency-Key`.

Метод               Endpoint                      Описание
  ------------------- ----------------------------- --------------------------
GET                 `/api/slots/availability`          Кэшированная доступность всех слотов (активные холды уменьшают значение `remaining`).

POST                `/api/slots/{slot_uuid}/hold`      Создать hold для указанного слота.

POST                `/api/holds/{hold_uuid}/confirm`   Подтвердить холд и атомарно уменьшить доступность слота.

POST                `/api/holds/{hold_uuid}/cancel`    Отменить холд и вернуть слот в пул доступных.

  ----------------------------------------------------------------------------

## Примеры использования

Допустим, сервис запущен локально, и слот с ID `1` существует:

Список доступных слотов
``` bash
curl -i   -H "Accept: application/json" http://127.0.0.1:8000/api/slots/availability
```

Создать новый холд (замените {slot_uuid} на существующий) 
``` bash
curl -i   -H "Accept: application/json"   -H "Idempotency-Key: 11111111-1111-1111-1111-111111111111"   -X POST http://127.0.0.1:8000/api/slots/{slot_uuid}/hold
```

Повторить тот же запрос (идемпотентный вызов)
``` bash
curl -i   -H "Accept: application/json"   -H "Idempotency-Key: 11111111-1111-1111-1111-111111111111"   -X POST http://127.0.0.1:8000/api/slots/{slot_uuid}/hold
```

Подтвердить холд (замените {hold_uuid} на существующий)
``` bash
curl -i   -H "Accept: application/json"   -X POST http://127.0.0.1:8000/api/holds/{hold_uuid}/confirm
```

Отменить холд
``` bash
curl -i   -H "Accept: application/json"   -X POST http://127.0.0.1:8000/api/holds/{hold_uuid}/cancel
```

Пример конфликта, когда слот исчерпан
``` bash
curl -i   -H "Accept: application/json"   -H "Idempotency-Key: 22222222-2222-2222-2222-222222222222"   -X POST http://127.0.0.1:8000/api/slots/{slot_uuid}/hold
```

Эндпоинт `GET /api/slots/availability` кэширует результаты на 5--15
секунд и использует Redis-блокировки для предотвращения «штормов» кеша.\
Подтверждение или отмена холда очищает соответствующий кеш.
