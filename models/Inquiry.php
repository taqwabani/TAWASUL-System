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

    /**
     * جلب الاستفسارات أو تفاصيل استفسار محدد مع رسائله
     */
    public function viewInquiries($inquiryId = null) {
        try {
            if ($inquiryId) {
                // جلب بيانات الاستفسار 
                $sqlInfo = "SELECT i.*, u.name as parentName 
                            FROM inquiries i 
                            JOIN users u ON i.parentID = u.userID 
                            WHERE i.inquiryID = ?";
                $stmt1 = $this->db->prepare($sqlInfo);
                $stmt1->execute([$inquiryId]);
                $info = $stmt1->fetch(PDO::FETCH_OBJ);

                // جلب الرسائل المرتبطة من جدول messages بترتيب زمني تصاعدي
                $sqlMsgs = "SELECT * FROM messages WHERE inquiryID = ? ORDER BY timestamp ASC";
                $stmt2 = $this->db->prepare($sqlMsgs);
                $stmt2->execute([$inquiryId]);
                $messages = $stmt2->fetchAll(PDO::FETCH_OBJ);

                return [
                    'details' => $info,
                    'chat' => $messages
                ];
            } else {
                // في حال عدم وجود معرف، جلب قائمة كافة الاستفسارات (للمدير)
                $sql = "SELECT i.inquiryID as inquiryId, i.status, i.subject, i.created_at, u.name as parentName 
                        FROM inquiries i 
                        JOIN users u ON i.parentID = u.userID 
                        ORDER BY i.inquiryID DESC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                
                return $stmt->fetchAll(PDO::FETCH_OBJ);
            }
        } catch (PDOException $e) {
            return ($inquiryId) ? null : [];
        }
    }

}



?>