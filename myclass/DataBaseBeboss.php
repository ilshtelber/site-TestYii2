<?php
namespace app\myclass;

class DataBaseBeboss extends \PDO{
	public $arrID = array();
	private $count;
	//конструктор
	public function __construct($dbhost,$dbname,$dbuser,$dbpassword,$sqldump = ''){
		try{
			parent::__construct("mysql:host=$dbhost; dbname=$dbname",$dbuser, $dbpassword);
			if(!$this->query("SHOW TABLES WHERE Tables_in_$dbname = 'groups'")->fetch() || !$this->query("SHOW TABLES WHERE Tables_in_$dbname = 'products'")->fetch()){
				if (file_exists($sqldump)) $this->exec(file_get_contents($sqldump));
				if(!$this->query("SHOW TABLES WHERE Tables_in_$dbname = 'groups'")->fetch() || !$this->query("SHOW TABLES WHERE Tables_in_$dbname = 'products'")->fetch()) die("Error in tables 'groups and products'");
			}
		}
		catch (PDOException $e){
			echo "невозможно установить соеденение ",$e->getMessage();
		}
	}
	//записывает в массив arrID данные, те какие ветки (id группы) стоит расскрывать
	protected function branch($get){
		$this->arrID[] = $get;
		$table = $this->query("SELECT * FROM groups WHERE id = ".$this->query("SELECT * FROM groups WHERE id = $get")->fetch()[1]);
		if(!$table) return 0;
		if(!$table->rowCount()) return 0;
		$get = $table->fetch()[0];
		$this->branch($get);
	}
	//выводим таблицу group через рекурсию
	protected function group($parent,$get){
		$table = $this->query("SELECT * FROM groups WHERE id_parent = $parent");
		echo "<ul>";
		while($value = $table->fetch())
		{
			if($value['id']==$get) echo "<li>".$value['name'].' ('.$this->productsCount($value['id']).')'."</li>";
			else echo "<li><a href='?group=$value[id]'>".$value['name'].' ('.$this->productsCount($value['id']).')'."</a></li>";
			foreach($this->arrID as $id) if($value['id'] == $id) $this->group($value['id'],$get);
		}
		echo "</ul>";
	}
	//производит подсчет кол-во продуктов в группе
	private function productsCountRecursive($get){
		$this->count += $this->query("SELECT * FROM products WHERE id_group = $get")->rowCount();
		$table_groips = $this->query("SELECT * FROM groups WHERE id_parent = $get");
		while($value = $table_groips->fetch()) $this->productsCountRecursive($value['id']);
		return $this->count;
	}
	//выводит ко-во продуктов в группе
	public function productsCount($get = 0){
		$this->count = 0;
		$get = intval($get,10);
		return $this->productsCountRecursive($get);
	}
	//выводит таблицу products определенной группы
	public function products($get = 0){
		$get = intval($get,10);
		if($get == 0){
			$table_products = $this->query("SELECT * FROM products");
			while($value = $table_products->fetch()) echo "<div>".$value['name']."</div>\n";
			return;
		}
		$table_products = $this->query("SELECT * FROM products WHERE id_group = $get");
		while($value = $table_products->fetch()) echo "<div>".$value['name']."</div>\n";
		$table_groips = $this->query("SELECT * FROM groups WHERE id_parent = $get");
		while($value = $table_groips->fetch()) $this->products($value['id']);
	}
	//проверка входных данных и вывод
	public function output($group = false){
		if(!is_numeric($group)) $group = false;
		$this->branch(intval($group,10));
		$this->group(0,intval($group,10));
	}
}