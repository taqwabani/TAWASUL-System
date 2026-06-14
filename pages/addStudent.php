<?php
/**
 * صفحة إضافة طالب جديد (للإدارة)
 */
session_start();
require_once '../config/db_connect.php';
require_once '../models/Admin.php';
require_once '../models/Schedule.php';
// التحقق من الصلاحيات
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$adminName = isset($_SESSION['name']) ? $_SESSION['name'] : "المدير";
$conn = Database::getInstance()->getConnection();

$admin = new Admin($conn);
$scheduleModel = new Schedule($conn);

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentName = trim($_POST['studentName']);
    $classID = $_POST['classID'];
    $parentID = $_POST['parentID'];

    if (!empty($studentName) && $admin->addStudent($studentName, $classID, $parentID)) {
        $_SESSION['student_msg'] = "تم إضافة الطالب بنجاح";
        header("Location: addStudent.php");
        exit();
    } else {
        $msg = "<div class='alert alert-danger'>حدث خطأ أثناء إضافة الطالب. يرجى التأكد من البيانات.</div>";
    }
}

if (isset($_SESSION['student_msg'])) {
    $msg = "<div class='alert alert-success'>{$_SESSION['student_msg']}</div>";
    unset($_SESSION['student_msg']);
}

// جلب البيانات للقوائم المنسدلة
$classes = $scheduleModel->getAllClassNames();
$allUsers = $admin->getAllUsers();
$parents = array_filter($allUsers, function($u) { return $u->role === 'parent'; });

$classOptions = "";
foreach ($classes as $c) {
    $classOptions .= "<option value='{$c['classID']}'>" . htmlspecialchars($c['className']) . "</option>";
}

$parentOptions = "";
foreach ($parents as $p) {
    $parentOptions .= "<option value='{$p->userID}'>" . htmlspecialchars($p->name) . "</option>";
}

ob_start();
include 'includes/adminSidebar.php';
$sidebarHtml = ob_get_clean();

$html_template = file_get_contents("../HTML/addStudent.html");
$html_template = str_replace(
    ["{SIDEBAR}", "{{ADMIN_NAME}}", "{{MESSAGE}}", "{{CLASS_OPTIONS}}", "{{PARENT_OPTIONS}}"],
    [$sidebarHtml, htmlspecialchars($adminName), $msg, $classOptions, $parentOptions],
    $html_template
);

echo $html_template;
?>