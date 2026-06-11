<?php

//   نتحقق من صلاحيات المسؤول وننقل رسائل النجاح بين الصفحات
session_start(); 

// استدعاء ملف الاتصال بقاعدة البيانات وملفات الموديلز     
require_once '../config/db_connect.php'; 
require_once '../models/Admin.php';
require_once '../models/Announcement.php';
include 'includes/adminSidebar.php';

//  [التحقق الأمني] التأكد من أن المستخدم مسجل دخول وصلاحيته "admin" لحماية الصفحة من الدخول غير المصرح به
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit(); 
}

// جلب معرف الإعلان من الرابط   لنوع رقمي لمنع ثغرات   Injection
$announcementID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$conn = Database::getInstance()->getConnection(); // جلب اتصال قاعدة البيانات من كلاس Database (Singleton)
// استدعاء دالة جلب بيانات الإعلان من كائن الـ Announcement بناءً على المعرف (ID)
$announcementData = Announcement::getById($conn, $announcementID);

// التحقق من أن الإعلان موجود فعلياً في قاعدة البيانات لمنع أخطاء النظام 
if (!$announcementData) {
    die("الإعلان المطلوب غير موجود.");
    $_SESSION['msg'] = "الإعلان المطلوب غير موجود.";
    header("Location: adminAnnouncements.php"); // إعادة التوجيه لصفحة الإعلانات
    exit();
}

//  معالجة البيانات القادمة من الفورم عند الضغط على زر الحفظ (POST )
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $announcementTitle = $_POST['title'];
    $announcementContent = $_POST['content'];
    
    // احتفظنا بمسار الصورة القديمة كقيمة افتراضية في حال لم يقم المستخدم برفع صورة جديدة
    $imagePath = $announcementData['imagePath']; 

    // التحقق مما إذا قام المستخدم برفع ملف صورة جديد وبدون مشاكل (Error == 0)
    if (isset($_FILES['announcementImage']) && $_FILES['announcementImage']['error'] == 0) {
        $targetDir = "../uploads/";
        
        // إذا كان مجلد الرفع (uploads) غير موجود، نقوم بإنشائه برمجياً وإعطائه الصلاحيات المناسبة
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // توليد اسم فريد للملف باستخدام دالة الوقت time() لمنع تداخل الأسماء في السيرفر عند تكرار المرفقات
        $fileName = time() . "_" . basename($_FILES["announcementImage"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        // نقل الملف من المجلد المؤقت بالسيرفر إلى مجلد الرفع الخاص بمشروعنا
        if (move_uploaded_file($_FILES["announcementImage"]["tmp_name"], $targetFilePath)) {
            // [تحسين أداء السيرفر] حذف الصورة القديمة من السيرفر فوراً لتوفير مساحة التخزين ومنع تراكم الملفات المهملة
            if (!empty($announcementData['imagePath']) && file_exists("../" . $announcementData['imagePath'])) {
                unlink("../" . $announcementData['imagePath']);
            }
            
            // تعيين المسار الجديد الذي سيتم تخزينه في قاعدة البيانات
            $imagePath = "uploads/" . $fileName; 
        }
    }

    //  تطبيق مفاهيم البرمجة كائنية التوجه (OOP) لتحديث البيانات
    // إنشاء كائن الآدمن وتمرير اتصال قاعدة البيانات له
    $currentAdmin = new Admin($conn); 
    $currentAdmin->setUserId($_SESSION['userID']);

    // إنشاء كائن الإعلان وتعبئته بالبيانات الجديدة (Encapsulation)
    $announcement = new Announcement($announcementTitle, $announcementContent, $imagePath);
    $announcement->setAnnouncementId($announcementID);

    // تمرير كائن الإعلان إلى دالة التحديث التابعة للآدمن (Dependency Injection)
    if ($currentAdmin->updateAnnouncement($announcement)) {
        // تخزين حالة النجاح في السيسشن لعرضها بعد توجيه الصفحة (مبدأ Post-Redirect-Get)
        $_SESSION['announcement_update_success'] = true;
        header("Location: adminAnnouncements.php?id=" . $announcementID);
        exit();
    } else {
        echo "حدث خطأ أثناء تحديث الإعلان.";
    }
}

// تجهيز رسالة النجاح وتمريرها لواجهة المستخدم )
$success_msg = "";
if (isset($_SESSION['announcement_update_success'])) {
    $success_msg = "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 5px; text-align: center; font-weight: bold;'>تم تحديث الإعلان بنجاح!</div>";
    unset($_SESSION['announcement_update_success']); // حذف الرسالة من السيسشن لكي لا تظهر مجدداً عند عمل Refresh للصفحة
}

ob_start();
$sidebarHtml = ob_get_clean();

$html_template = file_get_contents("../HTML/editAnnouncement.html");
$html_template = str_replace("{SIDEBAR}", $sidebarHtml, $html_template);
$html_template = str_replace("{{SUCCESS_MESSAGE}}", $success_msg, $html_template);
$html_template = str_replace("{{TITLE}}", htmlspecialchars($announcementData['title']), $html_template);
$html_template = str_replace("{{CONTENT}}", htmlspecialchars($announcementData['content']), $html_template);
$html_template = str_replace("{{ID}}", $announcementID, $html_template);

// تجهيز كود عرض الصورة الحالية في الواجهة إذا كانت متوفرة في قاعدة البيانات
$current_image = "";
if (!empty($announcementData['imagePath'])) {
    $current_image = "<p>الصورة الحالية: <img src='../" . $announcementData['imagePath'] . "' style='width:100px; height:auto; vertical-align:middle; margin-right:10px;'></p>";
}
$html_template = str_replace("{{CURRENT_IMAGE}}", $current_image, $html_template);

// طباعة القالب النهائي للمتصفح 
echo $html_template;