<?php

class Notification {
    private $db;

    private $id;
    private $title;
    private $message;
    private $createdAt;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * دالة لإنشاء إشعار جديد في قاعدة البيانات
     */
    public function createNotification($userID, $title, $message, $source = 'system') {
        try {
            $sql = "INSERT INTO notifications (userID, title, message, source, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userID, $title, $message, $source]);
        } catch (PDOException $e) {
            error_log("خطأ في إنشاء الإشعار: " . $e->getMessage());
            return false;
        }
    }
    
    // دالة لجلب إشعارات مستخدم معين
    public function getUserNotifications($userID) {
        $sql = "SELECT * FROM notifications WHERE userID = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
}
?>