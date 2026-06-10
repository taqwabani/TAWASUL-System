<?php
/* كلاس User مسؤول عن عمليات المستخدم
 مثل تسجيل الدخول والخروج وحفظ بيانات */
 
class User {
    protected $db;    // متغير الاتصال بقاعدة البيانات

    // بيانات المستخدم
    protected $userID;
    protected $userName; 
    protected $name;
    protected $password;
    protected $role;
    
    //  لاستقبال الاتصال بقاعدة البياناتConstructor
    public function __construct($db) {
        $this->db = $db;
    }

    // دالة التحقق من تسجيل الدخول
    public function validateLogin($u, $p) {
        try {
            $sql = "SELECT * FROM users WHERE userName = :u LIMIT 1";// جلب بيانات المستخدم حسب اسم المستخدم 
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['u' => $u]);
            $user = $stmt->fetch();

            if ($user && $p == $user['password']) { // التحقق من صحة كلمة المرور ووجود المستخدم
               // تخزين بيانات المستخدم داخل خصائص الكلاس
                $this->userID = $user['userID'];
                $this->userName = $user['userName'];
                $this->name = $user['name'];
                $this->role = $user['role'];
                return true;
            }

            return false;  // في حالة البيانات غير صحيحة
        } catch (PDOException $e) {

             // تسجيل الخطأ داخل ملف الأخطاء
            error_log($e->getMessage());
            return false;
        }
    }
    // دالة إنشاء جلسة بعد نجاح تسجيل الدخول
    public function login() {
        // التأكد من وجود المستخدم
        if (isset($this->userID)) {
            $_SESSION['userID'] = $this->userID;
            $_SESSION['role'] = $this->role;
            $_SESSION['name'] = $this->name;
            return true;
        }
        return false;
    }

    // دالة تسجيل الخروج
    public function logout() {
        
        session_unset();// حذف جميع بيانات 
        session_destroy();// إنهاء الجلسة الحالية
        header("Location: ../login.php");// إعادة المستخدم لصفحة تسجيل الدخول

        exit();
    }

    public function setUserId($id) {$this->userID = $id;}
    public function setRole($role) { $this->role = $role; }
    public function setName($name) { $this->name = $name; }
    public function getUserId() {return $this->userID; }
    public function getUserName() { return $this->userName; }
    public function getName() { return $this->name; }
    public function getRole() { return $this->role; }   // Getter للحصول على صلاحية المستخدم
}
?>