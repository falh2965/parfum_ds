<?php
session_start();
session_destroy();
header('location: longin.php');
exit;
?>