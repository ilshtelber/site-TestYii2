<?php

namespace app\controllers;

use Yii;
use yii\rest\ActiveController;

use yii\helpers\Url;
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use app\models\graph;


class GraphController extends ActiveController
{
    public $modelClass = 'app\models\graph';
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['only'] = ['create', 'update', 'delete'];
        $behaviors['authenticator']['authMethods'] = [
            HttpBasicAuth::className(),
            HttpBearerAuth::className(),
        ];
        /*$behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['create', 'update', 'delete'],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];*/
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors'  => [
                'Origin'                           => ["*"],
                'Access-Control-Request-Method'    => ['DELETE','PUT','GET','POST'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age'           => 3600,
                'Access-Control-Expose-Headers' => ["Authorization"],
                'Access-Control-Request-Headers' => ['Authorization'],
            ],
        ];
        return $behaviors;
    }
}