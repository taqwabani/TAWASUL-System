<?php
/**
 * منطق عمل صفحة المحادثة الخاصة بولي الأمر  
 */
session_start();
require_once "../config/db_connect.php"; 
require_once "../models/Message.php";    // كلاس الرسائل
require_once "../models/Inquiry.php";    // كلاس الاستفسارات

// التحقق من صلاحية الجلسة (UserID) لضمان دخول المستخدم المصرح له فقط
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

// استقبال معرف الاستفسار الحالي من الرابط (URL Parameters)
$currentInquiryID = isset($_GET['id']) ? $_GET['id'] : null;

if (!$currentInquiryID) {
    die("خطأ: لم يتم تحديد استفسار صالح.");
}

try {
    //الحصول على  نسخة وحدة من الاتصال  بقاعدة البيانات
    $database = Database::getInstance();
    $conn = $database->getConnection();

} catch (Exception $e) {
    die("خطأ في جلب اتصال قاعدة البيانات: " . $e->getMessage());
}

$parentName = isset($_SESSION['name']) ? $_SESSION['name'] : "ولي الأمر";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['messageText'])) {
    $messageText = trim($_POST['messageText']); // تنظيف النص من الفراغات
    $currentUserID = $_SESSION['userID'];

    if (!empty($messageText)) {
        try {
            $messageModel = new Message($conn);
            
            // استدعاء دالة حفظ الرسالة وتخزينها في جدول messages
            if ($messageModel->saveMessage($currentInquiryID, $currentUserID, $messageText)) {
                
                $inquiryModel = new Inquiry($conn);
                
                $inquiryModel->updateStatus($currentInquiryID, 'قيد الانتظار');
                header("Location: parentChat.php?id=" . $currentInquiryID);
                exit();
            }
        } catch (Exception $e) {
            error_log("خطأ في نظام المحادثة: " . $e->getMessage());
        }
    }
}

try {

    $inquiryModel = new Inquiry($conn);
    $chatData = $inquiryModel->viewInquiries($currentInquiryID);

    if (!$chatData || !$chatData['details']) {
        die("خطأ: الاستفسار المطلوب غير موجود.");
    }

    $messagesHTML = "";
    foreach ($chatData['chat'] as $msg) {
        $isParent = ($msg->senderID == $_SESSION['userID']);
        $bubbleClass = $isParent ? 'parent-msg' : 'admin-msg';
        $senderLabel = $isParent ? 'ولي الأمر' : 'الإدارة';

        $messagesHTML .= "
        <div class='message-bubble {$bubbleClass}'>
            <small>{$senderLabel}</small>
            <p>" . htmlspecialchars($msg->messageText) . "</p>
            <span style='font-size: 10px; display: block; margin-top: 5px;'>" . 
                date('h:i A', strtotime($msg->timestamp)) . // تنسيق وقت الرسالة
            "</span>
        </div>";
    }

   ob_start();
    include 'includes/sidebar.php'; 
    $sidebarHtml = ob_get_clean(); 

    $htmlContent = file_get_contents("../HTML/parentChat.html");
    
    $htmlContent = str_replace("{{CHAT_MESSAGES}}", $messagesHTML, $htmlContent);
    $htmlContent = str_replace("{{INQUIRY_ID}}", $currentInquiryID, $htmlContent);
    $htmlContent = str_replace("{{INQUIRY_SUBJECT}}", htmlspecialchars($chatData['details']->subject), $htmlContent);
    
    $formattedDate = date('Y/m/d', strtotime($chatData['details']->created_at));
    $htmlContent = str_replace("{{INQUIRY_DATE}}", $formattedDate, $htmlContent);

    $htmlContent = str_replace("{SIDEBAR}", $sidebarHtml, $htmlContent);
    $htmlContent = str_replace('{{PARENT_NAME}}', htmlspecialchars($parentName), $htmlContent);

    echo $htmlContent; 

} catch (PDOException $e) {
    die("حدث خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>