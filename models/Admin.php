<?php
require_once __DIR__ . '/user.php';
require_once 'Announcement.php';

class Admin extends User {
    //بتهيئة اتصال قاعدة البيانات عند إنشاء كائن جديد
    public function __construct($db,$userData = null) {
        parent::__construct($db);
        if ($userData) {
            $this->userID = $userData['userID'];
            $this->userName = $userData['userName'];
            $this->name = $userData['name'];
            $this->role = $userData['role'];
        }

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

    // دالة تعديل إعلان موجود
    public function updateAnnouncement(Announcement $announcement) {
        try {
            $sql = "UPDATE announcements SET title = ?, content = ?, imagePath = ? WHERE announcementID = ?";
            // تحضير الاستعلام
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                $announcement->getTitle(),
                $announcement->getContent(),
                $announcement->getImagePath(),
                $announcement->getAnnouncementId()
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // دالة حذف إعلان
    public function deleteAnnouncement($id) {
        try {
            $sql = "DELETE FROM announcements WHERE announcementID = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([(int)$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // جلب كافة المستخدمين من قاعدة البيانات
    public function getAllUsers() {
        try {
            $sql = "SELECT userID, userName, name, role FROM users ORDER BY userID DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }

    // حذف مستخدم من النظام
    public function deleteUser($id) {
        try {
            // جلب بيانات المستخدم للتحقق من صلاحياته قبل الحذف
            $user = $this->getUserById($id);
            
            // منع حذف أي حساب يتبع لـ "إدارة المدرسة" (role = admin) لضمان أمان النظام
            if (!$user || $user->role === 'admin') return false;
            
            $sql = "DELETE FROM users WHERE userID = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([(int)$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // إضافة مستخدم جديد للنظام (ولي أمر أو مدير)
    public function addUser($name, $userName, $password, $role) {
        try {
            $sql = "INSERT INTO users (name, userName, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $userName, $password, $role]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // جلب بيانات مستخدم معين بواسطة المعرف
    public function getUserById($id) {
        try {
            $sql = "SELECT userID, userName, name, role FROM users WHERE userID = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return null;
        }
    }

    // تحديث بيانات مستخدم
    public function updateUser($id, $name, $userName) {
        try {
            $sql = "UPDATE users SET name = ?, userName = ?
             WHERE userID = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $userName, (int)$id]);
        } catch (PDOException $e) {
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

    
    /**
     * جلب كافة معرفات أولياء الأمور لإرسال الإشعارات الجماعية
     */
    public function getAllParentIDs() {
        try {
            $sql = "SELECT userID FROM users WHERE role = 'parent'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }




}
?>