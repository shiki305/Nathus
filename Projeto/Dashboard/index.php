<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../Login/login.php");
    exit;
}

header("Location: dashboard.php");
exit;
?>