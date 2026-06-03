<?php
session_start();
include('../config/db_connect.php');
require_once "../models/Announcement.php";

// التحقق من تسجيل الدخول لحماية الصفحة
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

//  جلب آخر 3 إعلانات من قاعدة البيانات
$latest = Announcement::getLatestAnnouncements($conn, 3);
$announcements_cards = "";
if (!empty($latest)) {
    foreach ($latest as $ad) {
        $title = htmlspecialchars($ad['title']);
        
        // معالجة التاريخ بشكل آمن (دعم التسميتين created_at أو createdAt)
        $raw_date = isset($ad['createdAt']) ? $ad['createdAt'] : (isset($ad['created_at']) ? $ad['created_at'] : null);
        $date = $raw_date ? date("Y-m-d", strtotime($raw_date)) : "تاريخ غير متوفر";
        
        $content = mb_strimwidth(htmlspecialchars($ad['content']), 0, 100, "..."); // اختصار النص

        // معالجة عرض الصورة في حال وجودها (خيار الصور)
        $img_tag = "";
        if (!empty($ad['imagePath'])) {
            $img_url = "../" . $ad['imagePath'];
            $img_tag = "<img src='{$img_url}' style='width: 60px; height: 60px; object-fit: cover; border-radius: 6px; margin-left: 15px; border: 1px solid #ddd;'>";
        }

        $announcements_cards .= "
        <div style='background: #fdfdfd; border-right: 4px solid #010d50; padding: 15px; margin-bottom: 10px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center;'>
            {$img_tag}
            <div style='flex: 1;'>
                <div style='display:flex; justify-content:space-between; align-items: center;'>
                    <strong style='color:#010d50;'>$title</strong>
                    <small style='color:#888;'>$date</small>
                </div>
                <p style='font-size: 0.9rem; color: #555; margin: 5px 0 0 0;'>$content</p>
            </div>
        </div>";
    }
} else {
    $announcements_cards = "<p style='text-align:center; color:#888;'>لا توجد إعلانات جديدة حالياً.</p>";
}

// 3. قراءة ملف القالب واستبدال العلامة المحجوزة
$html = file_get_contents('../HTML/dashboard.html');
echo str_replace("{{LATEST_ANNOUNCEMENTS}}", $announcements_cards, $html);
?>
