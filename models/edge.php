<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\vertex;
use app\models\graph;

class edge extends ActiveRecord
{
    public static function tableName(){
        return 'edges';
    }

    //задаем правила для ввода данных
    public function rules(){
        return [
            [['vertex_from','vertex_to','weight'],"required"],
            [['weight','graph_id'],"integer"],
            ['graph_id',"validateGraph"],
            [['vertex_from','vertex_to'],"validateRoute"]
        ];
    }

    //позволяет проверить, есть ли в таблице vertices вершины которое мы задём
    public function validateRoute($attribute, $params)
    {
        $vertex = $this->$attribute;
        $alias = vertex::find()->select('alias')->where(['graph_id' => $this->graph_id])->asArray()->column();
        if( !in_array($vertex, $alias) )
            $this->addError($attribute, "vertex $vertex does not exist, you can use the following vertices: ".implode(",", $alias));
    }

    //проверяет, есть ли заданный граф (где мы передаем id графа) в таблице graphs
    public function validateGraph($attribute, $params)
    {
        if( !graph::find()->where(['id' => $this->$attribute])->exists() ) $this->addError($attribute, "graph not found");
    }
}