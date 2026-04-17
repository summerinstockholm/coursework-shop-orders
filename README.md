# Coursework Shop Orders

Учебный проект: **система управления заказами интернет-магазина**.

Это локальное веб-приложение на **PHP + MySQL**, запускаемое через **Docker Compose**.
Проект предназначен для курсовой работы и позволяет:

- вести справочники:
  - покупатели
  - категории
  - производители
  - склады
  - товары
- работать с операционными сущностями:
  - заказы
  - оплаты
  - доставки
- формировать отчеты:
  - заказы по покупателю
  - заказы за период
  - заказы по статусу
  - топ-10 заказов по сумме
  - топ-10 товаров по количеству в заказах

---

## Используемый стек

- PHP 8.4 + Apache
- MySQL 8.0
- phpMyAdmin
- Docker Compose

---

## Структура проекта

```text
coursework-shop-orders/
├── .env.example
├── .gitignore
├── README.md
├── docker-compose.yml
├── database/
│   ├── 01_schema.sql
│   ├── 02_seed.sql
│   └── queries/
├── docker/
│   └── php/
│       └── Dockerfile
└── src/
    ├── index.php
    ├── assets/
    │   └── css/
    │       └── style.css
    ├── config/
    │   ├── config.example.php
    │   └── config.local.php
    ├── includes/
    │   ├── db.php
    │   ├── footer.php
    │   ├── header.php
    │   └── menu.php
    ├── customers/
    ├── categories/
    ├── manufacturers/
    ├── warehouses/
    ├── products/
    ├── orders/
    ├── payments/
    ├── deliveries/
    └── reports/
```

---

## Что нужно установить перед запуском

На машине должны быть установлены:

- Docker Desktop
- Docker Compose

Проверка:

```bash
docker --version
docker compose version
```

---

## Как стороннему человеку поднять проект локально

### 1. Клонировать или скачать проект

Если проект в Git:

```bash
git clone <repo_url>
cd coursework-shop-orders
```

Если проект передан архивом:

1. распаковать архив
2. открыть терминал в корне проекта `coursework-shop-orders`

---

### 2. Создать файл `.env`

В проекте лежит шаблон `.env.example`.
Из него нужно создать рабочий `.env`:

```bash
cp .env.example .env
```

---

### 3. Заполнить `.env`

Открыть `.env` и указать **свои реальные пароли**.

Пример рабочего файла:

```env
APP_PORT=8080
PHPMYADMIN_PORT=8081
MYSQL_PORT=3307

MYSQL_DATABASE=shop_orders
MYSQL_USER=shop_user
MYSQL_PASSWORD=your_strong_app_password
MYSQL_ROOT_PASSWORD=your_strong_root_password

APP_BASE_URL=
APP_NAME=Система управления заказами интернет-магазина
DB_HOST=db
DB_PORT=3306
DB_CHARSET=utf8mb4
```

---

## Что означает каждая переменная в `.env`

### `APP_PORT`
Порт, на котором будет открываться само приложение.

По умолчанию:

```text
http://localhost:8080
```

### `PHPMYADMIN_PORT`
Порт, на котором будет доступен phpMyAdmin.

По умолчанию:

```text
http://localhost:8081
```

### `MYSQL_PORT`
Порт MySQL для подключения из внешнего клиента, например:

- DBeaver
- DataGrip
- TablePlus

По умолчанию:

```text
127.0.0.1:3307
```

### `MYSQL_DATABASE`
Имя базы данных.

Для текущего проекта:

```env
MYSQL_DATABASE=shop_orders
```

### `MYSQL_USER`
Пользователь MySQL, под которым работает само PHP-приложение.

Для текущего проекта:

```env
MYSQL_USER=shop_user
```

### `MYSQL_PASSWORD`
Пароль пользователя приложения `shop_user`.

Именно этот пользователь используется приложением для работы с БД.

### `MYSQL_ROOT_PASSWORD`
Пароль пользователя `root` в MySQL.

Он нужен для:

- входа под root в phpMyAdmin
- подключения внешним SQL-клиентом как root
- ручного администрирования базы

### `APP_BASE_URL`
Базовый URL приложения.
Для локального запуска оставить пустым:

```env
APP_BASE_URL=
```

### `APP_NAME`
Название приложения, которое отображается в интерфейсе.

### `DB_HOST`
Хост базы данных внутри Docker Compose.
Для проекта не менять:

```env
DB_HOST=db
```

### `DB_PORT`
Порт MySQL внутри Docker-сети.
Для проекта не менять:

```env
DB_PORT=3306
```

### `DB_CHARSET`
Кодировка подключения к БД.
Для проекта не менять:

```env
DB_CHARSET=utf8mb4
```

---

