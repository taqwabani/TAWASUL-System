<?php
session_start();
require_once "../config/db_connect.php"; 
require_once "../models/Inquiry.php";



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inquiryID = $_POST['inquiryID'];// جلب رقم الاستفسار
    $messageText = trim($_POST['messageText']);    // جلب نص الرسالة مع حذف الفراغات الزائدة

    
    //   الشخص المسجل حالياً في النظامID
    $senderID = $_SESSION['userID']; 
    // التحقق من أن البيانات المطلوبة غير فارغة

    if (!empty($messageText) && !empty($inquiryID)) {
        try {
  // استعلام إضافة الرسالة إلى قاعدة البيانات
           $sql = "INSERT INTO messages (inquiryID, senderID, messageText) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
        
 // تنفيذ الاستعلام مع تمرير البيانات المطلوبة
            if ($stmt->execute([$inquiryID, $senderID, $messageText])) {
                 $inquiryObj = new Inquiry($conn);

                //  تحديث الحالة إلى 'تم الرد'
                  $inquiryObj->updateStatus($inquiryID, 'تم الرد');

            // العودة للصفحة السابقة بعد النجاح
            header("Location: chat.php?id=" . $inquiryID);
            exit();
            
        }//عرض رسالة الخطأ في حال وجود مشكلة بقاعدة البيانات
        } catch (PDOException $e) {
            echo "خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    } else {
        echo "البيانات غير مكتملة.";
    }
}
?>