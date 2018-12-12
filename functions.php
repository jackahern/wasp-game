<?php
function redirect($msg) {
	$_SESSION['msg'] = $msg;
	header("Location: wasp-game.php");
	die();
}
function dd($dump) {
    echo "<pre>";
    print_r($dump);
    exit;
}