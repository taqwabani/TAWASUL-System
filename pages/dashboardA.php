<?php

session_start();

require_once "../config/db_connect.php";
include 'includes/adminSidebar.php';

$adminName = isset($_SESSION['name']) ? $_SESSION['name'] : "المدير";


ob_start();
$sidebarHtml = ob_get_clean();

$htmlTemplate = file_get_contents("../HTML/dashboardA.html");

$htmlTemplate = str_replace("{SIDEBAR}", $sidebarHtml,$htmlTemplate);
$htmlTemplate = str_replace("{{ADMIN_NAME}}",  htmlspecialchars($adminName),$htmlTemplate);
echo $htmlTemplate;

?>