<?php
/*
المحتوى
ارسال استفسارات جديدة ، وعرض قائمة باستفسارات ولي الامر السابقه
*/
session_start();

require_once "../config/db_connect.php"; 
require_once "../models/perent.php";


// التحقق من وجود المستخدم، وإلا يتم توجيهه لصفحة الدخول
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php"); 
    exit();
}

try {
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
} catch (Exception $e) {
    die("خطأ في جلب اتصال قاعدة البيانات: " . $e->getMessage());
}
$parentName = isset($_SESSION['name']) ? $_SESSION['name'] : "ولي الأمر";

//معالجة البيانات القادمة من نموذج إرسال الاستفسار
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send'])) {
    // استقبال البيانات من حقول الإدخال
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    
    if (!empty($subject) && !empty($message)) {
        //للتعامل مع العمليات الخاصة بولي الأمر  ParentUser إنشاء كائن من كلاس
        $parent = new ParentUser($conn);
        $currentUserID = $_SESSION['userID']; 
    // استدعاء الدالة الخاصة بإرسال الاستفسار وتمرير البيانات لقاعدة البيانات
    if ($parent->sendInquiry($conn, $subject, $message,   $currentUserID)) {
        $_SESSION['status_success'] = true; // تخزين حالة النجاح في الجلسة
        header("Location: inquiries.php"); // التحويل لصفحة من جديد
        exit();
    }
    }
}

//جلب الاستفسارات السابقة الخاصة بهذا المستخدم لعرضها في الجدول
$query = "SELECT * FROM inquiries WHERE parentID = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['userID']]);
$inquiries = $stmt->fetchAll();

$rows_html = "";
//  عرض جميع الاستفسارا في الجدول 
foreach ($inquiries as $row) {
     
    $date = date("Y-m-d", strtotime($row['created_at']));

    $statusClass = ($row['status'] == 'تم الرد') ? 'replied' : 'pending';
    
    $rows_html .= "<tr>
        <td><a href='../pages/parentChat.php?id={$row['inquiryID']}'>
                <img src='../images/lucide-MessageSquare.svg' class='action-icon'>
            </a>
        <td>{$date}</td>
        <td>{$row['subject']}</td>
        <td><span class='status {$statusClass}'>{$row['status']}</span></td>
    </tr>";
}


// التحقق من تم ارسال الاستفساربنجاح
$success_msg = "";
if (isset($_SESSION['status_success'])) {
    $success_msg = "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 5px; text-align: center; font-weight: bold;'>تم إرسال استفسارك بنجاح.</div>";
    unset($_SESSION['status_success']); // حذف الرسالة من الجلسة فوراً لكي لا تظهر عند التحديث
}


ob_start();
include 'includes/sidebar.php'; 
$sidebarHtml = ob_get_clean(); 

$html_template = file_get_contents("../HTML/inquiries.html");

$html_template = str_replace("{{SUCCESS_MESSAGE}}", $success_msg, $html_template);
$html_template = str_replace("{{INQUIRIES}}", $rows_html, $html_template);//بالصفوف الحقيقية التي تم تجهيزها {{INQUIRIES}}استبدال الكلمة المفتاحية

$html_template = str_replace("{SIDEBAR}", $sidebarHtml, $html_template);

$html_template = str_replace('{{PARENT_NAME}}', htmlspecialchars($parentName), $html_template);
echo $html_template;

?>