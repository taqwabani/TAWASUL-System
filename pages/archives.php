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

// تحديد اسم المستخدم للعرض بناءً على الجلسة، مع قيمة افتراضية
$userNameForDisplay = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : "زائر";
$userRole = $_SESSION['role'] ?? 'guest'; // الحصول على دور المستخدم
$user = '';
// تضمين الشريط الجانبي الصحيح بناءً على دور المستخدم
ob_start();
if ($userRole === 'admin') {
    include 'includes/adminSidebar.php';
    $user = "المدير";
} else { // إذا لم يكن مديراً (مثلاً ولي أمر)، نستخدم الشريط الجانبي العادي
    include 'includes/sidebar.php';
        $user = "ولي الأمر";
}
$sidebarHtml = ob_get_clean();

$announcementModel =
    new Announcement();

$announcements =
    $announcementModel->getArchiveAnnouncements($conn);

$archiveHtml = "";

foreach ($announcements as $ann) {
    $formattedDate =
        date(
            "Y-m-d",
            strtotime($ann->createdAt)
        );

    $imageHtml = "";

    if (!empty($ann->imagePath)) {
        $imageHtml = "
        <div class='announcement-image-wrapper'>
            <img
            src='../{$ann->imagePath}'
            class='announcement-img'>
        </div>
        ";
    }

    $archiveHtml .= "
    <div class='announcement-dashboard-card'>

        <div class='announcement-card-content'>

            <div class='announcement-card-header'>

                <h4 class='announcement-card-title'>
                    " . htmlspecialchars($ann->title) . "
                </h4>

                <span class='announcement-card-date'>
                    {$formattedDate}
                </span>

            </div>

            <div class='announcement-card-body'>
                " . nl2br(htmlspecialchars($ann->content)) . "
            </div>

        </div>

        {$imageHtml}

    </div>
    ";
}

$htmlTemplate =
    file_get_contents("../HTML/archives.html");
$htmlTemplate = str_replace("{SIDEBAR}", $sidebarHtml, $htmlTemplate); // استبدال الشريط الجانبي
$htmlTemplate = str_replace("{{PARENT_NAME}}", $userNameForDisplay, $htmlTemplate); // استبدال اسم المستخدم المعروض
$htmlTemplate = str_replace("{{NAME}}", $user, $htmlTemplate); // استبدال اسم المستخدم المعروض
$htmlTemplate = str_replace("{{ARCHIVE_ANNOUNCEMENTS}}", $archiveHtml, $htmlTemplate);

echo $htmlTemplate;
