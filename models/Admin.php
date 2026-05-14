<?php
require_once 'user.php';
require_once 'Announcement.php';

class Admin extends User {
    //بتهيئة اتصال قاعدة البيانات عند إنشاء كائن جديد
    public function __construct($db) {
        $this->db = $db;
    }
    //دالة الخاصة باضافة اعلان
    public function addAnnouncement(Announcement $announcement) {
        try {
            $sql = "INSERT INTO announcements (adminId, title, content, imagePath, createdAt) 
                    VALUES (?, ?, ?, ?, ?)";
            // تحضير الاستعلام
            $stmt = $this->db->prepare($sql);
            // جلب بيانات الإعلان من الكائن الممرر عبر دوال الجلب (Getters)
            $adminId = $this->userID; 
            $title = $announcement->getTitle();
            $content = $announcement->getContent();
            $imagePath = $announcement->getImagePath();
            $date = $announcement->getDate();
            // تنفيذ الاستعلام وتمرير المصفوفة التي تحتوي على القيم الفعلية
            return $stmt->execute([$adminId, $title, $content, $imagePath, $date]);
            
        } catch (PDOException $e) {
            // falseفي حال حدوث أي خطأ في قاعدة البيانات، يتم إرجاع قيمة 
            return false;
        }
    }

    
    public function viewInquiries($inquiryId = null) {
    try {
        if ($inquiryId) { // جلب تفاصيل استفسار محدد مع رسائله (لشاشة الشات)
           
            
            // جلب بيانات الاستفسار واسم ولي الأمر
            $sqlInfo = "SELECT i.*, u.name as parentName 
                        FROM inquiries i 
                        JOIN users u ON i.parentID = u.userID 
                        WHERE i.inquiryID = ?";
            $stmt1 = $this->db->prepare($sqlInfo);
            $stmt1->execute([$inquiryId]);
            $info = $stmt1->fetch(PDO::FETCH_OBJ);

            //بترتيب زمني messages جلب الرسائل المرتبطة من جدول
            $sqlMsgs = "SELECT * FROM messages WHERE inquiryID = ? ORDER BY timestamp ASC";
            $stmt2 = $this->db->prepare($sqlMsgs);
            $stmt2->execute([$inquiryId]);
            $messages = $stmt2->fetchAll(PDO::FETCH_OBJ);

            // نرجع مصفوفة تحتوي على النوعين من البيانات
            return [
                'details' => $info,// بيانات الاستفسار مع اسم ولي الأمر
                'chat' => $messages
            ];

        } else {  //  جلب كل القائمة في شاشة الجدول

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