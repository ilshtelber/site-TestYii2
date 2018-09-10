<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;

use yii\filters\AccessControl;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;

use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;
use yii\base\NotSupportedException;

use app\models\LoginForm;
use app\models\graph;
use app\models\edge;
use app\models\ShortestForm;

class RestController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['only'] = ['Edges','Relate', 'Dissociate', 'Shortest'];
        $behaviors['authenticator']['authMethods'] = [
              HttpBasicAuth::className(),
              HttpBearerAuth::className(),
        ];
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors'  => [
                'Origin'                           => ["*"],
                'Access-Control-Request-Method'    => ['DELETE','PUT','GET','POST'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age'           => 3600,
                'Access-Control-Allow-Origin' => ['*'],
                'Access-Control-Expose-Headers' => ["Authorization"],
                'Access-Control-Request-Headers' => ['Authorization'],
            ],
        ];
        return $behaviors;
    }

    //производит авторизацию пользователя, возвращая нам токен для аутентификаций
    public function actionLogin()
    {
        $model = new LoginForm();
        $model->load(Yii::$app->request->bodyParams, '');
        if ($token = $model->RestAuth()) return $token;
        return $model;
    }

    //выводит название всех ребер определенного графа в таблице edges
    public function actionEdges($id)
    {
        if( !is_numeric($id) ) throw new NotFoundHttpException('Page not found');
        if( !graph::find()->where(['id' => $id])->exists() ) throw new NotFoundHttpException("Object not found: $id");
        return Edge::find()->where(['graph_id' => $id])->all();
    }

    //связывает две вершины создавая при этом ребро в таблие edges при вводе vertex_from, vertex_to и weight
    public function actionRelate($id)
    {
        if( !is_numeric($id) ) throw new NotFoundHttpException('Page not found');
        if( !graph::find()->where(['id' => $id])->exists() ) throw new NotFoundHttpException("Object not found: $id");

        $model = new Edge();
        $model->graph_id = $id;
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        $model_update = Edge::findOne(['vertex_from'=>$model->vertex_from,'vertex_to'=>$model->vertex_to,'graph_id' => $id]);

        if( $model_update != NULL ){
            $model_update->load(Yii::$app->getRequest()->getBodyParams(), '');
            if ($model_update->save()){
                $response = Yii::$app->getResponse();
                $response->setStatusCode(201);
            } 
            elseif (!$model_update->hasErrors()) throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
        else{
            if ($model->save()) {
                $response = Yii::$app->getResponse();
                $response->setStatusCode(201);
            } elseif (!$model->hasErrors()) throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    //разрывает связь двух вершин удаляя ребро из таблицы edges при вводе vertex_from и vertex_to
    public function actionDissociate($id)
    {
        if( !is_numeric($id) ) throw new NotFoundHttpException('Page not found');
        if( !graph::find()->where(['id' => $id])->exists() ) throw new NotFoundHttpException("Object not found: $id");

        $r = Yii::$app->getRequest()->getBodyParams();
        $model = Edge::findOne(['vertex_from'=>$r['vertex_from'],'vertex_to'=>$r['vertex_to'],'graph_id' => $id]);
        if($model === NULL) throw new NotFoundHttpException("record not found");
        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        return $model;
    }

    //производит расчет кратчайщего пути в графе
    public function actionShortest($id)
    {
        if( !is_numeric($id) ) throw new NotFoundHttpException('Page not found');
        if( !graph::find()->where(['id' => $id])->exists() ) throw new NotFoundHttpException("Object not found: $id");

        $model = new ShortestForm();
        $model->graph_id = $id;
        if($model->load(Yii::$app->getRequest()->getBodyParams(), ''))
        {
            if( $model->validate() ){
                return $model->calculation();
            }
            else{
                $response = Yii::$app->getResponse();
                $response->setStatusCode(404);
                return $model->getErrors();
            }
        }
    }

    //тут задаем через какие методы запроса работает действий
    public function verbs()
    {
        return [
            'Login' => ['post'],
            'Edges' => ['get'],
            'Relate' => ['put', 'patch'],
            'Dissociate' => ['put', 'patch'],
        ];
    }

}