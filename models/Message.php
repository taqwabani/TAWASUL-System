<?php
/* كلاس الرسائل - المسؤول عن إدارة  محتوى الرسائل
*/

class Message {

    private $db;

    private $messageId;
    private $inquiryId;
    private $senderId;
    private $messageText;
    private $timestamp;

    public function __construct($db) {
        $this->db = $db;
    }

    
    public function saveMessage($inquiryId, $senderId, $messageText) {// دالة لحفظ رسالة جديدة في قاعدة البيانات
        try {
          
            $sql = "INSERT INTO messages (inquiryID, senderID, messageText) VALUES (?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            
            
            $result = $stmt->execute([$inquiryId, $senderId, $messageText]);
            
            return $result; // إرجاع true في حال النجاح
            
        } catch (PDOException $e) {
            // في حال حدوث خطأ في قاعدة البيانات
            return false;
        }
    }
}
?>