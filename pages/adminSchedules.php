<?php
/**
 * صفحة التحكم وإدارة الجداول الدراسية الأسبوعية (للإدارة) - Controller
 */
session_start();
require_once '../config/db_connect.php';
require_once '../models/Schedule.php';
ob_start();
include 'includes/adminSidebar.php';
$sidebarHtml = ob_get_clean();
//  التحقق من الصلاحيات
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
$adminName = isset($_SESSION['name']) ? $_SESSION['name'] : "المدير";
$conn = Database::getInstance()->getConnection();
$scheduleModel = new Schedule($conn);

$days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];

// 1. معالجة حفظ البيانات عند الضغط على زر "حفظ التعديلات"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_schedule'])) {
    $cID = (int)$_POST['classID'];
    $currentData = $scheduleModel->getWeeklyScheduleByClass($cID);
    $lookup = [];
    foreach($currentData as $row) $lookup[$row['dayName']][$row['periodNumber']] = $row['scheduleID'];

    foreach ($_POST['subjects'] as $dayIdx => $periodsData) {
        $dayName = $days[$dayIdx]; // تحويل الفهرس إلى اسم اليوم الصحيح من المصفوفة
        foreach ($periodsData as $pNum => $subject) {
            $subject = trim($subject);
            if (isset($lookup[$dayName][$pNum])) {
                $scheduleModel->updateSchedule($lookup[$dayName][$pNum], $subject);
            } elseif ($subject !== "") {
                $scheduleModel->addSchedule($cID, $dayName, $pNum, $subject);
            }
        }
    }
    header("Location: adminSchedules.php?classID=$cID");
    exit();
}

$classnames = $scheduleModel->getAllClassNames();
// اختيار الصف الأول افتراضياً إذا لم يتم تحديد صف في الرابط
$selectedClassID = isset($_GET['classID']) ? (int)$_GET['classID'] : ($classnames[0]['classID'] ?? null);

$class_buttons_html = '';
foreach ($classnames as $c) {
    $activeClass = ($c['classID'] == $selectedClassID) ? 'active' : '';
    $class_buttons_html .= "<a href='?classID={$c['classID']}' class='class_button $activeClass'>{$c['className']}</a>";
}

$periods = $scheduleModel->getTimes();

$scheduleData = [];
if ($selectedClassID) {
    $raw = $scheduleModel->getWeeklyScheduleByClass($selectedClassID);
    foreach($raw as $r) {
        // تنظيف اسم اليوم من المسافات لضمان المطابقة
        $dName = trim($r['dayName']);
        
        // معالجة ذكية: إذا كان الاسم في الداتا بيز بدون همزة والكود به همزة، نقوم بمطابقتها
        foreach($days as $standardDay) {
            $normStandard = str_replace(['أ','إ','آ'], 'ا', $standardDay);
            $normDB = str_replace(['أ','إ','آ'], 'ا', $dName);
            if($normStandard === $normDB) {
                $scheduleData[$standardDay][$r['periodNumber']] = $r['subjectName'];
                break;
            }
        }
    }
}

// بناء كود مواعيد الحصص (Periods HTML)
$periods_html = "";
if (!empty($periods)) {
    $periods_html .= "<div class='periods-container'>";
    foreach ($periods as $p) {
        $periods_html .= "
        <div class='period-box'>
            <div class='period-title'>الحصة {$p['periodNumber']}</div>
            <div class='period-time'>" . htmlspecialchars($p['startTime']) . " - " . htmlspecialchars($p['endTime']) . "</div>
        </div>";
    }
    $periods_html .= "</div>";
}
// بناء عرض الجداول لجميع الصفوف
$tables_html = ''; // تهيئة المتغير
if ($selectedClassID) {
    $tables_html .= "<form method='POST' action='adminSchedules.php'><input type='hidden' name='classID' value='$selectedClassID'>";
    $tables_html .= "<table class='timetable-grid'><thead><tr><th>اليوم / الحصة</th>";
    foreach ($periods as $p) $tables_html .= "<th>الحصة {$p['periodNumber']}</th>";
    $tables_html .= "</tr></thead><tbody>";

    foreach ($days as $index => $day) {
        $tables_html .= "<tr><td class='day-column'>$day</td>";
        foreach ($periods as $p) {
            $pNum = $p['periodNumber'];
            $val = $scheduleData[$day][$pNum] ?? "";
            $tables_html .= "<td><input type='text' name='subjects[$index][$pNum]' value='" . htmlspecialchars($val) . "' class='table-input' placeholder='-'></td>";
        }
        $tables_html .= "</tr>";
    }
    $tables_html .= "</tbody></table>";
    $tables_html .= "<div style='margin-top:20px; text-align:left;'><button type='submit' name='save_schedule' class='btn-class-filter'>حفظ التعديلات</button></div></form>";
}
// قراءة ملف الـ HTML الأساسي واستبدال المتغيرات
$html_content = file_get_contents("../HTML/adminSchedules.html");
$html_content = str_replace(
    ["{SIDEBAR}", "{{ADMIN_NAME}}", "{{PERIODS_LIST}}", "{{SCHEDULES_TABLE}}", '{{CLASS_BUTTONS_PLACEHOLDER}}'],
    [$sidebarHtml, htmlspecialchars($adminName), $periods_html, $tables_html, $class_buttons_html],
    $html_content
);

echo $html_content;
?>