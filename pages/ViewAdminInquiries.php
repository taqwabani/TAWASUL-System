<?php
/**
 * الواجهة الخاصة بعرض قائمة الاستفسارات للادمن
 */
require_once "../config/db_connect.php"; 
require_once "../models/Admin.php"; 

$admin = new Admin($conn);
$inquiries = $admin->viewInquiries();// جلب جميع الاستفسارات


$tableRows = ""; // متغير لتجميع صفوف الجدول
if (!empty($inquiries)) {
    foreach ($inquiries as $item) { // المرور على جميع الاستفسارات
        $statusClass = ($item->status == 'قيد الانتظار') ? 'pending' : 'replied'; // تحديد كلاس الحالة حسب حالة الاستفسار
        $formattedDate = date('Y/m/d', strtotime($item->created_at));
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

$htmlContent = file_get_contents("../HTML/ViewAdminInquiries.html");
$finalOutput = str_replace("{{INQUIRIES_TABLE}}", $tableRows, $htmlContent);
echo $finalOutput;
?>