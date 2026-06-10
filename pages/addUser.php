<?php
/**
 * صفحة إنشاء مستخدم جديد (للإدارة)
 */
session_start();
require_once '../config/db_connect.php';
require_once '../models/UserFactory.php';

// التحقق من الصلاحيات
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $userName = $_POST['userName'];
    $password = $_POST['password']; // ملاحظة: يفضل تشفير كلمة المرور مستقبلاً
    $role = $_POST['role'];
$conn = Database::getInstance()->getConnection(); // جلب اتصال قاعدة البيانات من كلاس Database (Singleton)

    $admin = UserFactory::create($conn, 'admin');
    $admin->setUserId($_SESSION['userID']);

    if ($admin->addUser($name, $userName, $password, $role)) {
        $_SESSION['user_msg'] = "تم إضافة المستخدم بنجاح";
        header("Location: u.PHP");
        exit();
    } else {
        $msg = "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 5px; text-align: center;'>حدث خطأ، ربما اسم المستخدم موجود مسبقاً.</div>";
    }
}

// قراءة القالب
if (file_exists("../HTML/addUser.html")) {
    $html_template = file_get_contents("../HTML/addUser.html");
    echo str_replace("{{MESSAGE}}", $msg, $html_template);
} else {
    echo "خطأ: ملف الواجهة addUser.html غير موجود.";
}
?>