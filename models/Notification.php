<?php

class NotificationManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
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