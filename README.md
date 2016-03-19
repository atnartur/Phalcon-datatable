# phalcon-datatable

Класс для работы с Datatables.js на серверв. Написан с использованием PHP-фреймворка [Phalcon 2](http://phalconphp.com).

## Примеры

Базовый пример:

```php
$datatable = new Datatable('users'); // инициализация класса для таблицы users

// подключение параметров

// установка значения поиска
$datatable->set('search', $this->request->get('search', 'string'));

// установка количества записей для выдачи
$datatable->set('length', $this->request->get('length', 'int'));

// установка номера строки, с которой нужно сделать выборку
$datatable->set('start', $this->request->get('start', 'int'));

// установка списка колонок для выборки
$datatable->set('columns', array('id', 'name', 'email'));

// установка значений для сортировки
$datatable->set('order', $this->request->get('order'));

// если идет запрос из Datatables.js (эта библиотека всегда посылает параметр draw)
if($this->request->has('draw')) 
    // возвращаем выдачу для Datatables.js
    return $this->response->setJsonContent($datatable->toDatatablesArray());
else
    // иначе возвращаем массив простых объектов моделей Phalcon
    return $this->response->setJsonContent($datatable->toObjects()->toArray());
```

Больше примеров в папке `examples`.

## Автор

Оригинальный код написан [Атнагуловым Артуром](http://atnartur.ru) <artur@clienddev.ru> при поддержке [ClienDDev team](http://clienddev.ru) под лицензией MIT.