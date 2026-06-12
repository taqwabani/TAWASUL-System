<?php
session_start();
require_once "../config/db_connect.php"; 
require_once "../models/Schedule.php";

if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$conn = Database::getInstance()->getConnection();
$scheduleModel = new Schedule($conn);
// جلب كافة الحصص مرتبة حسب الصف واليوم
$schedules = $scheduleModel->getAllSchedulesWithClassName();

// تنظيم البيانات في مصفوفة للعرض الشبكي
$grid = [];
$days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];

foreach ($schedules as $s) {
    $grid[$s['className']][$s['dayName']][$s['periodNumber']] = $s['subjectName'];
}

$rows_html = "";
if (!empty($grid)) {
    foreach ($grid as $className => $classData) {
        $rows_html .= "<div class='schedule-card'>";
        $rows_html .= "<h3>جدول صف: " . htmlspecialchars($className) . "</h3>";
        $rows_html .= "<table border='1'>";
        $rows_html .= "<thead><tr><th>الحصة / اليوم</th>";
        
        foreach ($days as $day) {
            $rows_html .= "<th>$day</th>";
        }
        $rows_html .= "</tr></thead><tbody>";

        for ($p = 1; $p <= 7; $p++) { // عرض 7 حصص أساسية
            $rows_html .= "<tr>";
            $rows_html .= "<td style='font-weight: bold;'>$p</td>";
            foreach ($days as $day) {
                $subject = isset($classData[$day][$p]) ? htmlspecialchars($classData[$day][$p]) : "-";
                $rows_html .= "<td>$subject</td>";
            }
            $rows_html .= "</tr>";
        }
        $rows_html .= "</tbody></table></div>";
    }
} else {
    $rows_html = "<div style='text-align:center; padding: 20px;'>لا يوجد جدول دراسي متاح حالياً.</div>";
}

ob_start();
include 'includes/sidebar.php'; 
$sidebarHtml = ob_get_clean(); 

$parentName = isset($_SESSION['name']) ? $_SESSION['name'] : "ولي الأمر";

// جلب القالب (يجب إنشاء ملف HTML/schedule.html مشابه لـ inquiries.html)
if (file_exists("../HTML/schedule.html")) {
    $template = file_get_contents("../HTML/schedule.html");
    $template = str_replace("{SIDEBAR}", $sidebarHtml, $template);
    $template = str_replace("{{SCHEDULES_TABLE}}", $rows_html, $template);
    $template = str_replace("{{PARENT_NAME}}", htmlspecialchars($parentName), $template);
    echo $template;
} else {
    echo "<h2>الجداول الدراسية</h2><table border='1'>$rows_html</table>";
}
?>
