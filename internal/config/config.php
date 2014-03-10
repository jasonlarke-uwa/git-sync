<?php
return array(
	'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..',
	'components' => array(
		'session' => array(
			'autostart' => true,
			'timeout' => 30
		),
		'db' => array (
			'connectionString' => 'mysql:host=localhost;dbname=db_misc',
			'username' => 'basic_user',
			'password' => '2bHCmfa9yt8cecxP',
			'charset' => 'utf8',
			'emulatePrepare' => true,
			'tablePrefix' => ''
		),
		'controller' => array (
			'class' => 'SiteController'
		)
	)
);
?>