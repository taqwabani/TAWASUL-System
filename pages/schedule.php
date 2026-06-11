<?php

session_start();

require_once "../config/db_connect.php";
require_once "../models/Student.php";
require_once "../models/Schedule.php";
include 'includes/sidebar.php';



$database = Database::getInstance();
$conn = $database->getConnection();

$parentName = isset($_SESSION['name']) ? $_SESSION['name'] : "زائر";
$parentID = $_SESSION['userID'];


$options = "";
$tableRows = "";
$periodsHtml = "";


try {

    $studentModel = new Student($conn);

    $students = $studentModel->getStudentsByParent($parentID);

    $options = "<option value=''></option>";

    foreach ($students as $student) {

        $selected = "";

        if (isset($_GET['studentID']) && $_GET['studentID'] == $student->studentID) {
            $selected = "selected";
        }
        $options .= "
        <option
            value='{$student->studentID}'
            {$selected}
        >
            {$student->studentName}
        </option>
    ";
    }

    $selectedStudentID = null;

    if (isset($_GET['studentID'])) {
        $selectedStudentID = $_GET['studentID'];
    }
    $classID = null;

    foreach ($students as $student) {
        if ($student->studentID == $selectedStudentID) {
            $classID = $student->classID;
            break;
        }
    }
    $scheduleData = [];

    if ($classID) {
        $scheduleModel = new Schedule($conn);
        $scheduleData = $scheduleModel->getScheduleByClass($classID);
    }

    $tableRows = "";

    $days = ["الأحد", "الإثنين", "الثلاثاء", "الأربعاء", "الخميس"];

    $scheduleModel = new Schedule($conn);
    $periodsData = $scheduleModel->getPeriods();
    
    foreach ($periodsData as $period) {
        $periodsHtml .= "
    <div>
        الحصة {$period->periodNumber}
        <br>
        " . date("H:i", strtotime($period->startTime)) . " - " . date("H:i", strtotime($period->endTime)) . "
    </div>
    ";
    }
    foreach ($days as $day) {
        $periods = [1 => "-", 2 => "-", 3 => "-", 4 => "-", 5 => "-", 6 => "-", 7 => "-"];

        foreach ($scheduleData as $lesson) {
            if ($lesson->dayName == $day) {
                $periods[$lesson->periodNumber]
                    = $lesson->subjectName;
            }
        }
        $tableRows .= "
    <tr>
        <td>{$day}</td>
        <td>{$periods[1]}</td>
        <td>{$periods[2]}</td>
        <td>{$periods[3]}</td>
        <td>{$periods[4]}</td>
        <td>{$periods[5]}</td>
        <td>{$periods[6]}</td>
        <td>{$periods[7]}</td>
    </tr>
    ";
    }
} catch (PDOException $e) {
    echo "حدث خطأ: " . $e->getMessage();
}




$htmlTemplate = file_get_contents("../HTML/schedule.html");

ob_start();
$sidebarHtml = ob_get_clean();
$htmlTemplate = str_replace("{SIDEBAR}", $sidebarHtml, $htmlTemplate);
$htmlTemplate = str_replace("{{PARENT_NAME}}", htmlspecialchars($parentName), $htmlTemplate);
$htmlTemplate = str_replace("{{STUDENT_OPTIONS}}", $options, $htmlTemplate);
$htmlTemplate = str_replace("{{SCHEDULE_ROWS}}", $tableRows, $htmlTemplate);
$htmlTemplate = str_replace("{{PERIODS_TIMES}}", $periodsHtml, $htmlTemplate);
echo $htmlTemplate;
