<?php
/**
 * صفحة تعديل بيانات مستخدم (للإدارة)
 */
session_start();
require_once '../config/db_connect.php';
require_once '../models/Admin.php';
include 'includes/adminSidebar.php';

// التحقق من الصلاحيات
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
$conn = Database::getInstance()->getConnection(); // جلب اتصال قاعدة البيانات من كلاس Database (Singleton)

$admin = new Admin($conn);
$admin->setUserId($_SESSION['userID']);

$userID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userData = $admin->getUserById($userID);

if (!$userData) {
    die("المستخدم غير موجود.");
}

$error_msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $userName = $_POST['userName'];
    $role = $_POST['role'];

    if ($admin->updateUser($userID, $name, $userName)) {
        $_SESSION['user_msg'] = "تم تحديث بيانات المستخدم بنجاح";
        header("Location: user.PHP");
        exit();
    } else {
        $error_msg = "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 5px; text-align: center;'>حدث خطأ أثناء التحديث.</div>";
    }
}


ob_start();
$sidebarHtml = ob_get_clean();

$html_template = file_get_contents("../HTML/editUser.html");
$html_template = str_replace("{SIDEBAR}", $sidebarHtml, $html_template);
$html_template = str_replace("{{NAME}}", htmlspecialchars($userData->name), $html_template);
$html_template = str_replace("{{USERNAME}}", htmlspecialchars($userData->userName), $html_template);
$html_template = str_replace("{{ID}}", $userID, $html_template);
$html_template = str_replace("{{ERROR_MESSAGE}}", $error_msg, $html_template);


echo $html_template;