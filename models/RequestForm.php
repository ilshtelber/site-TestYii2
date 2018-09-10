<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use app\models\User;

class RequestForm extends ActiveRecord{

	//имя таблицы
	public static function tableName(){
		return "messages";
	}

	//какие атрибуты будут отображаться в форме (форма для ввода данных в таблице messages)
	public function attributeLabels(){
		return [
			'id_user' => "Выберите пользователя",
			'sender' => "Введте ваш E-mail адресс",
			'text' => "Текст сообщения"
		];
	}

	//тут задаём правила для ввода данных в таблицу
	public function rules(){
		return [
			[['id_user','sender','text'], "required"],
			[['id_user','sender','text'], "trim"],
			['text',"string","length"=>[3,300]],
			['id_user',"integer"],
			['sender', "email"],
			['id_user',"isUser"]
		];
	}

	//
	public function isUser($attr){
		$arr = array();
        $ab = User::find()->asArray()->all();
        foreach($ab as $value) $arr[] = $value['id'];
		if( !in_array($this->$attr, $arr) ) $this->addError($attr, 'Такого пользователя нет');
	}
}