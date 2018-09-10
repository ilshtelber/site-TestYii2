<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\vertex;
use app\models\edge;

class graph extends ActiveRecord
{
    public $vertices;
    public $change;

	public static function tableName(){
        return 'graphs';
    }

    //связываем таблицу graphs с таблицой vertices
    public function getVertex(){
        return $this->hasMany(Vertex::className(), ['graph_id' => 'id']);
    }

    //связываем таблицу graphs c таблицой edges
    public function getEdge(){
        return $this->hasMany(Edge::className(), ['graph_id' => 'id']);
    }

    //задаем определенные правила для входных данных
    public function rules(){
        return [
            ["name","required"],
            ["name", 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            ['name', 'match', 'pattern' => '/^[a-z]\w*$/i'],
            ['name', 'string', 'length' => [3, 12]],
            [["id","name"],"unique"],
            ["vertices","each",'rule' => ['safe']],
            ['change', 'in', 'range' => ["add", "update", "delete"]],
            ['change', 'default', 'value' => 'add']
        ];
    }

    //данная функция срабатывает после добавление нового графа в таблицу graphs или обновление графа в таблице graphs, в зависемости от параметра change которое может имет только три значение. При change=add мы может добавить с этим графом новые вершины в таблицу vertices где они будут привязанны к добавленному или обовленному графу, при change=update во время изменения графа (например название графа) мы можем изменить так же и вершины в таблице vertices привязанные к этому графу, или вовсе при change=delete мы можем удалить некоторые ненужные вершины из таблицы vertices так же при изменение графа.
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);
        if($this->vertices){
            foreach ($this->vertices as $key => $value) {
                if($key == "alias") {$this->vertices[0]['alias'] = $value; unset($this->vertices['alias']);}
                if($key == "X") {$this->vertices[0]['X'] = $value; unset($this->vertices['X']);}
                if($key == "Y") {$this->vertices[0]['Y'] = $value; unset($this->vertices['Y']);}
            }
            $up = Yii::$app->getRequest()->getBodyParams()["change"];
            if($up === "delete" && Yii::$app->request->isPut){
                foreach ($this->vertices as $vertex) {
                    $model_delete = Vertex::findOne(['alias'=>$vertex,'graph_id' => $this->id]);
                    if($model_delete->graph_id === $this->id) $model_delete->delete();
                    Edge::deleteAll(['vertex_from' => $vertex,'graph_id' => $this->id]);
                    Edge::deleteAll(['vertex_to' => $vertex,'graph_id' => $this->id]);
                }
                $i = 1;
                foreach(Vertex::find()->where(['graph_id' => $this->id])->all() as $vertex){
                    $vertex->vertex_number = $i;
                    $vertex->save();
                    $i++;
                }
            }
            else{
                if($up === "update" && Yii::$app->request->isPut){
                	$this->unlinkAll("vertex",true);
                	$this->unlinkAll("edge",true);
                }
                foreach($this->vertices as $vertex) {
                    if( !in_array($vertex['alias'], Vertex::find()->select('alias')->where(['graph_id' => $this->id])->asArray()->column()) )
                    {
                        $vertex_model = new vertex();
                        $vertex_model->graph_id = $this->id;
                        $vertex_model->vertex_number = vertex::find()->where(['graph_id' => $this->id])->count() + 1;
                        $vertex_model->load($vertex,'');
                        $vertex_model->save();
                    }
                    else{
                        $vertex_model = vertex::findOne(["id"=>Vertex::find()->select('id')->where(['graph_id' => $this->id, 'alias' => $vertex['alias']])->column()[0]]);
                        $vertex_model->load($vertex,'');
                        $vertex_model->save();
                    }
                }
            }
        }
    }

    //после того, когда мы удаляем граф из таблицы graph, за ним так же удаляться все свзанные с этим графом вершины и ребра
    public function afterDelete(){
        parent::afterDelete();
        $this->unlinkAll("vertex",true);
        $this->unlinkAll("edge",true);
    }

    //выводит все графы и его вершины
	public function fields(){
        return [
            "id" => 'id',
            "graph_name" => 'name',
            "vertex_count" => function(){
                return count($this->vertex);
            },
            "vertices" => 'vertex',
        ];
    }
}