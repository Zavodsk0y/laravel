# API-Документация

Примечание: здесь рассмотрены только верные случаи ввода данных/файлов пользователем. Случаи неправильного ввода предусмотрены проектом, просто они не были включены сюда

Для удобного тестирования используйте программу Postman

## Аутентификация

### Регистрация

**URL**: http://localhost/api-file/registration

**Method:** POST

**Request Body:**
```yaml
    {
        "email": "user1@test.ru",
        "password": "Qa1",
        "first_name": "name",
        "last_name": "last_name"
    }
```

**Request Response:**

```yaml
    {
        "success": true,
        "code": 201,
        "message": "Success",
        "token": "???"
}
```

### Авторизация

**URL**: http://localhost/api-file/authorization

**Method:** POST

**Request Body:**

```yaml
    {
        "email": "user1@test.ru",
        "password": "Qa1",
    }
```
**Request Response:**

```yaml
    {
        "success": true,
        "code": 200,
        "message": "Success",
        "token": "???"
}
```

### Выход

**URL**: http://localhost/api-file/logout

**Method:** GET

**Request addition Header**: Authorization: Bearer {token}

**Описание** - очищает все существующие токены для данного пользователя

## Работа с файлами

### Загрузка файлов

**URL**: http://localhost/api-file/files

**Method:** POST

**Body:** form-data

**Request Body:**

| Параметр | Описание          |
|----------|-------------------|
| files[]  | Файлы для загрузки |


**Request Response:**

```yaml
    [
      {
        "success": false,
        "message": "The file must be a file of type: doc, pdf, docx, zip, jpeg, jpg, png.",
        "name": "Отчет 2 проверочная.odt"
      },
      {
        "success": false,
        "message": "The file must not be greater than 2048 kilobytes.",
        "name": "Snake_River_(5mb).jpg"
      },
      {
        "success": true,
        "code": 200,
        "message": "Success",
        "name": "z0bug4fwyhc51.png",
        "url": "http://localhost/api/files/K9PdIPqn1i",
        "file_id": "K9PdIPqn1i"
      }
    ]
```

### Удаление файла

**URL**: http://localhost/api-file/files/{file_id}

**Method:** DELETE

**Request addition Header**: Authorization: Bearer {token}

**Описание** - удаляем файл по его идентификатору

```yaml
    {
        "success": true,
        "code": 200,
        "message": "File deleted"
    }
```

### Редактирование имени файла

**URL**: http://localhost/api-file/files/{file_id}

**Method:** PATCH

**Request addition Header**: Authorization: Bearer {token}

**Описание** - меняем имя файла

**Request Body:**

```yaml
  {
      "name": "File name updated"
  }
```
**Request Response:**

```yaml
  {
      "success": true,
      "code": 200,
      "message": "Renamed"
  }
```

### Скачивание файла

**URL**: http://localhost/api-file/files/{file_id}

**Method:** GET

**Request addition Header**: Authorization: Bearer {token}

**Описание** - получаем файл на скачивание

### Просмотр файлов пользователя

**URL**: http://localhost/api-file/files/disk

**Method:** GET

**Request addition Header**: Authorization: Bearer {token}

**Request Response:**

```yaml
[
  {
    "file_id": "ILYTykPRfG",
    "name": "File name updated.jpg",
    "code": 200,
    "url": "http://localhost/api/files/ILYTykPRfG",
    "accesses": [
      {
        "fullname": "name last_name",
        "email": "user1@test.ru",
        "type": "author"
      }
    ]
  }
]
```

### Просмотр файлов, к которым имеет доступ пользователь

**URL**: http://localhost/api-file/files/shared

**Method:** GET

**Request addition Header**: Authorization: Bearer {token}

**Request Response:**

```yaml
[
  {
    "file_id": "K93d4Pqn1i",
    "code": 200,
    "name": "z0bug4fwyhc51.png",
    "url": "http://localhost/api/files/K93d4Pqn1i",
  }
]
```

## Работа с доступом

### Добавление прав доступа

**URL**: http://localhost/api-file/files/{file_id}/accesses

**Method:** POST

**Request addition Header**: Authorization: Bearer {token}

**Request Body:**
```yaml
    {
        "email": "user2@test.ru",
    }
```

**Request Response:**

```yaml
[
  {
    "fullname": "name last_name",
    "email": "user1@test.ru",
    "type": "author",
    "code": 200
  },
  {
    "fullname": "firstname lastname",
    "email": "user2@test.ru",
    "type": "co-author",
    "code": 200
  }
]
```

### Удаление прав доступа

**URL**: http://localhost/api-file/files/{file_id}/accesses

**Method:** DELETE

**Request addition Header**: Authorization: Bearer {token}

**Request Body:**
```yaml
    {
        "email": "user2@test.ru",
    }
```

**Request Response:**

```yaml
[
  {
    "fullname": "name last-name",
    "email": "user1@test.ru",
    "code": 200,
    "type": "author"
  }
]
```


