<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\BaseArrayHelper;
use yii\bootstrap\ActiveForm;


$this->params['breadcrumbs'][] = $this->title;
?>


<div class="site-request">
    <div id="ajax_user" class="hero-unit">
        <div id='user_<?=$users[0]["id"]?>' class="row page-header">
            <img src="<?=$_SERVER['SCRIPT_NAME']?>/../images/no-avatar.jpg" width="100" height="120" alt="no-avatar" class="col-lg-2">
            <div class="col-lg-4">
                <h2><?=Html::a($users[0]['alias'], Url::to(['site/user','id'=>$users[0]['alias']]))?></h2>
                <div><b><?=$users[0]['username']?> <?=$users[0]['surname']?></b></div>
                <div><small><i><?=$users[0]['email']?></i></small></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'requestform']); ?>
                <?= $form->field($model, 'id_user')->dropDownList(BaseArrayHelper::map($users, 'id', 'alias'),['id'=>"select_user"]) ?>
                <?= $form->field($model, 'sender') ?>
                <?= $form->field($model, 'text')->textarea(['rows' => 5]) ?>
                <div class="form-group">
                    <?= Html::submitButton('Отправить', ['id'=>"btn", 'class' => "btn btn-primary"]) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
$js = <<<JS
    $('#select_user').on('change',function(e){
        $.ajax({
            url: 'request',
            data: {user:$(this).val()},
            type: 'POST',
            success: function(res){
                $('#ajax_user').html(res);
                console.log(res);
            },
            error: function(){
                $('#ajax_user').html("<h1>произошла ошибка</h1>");
            }

        });
    });
JS;
$this->registerJs($js);

