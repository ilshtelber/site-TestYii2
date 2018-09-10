<?php

/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="site-index">
	<div class="">
	    <? foreach($users as $user):?>
			<div id='user_<?=$user["id"]?>' class="row page-header">
			    <img src="<?=$_SERVER['SCRIPT_NAME']?>/../images/no-avatar.jpg" width="100" height="120" alt="no-avatar" class="col-lg-2">
			    <div class="col-lg-4">
			    	<h2><?=Html::a($user['alias'],Url::to(['site/user','id'=>$user['alias']]))?></h2>
			    	<div><b><?=$user['username']?> <?=$user['surname']?></b></div>
			    	<div><small><i><?=$user['email']?></i></small></div>
			    </div>
			</div>
	    <? endforeach; ?>
	</div>
</div>
