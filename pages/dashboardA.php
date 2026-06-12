<?php
// صفحة لوحة تحكم المدير الرئيسية - تعرض أحدث الإعلانات المنشورة من قبل الإدارة
session_start();
require_once "../config/db_connect.php";
require_once "../models/Announcement.php";
$database = Database::getInstance();
$conn = $database->getConnection();
$adminName = isset($_SESSION['name']) ? $_SESSION['name'] : "المدير";
ob_start();
include 'includes/adminSidebar.php';
$sidebarHtml = ob_get_clean();
$anno = new Announcement();
$announcementsFromDB = $anno->getAllAnnouncements($conn); // جلب كافة الإعلانات المنشورة من قبل الإدارة
$announcementsHtml = "";
if (!empty($announcementsFromDB)) {
    foreach ($announcementsFromDB as $ann) {
        $formattedDate = date("Y-m-d", strtotime($ann['createdAt']));
        $imageHtml = "";
        if (!empty($ann['imagePath'])) {
            $imageHtml = "
            <div class='announcement-image-wrapper'>
                <img src='../" . htmlspecialchars($ann['imagePath']) . "' alt='مرفق الإعلان' class='announcement-img'>
            </div>";
        }
        $announcementsHtml .= "
        <div class='announcement-dashboard-card'>
            <div class='announcement-card-content'>
                <div class='announcement-card-header'>
                    <h4 class='announcement-card-title'>" . htmlspecialchars($ann['title']) . "</h4>
                    <span class='announcement-card-date'>{$formattedDate}</span>
                </div>
                <div class='announcement-card-body'>
                    " . nl2br(htmlspecialchars($ann['content'])) . "
                </div>
            </div>
            {$imageHtml}
        </div>";
    }
} else {
    // في حال كانت قاعدة البيانات فارغة من الإعلانات
    $announcementsHtml = "<div style='text-align: center; color: #718096; padding: 20px;'>لا توجد إعلانات منشورة حالياً من قبل الإدارة.</div>";
}
$htmlTemplate = file_get_contents("../HTML/dashboardA.html");

$htmlTemplate = str_replace("{SIDEBAR}", $sidebarHtml,$htmlTemplate);
$htmlTemplate = str_replace("{{ADMIN_NAME}}",  htmlspecialchars($adminName),$htmlTemplate);
$htmlTemplate = str_replace('{{ANNOUNCEMENTS_LIST}}', $announcementsHtml, $htmlTemplate);
echo $htmlTemplate;

?>