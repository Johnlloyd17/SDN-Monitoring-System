<?php
// Executes one endpoint in-process with a simulated session and request body.
$spec   = json_decode(file_get_contents($argv[1]), true);
$target = $argv[2];
$_SERVER['REQUEST_URI']    = '/pages/bpls_data/bpls.php';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET   = isset($spec['get']) ? $spec['get'] : array();
$_POST  = isset($spec['post']) ? $spec['post'] : array();
$_FILES = isset($spec['files']) ? $spec['files'] : array();
@session_start();
$_SESSION['role']     = isset($spec['role']) ? $spec['role'] : 'Administrator';
$_SESSION['username'] = isset($spec['session_user']) ? $spec['session_user'] : 'admin';
$_SESSION['userid']   = 1;
ob_start();
include $target;
$out = ob_get_clean();
echo $out;