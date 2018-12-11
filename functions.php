<?php
function redirect($msg) {
	$_SESSION['msg'] = $msg;
	header("Location: wasp-game.php");
	die();
}