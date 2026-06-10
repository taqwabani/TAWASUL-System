<?php
/**
 * الواجهة الخاصة بعرض قائمة الاستفسارات للادمن
 */
session_start();
require_once "../config/db_connect.php"; 
require_once "../models/UserFactory.php"; 
include 'includes/adminSidebar.php';

$adminName = isset($_SESSION['name']) ? $_SESSION['name'] : "المدير";

$database = Database::getInstance();
$conn = $database->getConnection(); 


$admin = UserFactory::create($conn, 'admin');
$inquiries = $admin->viewInquiries();// جلب جميع الاستفسارات


$tableRows = ""; // متغير لتجميع صفوف الجدول
if (!empty($inquiries)) {
    foreach ($inquiries as $item) { // المرور على جميع الاستفسارات
        $statusClass = ($item->status == 'قيد الانتظار') ? 'pending' : 'replied'; // تحديد كلاس الحالة حسب حالة الاستفسار
        $formattedDate = date('Y/m/d', strtotime($item->created_at));
        $formattedDate = $item->created_at ? date('Y/m/d', strtotime($item->created_at)) : 'غير محدد';
//تنسيق التاريخ لعرضه بشكل مناسب في الجدول
        $tableRows .= "<tr>
            <td>" . htmlspecialchars($item->parentName) . "</td>
            <td>" . htmlspecialchars($item->subject) . "</td>
            <td>" . $formattedDate . "</td> 
            <td><span class='status-badge {$statusClass}'>" . htmlspecialchars($item->status) . "</span></td>
            <td class='actions'>
                <a href='chat.php?id={$item->inquiryId}' class='reply-link'>
                    <img src='../images/lucide-MessageSquare.svg' class='reply-icon'>
                    <span>رد</span>
                </a>
                <button class='more-btn'></button>
            </td>
        </tr>";
    }
} else {
    $tableRows = "<tr><td colspan='5' style='text-align:center; padding: 20px;'>لا توجد استفسارات واردة بعد.</td></tr>";
}

ob_start();
$sidebarHtml = ob_get_clean();

$htmlTemplate = file_get_contents("../HTML/ViewAdminInquiries.html");

$htmlTemplate = str_replace(
    "{{ADMIN_NAME}}",
    htmlspecialchars($adminName),
    $htmlTemplate
);

$htmlTemplate = str_replace(
    "{SIDEBAR}",
    $sidebarHtml,
    $htmlTemplate
);

$htmlTemplate = str_replace(
    "{{INQUIRIES_TABLE}}",
    $tableRows,
    $htmlTemplate
);

echo $htmlTemplate;
?>