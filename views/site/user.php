<div id='user_<?=$user["id"]?>' class="row page-header">
    <img src="<?=$_SERVER['SCRIPT_NAME']?>/../images/no-avatar.jpg" width="100" height="120" alt="no-avatar" class="col-lg-2">
    <div class="col-lg-4">
    	<h2>#<?=$user['alias']?></h2>
    	<div><b><?=$user['username']?> <?=$user['surname']?></b></div>
    	<div><small><i><?=$user['email']?></i></small></div>
    </div>
</div>


<div>
<? foreach($user->message as $application): ?>
	<div id_message="<?=$application->id?>" class="well">
		<div><b><small><?=$application->sender?></small></b></div>
		<div><big><?=$application->text?></big></div>
	</div>
<? endforeach; ?>
</div>