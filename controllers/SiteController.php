<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\myclass\DataBaseBeboss;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;

use yii\helpers\Html;
use yii\helpers\Url;

use app\models\RequestForm;
use app\models\LoginForm;
use app\models\User;
use app\models\graph;
use app\models\vertex;
use app\models\edge;


class SiteController extends Controller
{
   public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    //главная страница
    public function actionIndex()
    {
        $this->view->title = 'главная страница';
        return $this->render('index');
    }

    //выводит всех пользователей
    public function actionUsers()
    {
        $this->view->title = 'список пользователей';
        $users = User::find()->asArray()->all();
        return $this->render('users', compact('users'));
    }

    //выводит конкретного пользователя по его псевдониму, а так же все сообщение адресованного к этому пользоватклю, которые мы отправили через Request
    public function actionUser($id){
        $this->view->title = "пользователь не найден";
        $user = User::find()->with('message')->where(['alias' => $id])->limit(1)->one();
        if(!$user) return $this->render('user_error');
        $this->view->title = $user['alias'];
        return $this->render('user', compact('user'));
    }

    //добавляем сообщение конкретному пользователю, занося тем самым данные в таблицу messages
    public function actionRequest(){
        $this->view->title = 'отправка заявки';
        if(Yii::$app->request->isAjax){
            $post = Yii::$app->request->post()['user'];
            $user = User::find()->where(['id' => $post])->limit(1)->one();
            $link = Html::a($user['alias'], Url::to(['site/user','id'=>$user['alias']]));
            $message = "<div id='user_$user[id]' class='row page-header'> <img src='$_SERVER[SCRIPT_NAME]/../images/no-avatar.jpg' width='100' height='120' alt='no-avatar' class='col-lg-2'> <div class='col-lg-4'> <h2>$link</h2> <div><b>$user[username] $user[surname]</b></div> <div><small><i>$user[email]</i></small></div></div></div>";
            return $message;
        }

        $model = new RequestForm();
        $users = User::find()->all();
        if($model->load(Yii::$app->request->post())) {
        	if( $model->save() ){
        		Yii::$app->session->setFlash('success',"Данные приняты");
        		return $this->refresh();
        	}
        	else{
        		Yii::$app->session->setFlash('error',"произошла ошибка");
        	}
        }
        return $this->render('request', compact('model','users'));
    }

    public function actionTask1(){
        $this->view->title = 'первое тестовое задание от Beboss';
    	$db = new DataBaseBeboss(preg_replace("/(;dbname=.*)|(mysql:host=)/i","",Yii::$app->db->dsn),preg_replace("/mysql:host=.*;dbname=/i","",Yii::$app->db->dsn),Yii::$app->db->username,Yii::$app->db->password,$_SERVER["DOCUMENT_ROOT"].'/db_import.sql');
        return $this->render('recursion',compact('db'));
    }

    public function actionTask2(){
        $this->view->title = 'второе тестовое задание от Beboss';
        return $this->render('coordinates');
    }

    public function actionTask3(){
        $this->view->title = 'третье тестовое задание от ДЕСК';
        return $this->render('graph');
    }

    public function actionTest()
    {
        $m = new User();
        $m->alias = "marsel";
        $m->username = "Марсель";
        $m->surname = "Xисамутдинов";
        $m->email = "marselgmx@gmail.com";
        $m->password = Yii::$app->security->generatePasswordHash("12345");
        $m->auth_key = Yii::$app->security->generateRandomString();
        $m->save();

        $m = new User();
        $m->alias = "test";
        $m->username = "Денис";
        $m->surname = "Захаренко";
        $m->email = "zahar@mail.ru";
        $m->password = Yii::$app->security->generatePasswordHash("qwerty123");
        $m->auth_key = Yii::$app->security->generateRandomString();
        $m->save();

        $m = new User();
        $m->alias = "nagibator3000";
        $m->username = "Марина";
        $m->surname = "Антиранова";
        $m->email = "masha@yandex.ru";
        $m->password = Yii::$app->security->generatePasswordHash("admin");
        $m->auth_key = Yii::$app->security->generateRandomString();
        $m->save();
        return $m;
        //return $this->render('error', compact('users'));
    }
}
