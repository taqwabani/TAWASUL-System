<?php
session_start(); 

// التحقق من وجود المستخدم، وإلا يتم توجيهه لصفحة الدخول
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}
require_once "../config/db_connect.php"; 
require_once "../models/Announcement.php";


$database = Database::getInstance();
$conn = $database->getConnection();
// جلب اسم ولي الأمر المخزن في السيسشن أثناء عملية تسجيل الدخول بنجاح
$parentName = isset($_SESSION['name']) ? $_SESSION['name'] : "زائر";

$anno = new Announcement();
$announcementsFromDB = $anno->getLatestAnnouncements($conn, 5); // جلب آخر 5 مستجدات

$announcementsHtml = "";

if (!empty($announcementsFromDB)) {
    foreach ($announcementsFromDB as $ann) {
        $formattedDate = date("Y-m-d", strtotime($ann->createdAt));
        
        $imageHtml = "";
        if (!empty($ann->imagePath)) {
            $imageHtml = "
            <div class='announcement-image-wrapper'>
                <img src='../" . htmlspecialchars($ann->imagePath) . "' alt='مرفق الإعلان' class='announcement-img'>
            </div>";
        }

        $announcementsHtml .= "
        <div class='announcement-dashboard-card'>
            <div class='announcement-card-content'>
                <div class='announcement-card-header'>
                    <h4 class='announcement-card-title'>" . htmlspecialchars($ann->title) . "</h4>
                    <span class='announcement-card-date'>{$formattedDate}</span>
                </div>
                <div class='announcement-card-body'>
                    " . nl2br(htmlspecialchars($ann->content)) . "
                </div>
            </div>
            {$imageHtml}
        </div>";
    }
} else {
    // في حال كانت قاعدة البيانات فارغة من الإعلانات
    $announcementsHtml = "<div style='text-align: center; color: #718096; padding: 20px;'>لا توجد إعلانات منشورة حالياً من قبل الإدارة.</div>";
}

ob_start(); 
include 'includes/sidebar.php'; 
$sidebarHtml = ob_get_clean(); 

$mainHtmlTemplate = file_get_contents("../HTML/dashboard.html");

$finalPageContent = str_replace('{SIDEBAR}', $sidebarHtml, $mainHtmlTemplate);
$finalPageContent = str_replace('{{PARENT_NAME}}', htmlspecialchars($parentName), $finalPageContent);
$finalPageContent = str_replace('{{ANNOUNCEMENTS_LIST}}', $announcementsHtml, $finalPageContent);

echo $finalPageContent;
?>