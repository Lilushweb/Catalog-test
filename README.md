# Catalog

Тестовый каталог товаров на `Laravel 12` + `Vue 3` + `Inertia.js` + `Sanctum`.

Проект включает:

- публичный каталог товаров с пагинацией;
- страницу карточки товара;
- фильтрацию товаров по категории;
- REST API для товаров и категорий;
- административную часть с авторизацией по токену;
- сиды с тестовыми категориями, товарами и администратором;
- запуск через `Docker Compose`.

## Стек

- PHP 8.4
- Laravel 12
- PostgreSQL 15
- Vue 3
- Inertia.js
- Vite
- Laravel Sanctum
- Tailwind CSS

## Функциональность

### Публичная часть

- `GET /` — список товаров с пагинацией
- `GET /product/{id}` — карточка товара
- фильтр по категории на странице каталога

### Административная часть

- `GET /admin/login` — вход администратора
- `GET /admin/products` — управление товарами
- `GET /admin/products/create` — создание товара
- `GET /admin/products/{id}/edit` — редактирование товара

### API

Публичные эндпоинты:

- `GET /api/products`
- `GET /api/products/{id}`
- `GET /api/categories`
- `POST /api/login`

Защищённые эндпоинты:

- `POST /api/products`
- `PUT /api/products/{id}`
- `PATCH /api/products/{id}`
- `DELETE /api/products/{id}`
- `POST /api/logout`

## Модели

### Category

- `id`
- `name`
- `description`
- `created_at`
- `updated_at`

### Product

- `id`
- `name`
- `description`
- `price`
- `category_id`
- `created_at`
- `updated_at`
- `deleted_at`

Связи:

- товар принадлежит одной категории;
- категория содержит много товаров.

## Тестовые данные

Сидер создаёт администратора:

- email: `admin@example.com`
- password: `password`

## Запуск через Docker

### 1. Поднять контейнеры

```bash
docker compose build
docker compose up -d
```

### 2. Установить PHP-зависимости

```bash
docker compose exec app composer install
```

### 3. Подготовить окружение

Если `.env` уже есть, этот шаг можно пропустить.

```bash
cp .env.example .env
```

### 4. Сгенерировать ключ приложения

```bash
docker compose exec app php artisan key:generate
```

### 5. Выполнить миграции и сиды

```bash
docker compose exec app php artisan migrate --seed
```

### 6. Установить frontend-зависимости

```bash
docker compose exec app npm install
```

### 7. Собрать Vite assets

Production-сборка:

```bash
docker compose exec app npm run build
```

Или dev-режим:

```bash
docker compose exec app npm run dev -- --host 0.0.0.0 --port 5173
```

## Доступ к приложению

- приложение: `http://localhost:8080`
- PostgreSQL с хоста: `127.0.0.1:5433`

## Основные Docker-настройки

Сервис `db` использует:

- database: `building`
- username: `admin`
- password: `admin`

Внутри docker-сети Laravel подключается к БД по:

- host: `catalog_db`
- port: `5432`

## Полезные команды

```bash
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint
```

## Проверка

API-тесты:

```bash
docker compose exec app php artisan test tests/Feature/Api/ProductApiTest.php
```

## Валидация товара

При создании и обновлении товара проверяются:

- `name` — обязательное строковое поле;
- `price` — обязательное число больше `0`, максимум `2` знака после точки;
- `category_id` — обязательная существующая категория.

## Примечания

- Для административных действий используется токен Sanctum.
- Токен сохраняется на фронтенде в `localStorage`.
- Для товаров включено мягкое удаление (`Soft Deletes`).
