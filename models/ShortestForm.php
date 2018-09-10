<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\vertex;
use app\models\graph;
use app\models\edge;
use yii\web\HttpException;
use app\myclass\Node;

/**
 * данный модель расчитывает кратчайший путь от одного вершины к другому
 */
class ShortestForm extends Model
{
    public $from;
    public $to;
    public $graph_id;

    public $route;
    public $total;

    public function rules()
    {
        return [
            [['from', 'to'], "required"],
            [['from', 'to'], "validateRoute"],
            ['graph_id',"integer"],
            ['graph_id',"validateGraph"]
        ];
    }

    //данное правило позволяет проверить, есть ли заданные вершины в таблице vertices
    public function validateRoute($attribute, $params)
    {
        $vertex = $this->$attribute;
        $alias = vertex::find()->select('alias')->where(['graph_id' => $this->graph_id])->asArray()->column();
        if( !in_array($vertex, $alias) )
            $this->addError($attribute, "vertex $vertex does not exist, you can use the following vertices: ".implode(",", $alias));
    }

    //данное правило позволяет проверить, есть ли текущий граф в таблице graphs
    public function validateGraph($attribute, $params)
    {
        if( !graph::find()->where(['id' => $this->$attribute])->exists() ) $this->addError($attribute, "graph not found");
    }


    //производит подчет кратчайщего пути в графе
    public function calculation(){
    	$routes = Edge::find()->where(['graph_id'=>$this->graph_id])->asArray()->all();
    	$nodes = array();
    	$paths = array();

		foreach ($routes as $route) {
			if(!array_key_exists($route['vertex_from'], $nodes)){
				$from_node = new Node($route['vertex_from']);
				$nodes[$from_node->getId()] = $from_node;
			}
			else{
				$from_node = $nodes[$route['vertex_from']];
			}

			if(!array_key_exists($route['vertex_to'], $nodes)){
				$to_node = new Node($route['vertex_to']);
				$nodes[$to_node->getId()] = $to_node;
			}
			else{
				$to_node = $nodes[$route['vertex_to']];
			}

			$from_node->connect($to_node, $route['weight']);
		}

		$start_node = $nodes[$this->from];
		$end_node = $nodes[$this->to];

		$paths[] = array($start_node);
		$startingNode = $start_node;
		$endingNode = $end_node;

		$path = array();
		$potential = array();
		$this->calculatePotentials($startingNode, $nodes, $paths, $potential);
		while ( $endingNode->getId() != $startingNode->getId() ) {
            $path[] = $endingNode;
            $endingNode = $endingNode->getPotentialFrom();
            if( !$endingNode ) throw new HttpException(415 ,"Error route");
        }
        $path[] = $startingNode;
        $path = array_reverse($path);
        $literal = array();
        foreach ( $path as $p ) $literal[] = $p->getId();
		$this->route = $literal;
		$this->total = $potential[$this->to];

		return $this;
    }

    public function calculatePotentials(Node $node, &$nodes, &$paths, &$potential){
    	$connections = $node->getConnections();
    	$sorted = array_flip($connections);
    	krsort($sorted);
    	foreach ( $connections as $id => $distance ) {
            $nodes[$id]->setPotential($node->getPotential() + $distance, $node);
            $potential[$id] = $nodes[$id]->getPotential();
            foreach ( $paths as $path ) {
                $count = count($path);
                if ($path[$count - 1]->getId() === $node->getId()) $paths[] = array_merge($path, array($nodes[$id]));
                
            }
        }
        $node->markPassed();
        foreach ( $sorted as $id ) {
            $node = $nodes[$id];
            if (! $node->isPassed()) $this->calculatePotentials($node, $nodes, $paths, $potential);
        }
    }

    //тут мы выводим результат
    public function fields(){
        return [
            "id" => 'graph_id',
            "from" => 'from',
            "to" => 'to',
            "route"=>'route',
            "total"=>'total'
        ];
    }
}