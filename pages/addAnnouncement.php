<?php
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
        header("Location: adminAnnouncements.php?success=1");// إعادة التوجيه لصفحة عرض الإعلانات مع إرسال رسالة نجاح
        exit();
    } else {
        // إظهار رسالة خطأ في حال فشل الاستعلام في قاعدة البيانات
        echo "حدث خطأ أثناء إضافة الإعلان في قاعدة البيانات.";
    }
}
?>