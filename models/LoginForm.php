<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * данный модель позволяет авторизировать нового пользователя
 */
class LoginForm extends Model
{
    public $alias;
    public $password;
    public $rememberMe = true;

    private $_user;

    //здесь задаем правила для имя пользователя и пароля
    public function rules()
    {
        return [
            // user's alias and password are both required
            [['alias', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }
    
    //это правило служит для проверки валидаций пароля при авторизаций
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect alias or password.');
            }
        }
    }

    //Авторизирует пользователя, используя предоставленное имя пользователя и пароль.
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    //позволяет авторизировать пользователя по REST API, где возвращает токен, для того, чтобы выполнить некторые операций под пользователем (привязанного к этому токену) используея этот токен в заголовках Authorization, например Authorization: 'здесь мы вводим токен'
    public function RestAuth()
    {
        if ($this->validate()) {
            $model = $this->getUser();
            $model->access_token = \Yii::$app->security->generateRandomString();
            $model->expired_at = time() + 3600 * 24;
            return $model->save() ? $model : null;
        } else {
            return null;
        }
    }
   
   //данный метод возвращает нам пользователя
    protected function getUser()
    {
        if ($this->_user === null){
            $this->_user = User::findByUsername($this->alias);
        }
        return $this->_user;
    }
}