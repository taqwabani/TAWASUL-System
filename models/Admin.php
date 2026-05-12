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
}
?>