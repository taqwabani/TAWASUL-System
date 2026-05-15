<?php
/**
 * صفحة الخاصة بمنطق عمل اضافة اعلان جديد 
 */
session_start(); 

require_once '../config/db_connect.php'; 
require_once '../models/Admin.php';
require_once '../models/Announcement.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //استقبال البيانات النصية من النموذج (العنوان والمحتوى)
    $announcementTitle = $_POST['title'];
    $announcementContent = $_POST['content'];
    $imagePath = ""; // متغير افتراضي لمسار الصورة في حال لم يتم رفع صورة
// التحقق من وجود ملف مرفوع وعدم وجود أخطاء في عملية الرفع
    if (isset($_FILES['announcementImage']) && $_FILES['announcementImage']['error'] == 0) {
        // تحديد المجلد المستهدف لرفع الصور
        $targetDir = "../uploads/";
        // إنشاء المجلد تلقائيا إذا لم يكن موجودا مع إعطاء صلاحيات الكتابة 
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        //  لتجنب تكرار الأسماء وتداخل الملفات time()انشاء اسم فريد للملف باستخدام دالة 
        $fileName = time() . "_" . basename($_FILES["announcementImage"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        // نقل الملف من المجلد المؤقت في السيرفر إلى المجلد الدائم للمشروع
        if (move_uploaded_file($_FILES["announcementImage"]["tmp_name"], $targetFilePath)) {
            $imagePath = "uploads/" . $fileName; 
        }
    }
    // إنشاء كائن من كلاس الادمن وتمرير اتصال قاعدة البيانات له
    $currentAdmin = new Admin($conn); 

    // ربط الإعلان بالمدير الحالي عن طريق جلب معرفه من الجلسة 
    if(isset($_SESSION['userID'])) {
        $currentAdmin->setUserId($_SESSION['userID']);
    }
    // إنشاء كائن إعلان جديد وتمرير كافة البيانات (العنوان، المحتوى، مسار الصورة)
    $newAnnouncement = new Announcement($announcementTitle, $announcementContent, $imagePath);
    // تنفيذ عملية الإضافة في قاعدة البيانات والتحقق من نجاحها
    if ($currentAdmin->addAnnouncement($newAnnouncement)) {
        $_SESSION['announcement_success'] = true; // تخزين حالة النجاح في الجلسة
        header("Location: addAnnouncement.php"); // العودة لصفحة 
        exit();
    } else {
        // إظهار رسالة خطأ في حال فشل الاستعلام في قاعدة البيانات
        echo "حدث خطأ أثناء إضافة الإعلان في قاعدة البيانات.";
    }
}

// منطق عرض الصفحة (GET)
$success_msg = "";
if (isset($_SESSION['announcement_success'])) {
    // رسالة النجاح مع تنسيق
    $success_msg = "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 5px; text-align: center; font-weight: bold;'> تم نشر الإعلان بنجاح!</div>";
    unset($_SESSION['announcement_success']); // حذف الرسالة من الجلسة فورا لكي لا تظهر عند التحديث
}

// قراءة القالب واستبدال العلامة المحجوزة
$html_template = file_get_contents("../HTML/addAnnouncement.html");
echo str_replace("{{SUCCESS_MESSAGE}}", $success_msg, $html_template);

?>