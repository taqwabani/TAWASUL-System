<?php

session_start();

require_once "../config/db_connect.php";
require_once "../models/Student.php";

$database = Database::getInstance();
$conn = $database->getConnection();

$parentID = $_SESSION['userID'];
$parentName = isset($_SESSION['name']) ? $_SESSION['name'] : "زائر";

$studentModel = new Student($conn);

$students = $studentModel->getStudentsByParent($parentID);


$studentsHtml = "";
foreach ($students as $student) {

    $studentsHtml .= "


<div class='student-card'>

    <h3>{$student->studentName}</h3>

    <p>
        الصف:
        <span>{$student->className}</span>
    </p>

</div>

";
}


ob_start();

include 'includes/sidebar.php';

$sidebarHtml = ob_get_clean();

$htmlTemplate =
    file_get_contents("../HTML/student.html");

$htmlTemplate = str_replace(
    "{{STUDENTS_LIST}}",
    $studentsHtml,
    $htmlTemplate
);

$htmlTemplate = str_replace(
    "{SIDEBAR}",
    $sidebarHtml,
    $htmlTemplate
);
$htmlTemplate = str_replace("{{PARENT_NAME}}",htmlspecialchars($parentName),$htmlTemplate);

echo $htmlTemplate;
