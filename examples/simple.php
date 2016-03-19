<?php
/**
 * Пример простой выборки с подключением параметров Datatables
 */

namespace App\Modules\Admin\Controllers;

class UsersController extends \Phalcon\Mvc\Controller{
	public function indexAction(){
		$datatable = new \Datatable('users'); // инициализация класса для таблицы users

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
	}
}