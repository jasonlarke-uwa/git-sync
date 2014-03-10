<?php
	require_once "Language.php";
	
	echo Localization::get('messages.hello', array('name' => 'Jason'));
?>