## Где указывать реальные пароли

Реальные пароли нужно указывать **только в `.env`**.

Их **не нужно** прописывать:

- в `docker-compose.yml`
- в `config.example.php`
- в `config.local.php`
- в любых PHP-файлах проекта

PHP-приложение уже читает параметры подключения из переменных окружения.

---

## Как запустить проект

Из корня проекта выполнить:

```bash
docker compose up --build -d
```

---

## Как проверить, что проект поднялся

```bash
docker compose ps
```

Ожидается, что будут запущены контейнеры:

- `coursework_app`
- `coursework_db`
- `coursework_phpmyadmin`

---

## Куда заходить после запуска

### Приложение

```text
http://localhost:8080
```

### phpMyAdmin

```text
http://localhost:8081
```

---

## Как войти в phpMyAdmin

На форме входа использовать:

- **сервер**: `db`
- **пользователь**:
  - `shop_user`
  - или `root`
- **пароль**:
  - соответствующий из `.env`

### Рекомендуемый вариант

Для обычной работы:

- user: `shop_user`
- password: значение `MYSQL_PASSWORD`

Для администрирования:

- user: `root`
- password: значение `MYSQL_ROOT_PASSWORD`

---

## Как подключиться к БД из DBeaver

Параметры подключения:

- **Host**: `127.0.0.1`
- **Port**: `3307`
- **Database**: `shop_orders`
- **User**: `shop_user`
- **Password**: значение `MYSQL_PASSWORD`

Если нужен root-доступ:

- **User**: `root`
- **Password**: значение `MYSQL_ROOT_PASSWORD`

---

## Как создается база данных

При первом запуске пустой БД MySQL автоматически выполняет:

1. `database/01_schema.sql`
2. `database/02_seed.sql`

То есть автоматически:

- создаются таблицы
- загружаются тестовые данные

### Важно
Это выполняется **только при первой инициализации пустого volume MySQL**.

---

## Как полностью пересоздать базу с нуля

Если нужно снова развернуть БД с чистого листа и заново автоматически залить:

- схему
- тестовые данные

нужно выполнить:

```bash
docker compose down -v
docker compose up --build -d
```

### Что делает `down -v`
Удаляет volume MySQL.
После этого база считается пустой и при следующем старте снова выполняются:

- `01_schema.sql`
- `02_seed.sql`

---

## Как остановить проект

```bash
docker compose down
```

---

## Как посмотреть логи

### Все сервисы сразу

```bash
docker compose logs -f
```

### Только приложение

```bash
docker compose logs -f app
```

### Только база

```bash
docker compose logs -f db
```

### Только phpMyAdmin

```bash
docker compose logs -f phpmyadmin
```

---

## Конфигурационные файлы PHP

В проекте есть два файла:

- `src/config/config.example.php`
- `src/config/config.local.php`

Они уже настроены на чтение параметров из переменных окружения.

### Практический смысл
Обычно **редактировать их не нужно**.
Рабочие значения задаются через `.env`.

---

## Что уже реализовано

### Справочники
- покупатели
- категории
- производители
- склады
- товары

### Операционные сущности
- заказы
- оплаты
- доставки

### Отчеты
- заказы по покупателю
- заказы за период
- заказы по статусу
- топ-10 заказов по сумме
- топ-10 товаров по количеству в заказах

---

## Полезные URL после запуска

### Главная
```text
http://localhost:8080
```

### Покупатели
```text
http://localhost:8080/customers/list.php
```

### Категории
```text
http://localhost:8080/categories/list.php
```

### Производители
```text
http://localhost:8080/manufacturers/list.php
```

### Склады
```text
http://localhost:8080/warehouses/list.php
```

### Товары
```text
http://localhost:8080/products/list.php
```

### Заказы
```text
http://localhost:8080/orders/list.php
```

### Оплаты
```text
http://localhost:8080/payments/list.php
```

### Доставки
```text
http://localhost:8080/deliveries/list.php
```

### Отчеты
```text
http://localhost:8080/reports/orders_by_customer.php
http://localhost:8080/reports/orders_by_period.php
http://localhost:8080/reports/orders_by_status.php
http://localhost:8080/reports/top_orders.php
http://localhost:8080/reports/top_products.php
```

---

## Быстрый сценарий запуска

```bash
git clone <repo_url>
cd coursework-shop-orders
cp .env.example .env
# отредактировать .env и задать свои пароли
docker compose up --build -d
```

После этого открыть:

- `http://localhost:8080`
- `http://localhost:8081`

---

## Назначение проекта

Проект предназначен как учебное веб-приложение работы:

- ведение данных о покупателях
- ведение данных о товарах
- оформление и сопровождение заказов
- ведение оплат и доставок
- формирование отчетов
