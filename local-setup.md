# Локальная установка Career Tracker

## Быстрый старт с Docker

### 1. Установка Docker
Убедитесь, что у вас установлен Docker Desktop.

### 2. Запуск проекта
```bash
# Клонируем проект (или используем текущую папку)
cd CareerTracker

# Запускаем Docker контейнеры
docker-compose up -d

# Ждем пока все сервисы запустятся (1-2 минуты)
```

### 3. Доступ к приложению
- **WordPress сайт**: http://localhost:8080
- **PhpMyAdmin**: http://localhost:8081 (для управления БД)

### 4. Первоначальная настройка WordPress

1. Откройте http://localhost:8080
2. Выберите язык (English/Русский)
3. Заполните форму установки:
   - Название сайта: `Career Tracker`
   - Имя пользователя: `admin`
   - Пароль: `your_strong_password`
   - Email: `your-email@example.com`
4. Нажмите "Установить WordPress"

### 5. Активация темы

1. Войдите в админ-панель: http://localhost:8080/wp-admin
2. Перейдите в "Внешний вид" → "Темы"
3. Активируйте тему "Career Tracker"

## Альтернативная установка (XAMPP/WAMP/MAMP)

### 1. Скопируйте файлы
```bash
# Скопируйте все файлы проекта в:
# XAMPP: /xampp/htdocs/wordpress/wp-content/themes/career-tracker/
# WAMP: /wamp/www/wordpress/wp-content/themes/career-tracker/
# MAMP: /Applications/MAMP/htdocs/wordpress/wp-content/themes/career-tracker/
```

### 2. Создайте базу данных
- Откройте phpMyAdmin (обычно http://localhost/phpmyadmin)
- Создайте БД с именем `wordpress`

### 3. Установите WordPress
- Скачайте WordPress с wordpress.org
- Разархивируйте в папку веб-сервера
- Запустите установку

## Тестирование функций

После установки протестируйте:

1. **Создание проекта**:
   - Нажмите "Create Project"
   - Заполните форму
   - Проверьте создание

2. **Редактирование проекта**:
   - Откройте созданный проект
   - Нажмите "Edit Project"
   - Измените данные

3. **Создание секций**:
   - Нажмите "Add Section"
   - Создайте секцию с заголовком

4. **Добавление контента**:
   - Нажмите "Add Content" в секции
   - Попробуйте все типы контента:
     - Rich Text (с форматированием)
     - Subtitle
     - Code Block
     - Image Upload

5. **Rich Text Editor**:
   - Проверьте все кнопки панели инструментов
   - Попробуйте горячие клавиши (Ctrl+B, Ctrl+I, etc.)
   - Загрузите изображение

## Остановка сервисов

```bash
# Остановить контейнеры
docker-compose down

# Остановить и удалить данные
docker-compose down -v
```

## Полезные команды

```bash
# Посмотреть логи WordPress
docker-compose logs wordpress

# Посмотреть логи БД
docker-compose logs db

# Перезапустить сервисы
docker-compose restart

# Зайти в контейнер WordPress
docker-compose exec wordpress bash
```