<?php
/**
 * Пример выборки с использованием SQL JOIN
 */

namespace App\Modules\Admin\Controllers;

class ExampleController extends \Phalcon\Mvc\Controller {
	public function indexAction(){
		$datatable = new \Datatable('example'); // инициализация класса для таблицы example
		// указываем, какие колонки нам необходимы
		$datatable->set('columns', array(
			'id', 
			'name', 
			'author_name'
		));

		// указываем, какие колонки нам необходимы от Phalcon Query Builder
		$datatable->qb->columns(array(
			'App\Models\Example.id as id', 
			'App\Models\Example.name as name', 
			'App\Models\Users.name as author_name'
		));
		
		// используем SQL JOINs согласно документации Phalcon
		$datatable->qb->leftJoin('App\Models\Users', 'App\Models\Example.user_id = App\Models\Users.id');

		// Подключаем параметры Datatables (подробнее об этом в файле simple.php)
		$datatable->set('search', $this->request->get('search', 'string'));
		$datatable->set('length', $this->request->get('length', 'int'));
		$datatable->set('start', $this->request->get('start', 'int'));
		$datatable->set('order', $this->request->get('order'));

		// выводим результат
		if($this->request->has('draw'))
			return $this->response->setJsonContent($datatable->toDatatablesArray());
		else
			return $this->response->setJsonContent($datatable->toObjects()->toArray());
	}
}