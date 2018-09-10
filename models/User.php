<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'users';
    }

    public function rules(){
        return [
            ["alias","required"],
            [["id","alias"],"unique"],
        ];
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public static function findByUsername($alias)
    {
        return static::findOne(['alias' => $alias]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public function getMessage(){
        return $this->hasMany(RequestForm::className(), ['id_user' => 'id']);
    }

    public function fields()
    {
        return [
            'id' => 'id',
            'username' => 'alias',
            'token' => 'access_token',
        ];
    }

}