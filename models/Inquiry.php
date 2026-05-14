<?php
/*
جزء كلاس الاستفسار
مشؤول عن إدارة العمليات الخاصة بالاستفسارات  
*/
class Inquiry {
    private $db;

    private $inquiryID;
    private $status;
    private $parentID;
    private $subject;
    

    public function __construct($db) {
        $this->db = $db;
    }

     //دالة إنشاء استفسار جديد
    public function create($parentID, $subject, $messageText) {
        try {
            //لضمان تنفيذ العمليتين معاً أو إلغائهما في حال حدوث خطا Transactionاستخدام 
            $this->db->beginTransaction();

            // inquiries الحفظ في جدول 
            $stmt = $this->db->prepare("INSERT INTO inquiries (parentID, status, subject) VALUES (?, 'قيد الانتظار', ?)");
            $stmt->execute([$parentID, $subject]);
            $inquiryID = $this->db->lastInsertId();

            // messages الحفظ في جدول 
            $stmtMsg = $this->db->prepare("INSERT INTO messages (inquiryID, senderID, messageText) VALUES (?, ?, ?)");
            $stmtMsg->execute([$inquiryID, $parentID, $messageText]);

            // اعتماد الحفظ النهائي في قاعدة البيانات
            $this->db->commit();

            return true;
            
        } catch (Exception $e) {
            // في حال حدوث أي خطأ، يتم التراجع عن كل العمليات السابقة
            $this->db->rollBack();
            return false;
        }
    }

    
    // دالة لتحديث حالة الاستفسار (مثلاً من قيد الانتظار إلى تم الرد)
    public function updateStatus($inquiryID, $newStatus) {
    try {// تحديث حالة الاستفسار في قاعدة البيانات
        $stmt = $this->db->prepare("UPDATE inquiries SET status = ? WHERE inquiryID = ?");
        return $stmt->execute([$newStatus, $inquiryID]);
    } catch (Exception $e) {
        return false;
    }
}

}



?>