<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: dashboard/");
    exit;
} else {
    header("Location: login.php");
    exit;
}
?>
