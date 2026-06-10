<?php
/**
 * صفحة إنشاء مستخدم جديد (للإدارة)
 */
session_start();
require_once '../config/db_connect.php';
require_once '../models/UserFactory.php';
include 'includes/adminSidebar.php';

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
        header("Location: user.PHP");
        exit();
    } else {
        $msg = "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 5px; text-align: center;'>حدث خطأ، ربما اسم المستخدم موجود مسبقاً.</div>";
    }
}

ob_start();
$sidebarHtml = ob_get_clean();

$html_template = file_get_contents("../HTML/addUser.html");
$html_template = str_replace("{SIDEBAR}", $sidebarHtml, $html_template);
$html_template = str_replace("{{MESSAGE}}", $msg, $html_template);

echo $html_template;
?>