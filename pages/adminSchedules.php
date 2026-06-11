<?php
/**
 * صفحة التحكم وإدارة الجداول الدراسية الأسبوعية (للإدارة) - Controller
 */
session_start();
require_once '../config/db_connect.php';
require_once '../models/Schedule.php';

// 1. التحقق من الصلاحيات
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$conn = Database::getInstance()->getConnection();
$scheduleModel = new Schedule($conn);

// 2. معالجة إضافة حصة/مادة جديدة للجدول (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_period'])) {
    $classID = (int)$_POST['classID'];
    $dayName = $_POST['dayName'];
    $periodNumber = (int)$_POST['periodNumber'];
    $subjectName = trim($_POST['subjectName']);
    
    if (!empty($subjectName)) {
        if ($scheduleModel->addSchedule($classID, $dayName, $periodNumber, $subjectName)) {
            $_SESSION['msg'] = "تم إضافة الحصة إلى الجدول بنجاح";
        } else {
            $_SESSION['msg'] = "خطأ: تعذر إضافة الحصة للجدول";
        }
    }
    header("Location: adminSchedules.php");
    exit();
}

// 3. معالجة حذف حصة من الجدول (GET)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $scheduleModel->deleteSchedule($id);
    $_SESSION['msg'] = "تم حذف الحصة من الجدول بنجاح";
    header("Location: adminSchedules.php");
    exit();
}

// 4. جلب البيانات المطلوبة للواجهة
// جلب قائمة الصفوف الدراسية المتاحة لتعرض في القائمة المنسدلة للنموذج
try {
    $classesStmt = $conn->query("SELECT * FROM classes");
    $classes = $classesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $classes = [];
}

// جلب كل الحصص والأنشطة المضافة حالياً في جداول المدرسة لعرضها في الجدول
$schedules = $scheduleModel->getAllSchedulesWithClassName();

// 5. بناء أسطر جدول العرض للمدير
$rows = "";
if (count($schedules) > 0) {
    foreach ($schedules as $s) {
        $rows .= "<tr>
            <td>" . htmlspecialchars($s['className']) . "</td>
            <td>" . htmlspecialchars($s['dayName']) . "</td>
            <td>الحصة " . htmlspecialchars($s['periodNumber']) . "</td>
            <td>" . htmlspecialchars($s['subjectName']) . "</td>
            <td>
                <a href='?delete=" . $s['scheduleID'] . "' class='action-link delete' onclick='return confirm(\"هل أنت متأكد من حذف هذه الحصة؟\")' style='color:red;'>حذف</a>
            </td>
        </tr>";
    }
} else {
    $rows = "<tr><td colspan='5' style='text-align:center;'>لا توجد حصص مضافة في الجداول حالياً.</td></tr>";
}

// 6. تجهيز السايدبار الخاص بالمدير
$currentPage = 'adminSchedules.php';
ob_start();
include 'includes/adminSidebar.php';
$sidebarHtml = ob_get_clean();

// 7. بناء قائمة خيارات الصفوف (Options) للـ HTML
$class_options = "";
foreach ($classes as $c) {
    $class_options .= "<option value='" . $c['classID'] . "'>" . htmlspecialchars($c['className']) . "</option>";
}

// 8. قراءة القالب ودمج البيانات المرسلة
if (file_exists("../HTML/adminSchedules.html")) {
    $template = file_get_contents("../HTML/adminSchedules.html");
    
    $template = str_replace("{SIDEBAR}", $sidebarHtml, $template);
    $template = str_replace("{{CLASS_OPTIONS}}", $class_options, $template);
    $template = str_replace("{{SCHEDULES_TABLE}}", $rows, $template);
    $template = str_replace("{{MSG}}", isset($_SESSION['msg']) ? $_SESSION['msg'] : "", $template);
    
    unset($_SESSION['msg']);
    echo $template;
} else {
    die("خطأ: ملف القالب adminSchedules.html غير موجود.");
}
?>