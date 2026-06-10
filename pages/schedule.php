<?php

session_start();

require_once "../config/db_connect.php";
include 'includes/sidebar.php';

$parentName = isset($_SESSION['name'])? $_SESSION['name'] : "زائر";

$htmlTemplate = file_get_contents("../HTML/schedule.html");


ob_start();
$sidebarHtml = ob_get_clean();

$htmlTemplate = str_replace(
    "{SIDEBAR}",
    $sidebarHtml,
    $htmlTemplate
);

$htmlTemplate = str_replace(
    "{{PARENT_NAME}}",
    htmlspecialchars($parentName),
    $htmlTemplate
);

echo $htmlTemplate;


?>