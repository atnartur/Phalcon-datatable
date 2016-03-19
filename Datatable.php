<?php
/**
 * @author Artur Atnagulov (atnartur), ClienDDev team (clienddev.ru)
*/

use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Query\Builder as QueryBuilder;

class Datatable{
	/** @var string Неймспейс моделей */
	const models_namespace = 'App\Models\\';

	/** @var string Базовая таблица, из которой будет делаться выборка */
	private $table;

	/** @var integer Количество строк */
	private $length = 0;

	/** @var integer Номер строки, с которой нужно начать выборку */
	private $start = 0;

	/** @var string Слово для поиска */
	private $search = '';

	/** @var array Параметры сортировки */
	private $order = array();

	/** @var array Список колонок для выборки. Если не задано - возвращаются все колонки. */
	private $columns = array();

	/** @var array Массив с ошибками */
	private $errors = array();


	/** @var Phalcon\Mvc\Model\Query\Builder Экземлпяр Phalcon Query Builder */
	public $qb;

	/**
	 * Конструктор
	 *
	 * @param string $table название таблицы без namespace в нижнем регистре
	 */
	function __construct($table){
		$this->qb = new QueryBuilder();
		$this->table = $table;
		$this->qb->from($this->_model_name_with_namespace());
	}

	/**
	 * Преобразует слова в CamelCase
	 * @param  string $input     входная строка
	 * @param  string $separator разделитель
	 * @return string            CamelCase строка
	 */
	private static function camelize($input, $separator = '_')
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }


	/**
	 * @return string Возвращает название таблицы вместе с неймспейсом
	 */
	function _model_name_with_namespace(){
		return self::models_namespace . self::camelize($this->table);
	}


	/**
	 * Метод для установки значений
	 * 
	 * @param string $name Название параметра
	 * @param string $value Значение параметра
	 */
	function set($name, $value = ''){
		if($value != '')
			$this->$name = $value;

		if(is_array($value) && $name == 'search')
			$this->$name = $value['value'];
	}


	/**
	 * Подготовка к запросу. Делается все, кроме лимитирования (limit & offset)
	 */
	function _prepare(){
		if($this->search != ''){
			$qb2 = $this->qb;
			$search = $this->search;

			if(count($this->columns) == 0){
				$cols = array();
				foreach($qb2->getDI()->getShared('db')->describeColumns($this->table) as $col)
					$cols[] = $col->getName();
			}
			else
				$cols = $this->columns;

			$sql_arr = array();

			foreach($cols as $col){
				$sql_arr[] = '' . $col . ' LIKE \'%'.$search.'%\'';
			}

			$this->qb->andWhere(implode(' OR ', $sql_arr));
		}

		if(count($this->order) != 0){
			if(count($this->columns) == 0)
				$this->errors[] = 'Сортировка не работает из-за того, что не указаны столбцы таблицы';
			else{
				foreach($this->order as $column){
					$index = $column['column'];
					$direction = $column['dir'];

					$column_name = $this->columns[$index];

					$this->qb->orderBy($column_name . ' ' . strtoupper($direction));
				}
			}
		} 
	}


	/**
	 * Устанавливает лимит (limit & offset)
	 */
	function _limit(){
		if($this->length != 0)
			$this->qb->limit($this->length, $this->start);
	}


	/**
	 * Возвращает массив для Datatables
	 * 
	 * @return array Массив для ответа в Datatables
	 */
	function toDatatablesArray(){
		$this->_prepare();

		$qb2 = $this->qb;
		$filtered = count($qb2->getQuery()->execute()->toArray());

		$this->_limit();

		$array = $this->qb->getQuery()->execute()->toArray();	
		$res = array();
		$allowed_collumns = $this->columns;

		foreach($array as $row){
			$row_res = array();

			if(count($allowed_collumns) == 0){
				foreach($row as $col)
					$row_res[] = $col;
			}
			else{
				foreach($allowed_collumns as $col)
					$row_res[] = $row[$col];
			}


			$res[] = $row_res;
		}

		$return = array(
			'recordsTotal' => (int) (new Query("SELECT COUNT(id) as count FROM " . $this->_model_name_with_namespace(), $this->qb->getDI()))->execute()->toArray()['0']['count'],
			'recordsFiltered' => $filtered,
			'data' => $res
		);

		if(count($this->errors) != 0)
			$return['error'] = implode(',', $this->errors);

		return $return;
	}

	/**
	 * Возвращает простые объекты моделей
	 * 
	 * @return array простые объекты моделей
	 */
	function toObjects(){
		$this->_prepare();
		$this->_limit();
		return $this->qb->getQuery()->execute();
	}
}s