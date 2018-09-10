<?php
/**
 * задание 1
 * DataBaseBeboss - Выводит таблицу groups и products
 */
 

if(!isset($_GET['group'])) $_GET['group'] = 0;
?>

<aside style="float: left; border-right: 1px solid black; padding-right: 2%;">
	<a href='/task1'>все товары</a>
	<?=$db->output($_GET['group'])?>
</aside>
<article style="margin-left: 400px;">
	<?=$db->products($_GET['group'])?>
</article>
