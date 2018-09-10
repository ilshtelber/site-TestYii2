<?php
namespace app\models;
use yii\db\ActiveRecord;
use app\models\graph;

class Vertex extends ActiveRecord
{
    public static function tableName(){
        return 'vertices';
    }

    //задаем правила для ввода данных
    public function rules(){
        return [
            ["alias","required"],
            ["alias", 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            ['alias', 'match', 'pattern' => '/^[a-z]\w*$/i'],
            ['alias', 'string', 'length' => [1, 5]],
            ['graph_id',"integer"],
            ['X',"double","min"=>0,"max"=>1100],
            ['Y',"double","min"=>0,"max"=>500],
        ];
    }

    //данное правило мы не используем, подобныйй алгоритм используеться в модели graph, но несмотря на это, данное правило позволяет задавать только уникальные название для вершины каждого графа
    public function validateAlias($attribute, $params)
    {
        if(!$this->graph_id) $this->addError($attribute, "error");
        $alias = self::find()->select('alias')->where(['graph_id' => $this->graph_id])->asArray()->column();
        if( in_array($this->alias, $alias) )
            $this->addError($attribute, "vertex $this->alias is in the table");
    }

    //служит для вывода всех вершин кроме его id и id его привязанного графа
    public function fields(){
        $fields = parent::fields();
        unset($fields['id'],$fields['graph_id']);
        return $fields;
    }
}