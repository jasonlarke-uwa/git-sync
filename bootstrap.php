<?php
	$wireframe = "../wireframe/WireFrame.php";
	$config = "internal/config/config.php";
	
	require($wireframe);
	WireFrame::createApplication('WebApplication', $config);
	WireFrame::app()->run();
?>