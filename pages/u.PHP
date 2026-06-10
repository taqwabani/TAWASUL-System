<?php
/**
 * صفحة إدارة المستخدمين (للإدارة)
 */
session_start();
require_once '../config/db_connect.php';
require_once '../models/Admin.php';

// التحقق من الصلاحيات: يجب أن يكون المستخدم "admin"
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
$conn = Database::getInstance()->getConnection(); // جلب اتصال قاعدة البيانات من كلاس Database (Singleton)

$admin = new Admin($conn);
$admin->setUserId($_SESSION['userID']);

// معالجة طلب الحذف إذا تم إرسال معرف المستخدم عبر الرابط
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    if ($admin->deleteUser($deleteId)) {
        $_SESSION['user_msg'] = "تم حذف المستخدم بنجاح";
    } else {
        $_SESSION['user_msg'] = "فشل حذف المستخدم (لا يمكنك حذف حسابك الخاص)";
    }
    header("Location: u.PHP");
    exit();
}

// جلب قائمة المستخدمين
$users = $admin->getAllUsers();
$rows_html = "";

foreach ($users as $user) {
    $roleText = ($user->role === 'admin') ? 'مدير' : 'ولي أمر';
    $rows_html .= "
    <tr>
        <td>" . htmlspecialchars($user->name) . "</td>
        <td>" . htmlspecialchars($user->userName) . "</td>
        <td>" . $roleText . "</td>
        <td class='actions'>
            <a href='editUser.php?id={$user->userID}' class='action-link edit' style='margin-left:10px; color:#001f3f; text-decoration:none;'>تعديل</a>
            <a href='u.PHP?delete_id={$user->userID}' class='action-link delete' onclick='return confirm(\"هل أنت متأكد من حذف هذا المستخدم؟ سيؤدي ذلك لحذف كافة بياناته المرتبطة.\")' style='color:red;'>حذف</a>
        </td>
    </tr>";
}

if (empty($rows_html)) {
    $rows_html = "<tr><td colspan='4' style='text-align:center;'>لا يوجد مستخدمين حالياً في النظام</td></tr>";
}

// عرض رسالة الحالة (نجاح أو فشل)
$status_msg = "";
if (isset($_SESSION['user_msg'])) {
    $status_msg = "<div style='padding:12px; background:#e3f2fd; border:1px solid #bbdefb; color:#0d47a1; margin-bottom:15px; border-radius:8px; text-align:center; font-weight:bold;'>{$_SESSION['user_msg']}</div>";
    unset($_SESSION['user_msg']);
}

// ربط البيانات بالقالب
$html_template = file_get_contents("../HTML/u.html");
$html_template = str_replace("{{USERS_TABLE}}", $rows_html, $html_template);
$html_template = str_replace("{{STATUS_MESSAGE}}", $status_msg, $html_template);

echo $html_template;
?>