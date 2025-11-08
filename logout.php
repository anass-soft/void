<?php
// logout.php
require_once 'functions.php';
start_session();
session_destroy();
header('Location: index.php');
exit;
?>