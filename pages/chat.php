<?php
/**
 * صفحة الخاصة بمنظق عمل الشات لرد ع الاستفسارات الخاصة بالادمن
 * 
 */
require_once "../config/db_connect.php"; 
require_once "../models/UserFactory.php"; 

$inquiryId = isset($_GET['id']) ? $_GET['id'] : null;
if (!$inquiryId)
    {
         die("خطأ: لم يتم تحديد استفسار.");
    }

$admin = UserFactory::create($conn, 'admin');// إنشاء كائن من كلاس الإدارة للتعامل مع البيانات الخاصة بالاستفسارات والمحادثات
$data = $admin->viewInquiries($inquiryId);// جلب بيانات الاستفسار والمحادثة

if (!$data || !$data['details']) // التحقق من وجود بيانات للاستفسار
    {
    die("خطأ: الاستفسار غير موجود.");   // إيقاف تنفيذ الصفحة وإظهار رسالة خطأ
    
    }

$inquiryInfo = $data['details'];// بيانات الاستفسار الأساسية
$chatMessages = $data['chat'];// رسائل المحادثة

$messagesHTML = "";
foreach ($chatMessages as $msg) {// المرور على جميع الرسائل لعرضها داخل صفحة المحادثة
    // تمييز الرسالة بناء على المرسل
    $bubbleClass = ($msg->senderID == $inquiryInfo->parentID) ? 'parent-msg' : 'admin-msg';
    $senderLabel = ($msg->senderID == $inquiryInfo->parentID) ? 'ولي الأمر' : 'الإدارة';
    // جلب المعرفات مع دعم اختلاف حالة الأحرف التي قد تنتج عن قاعدة البيانات
    $msgSenderID = $msg->senderID ?? $msg->senderid ?? null;
    $inquiryParentID = $inquiryInfo->parentID ?? $inquiryInfo->parentid ?? null;

    // المقارنة الرقمية الصارمة لتحديد هوية المرسل
    $isParent = ((int)$msgSenderID === (int)$inquiryParentID);
    $bubbleClass = $isParent ? 'parent-msg' : 'admin-msg';
    $senderLabel = $isParent ? 'ولي الأمر' : 'الإدارة';
       // إنشاء شكل الرسالة داخل المحادثة
    $messagesHTML .= "
    <div class='message-bubble {$bubbleClass}'>
        <small>{$senderLabel}</small>
        <p>" . htmlspecialchars($msg->messageText) . "</p>
        <span style='font-size: 10px; display: block; margin-top: 5px;'>" . 
            date('h:i A', strtotime($msg->timestamp)) . 
        "</span>
    </div>";
}


$htmlFile = file_get_contents("../HTML/chat.html");

// استبدال القيم داخل ملف HTML
$htmlFile = str_replace("{{parentName}}", htmlspecialchars($inquiryInfo->parentName), $htmlFile);
$htmlFile = str_replace("{{subject}}", htmlspecialchars($inquiryInfo->subject), $htmlFile);
$htmlFile = str_replace("{{date}}", date('Y/m/d', strtotime($inquiryInfo->created_at)), $htmlFile);
$htmlFile = str_replace("{{chatContent}}", $messagesHTML, $htmlFile);
$htmlFile = str_replace("{{inquiryID}}", $inquiryId, $htmlFile);

echo $htmlFile;
?>