<?php
/*
المحتوى
ارسال استفسارات جديدة ، وعرض قائمة باستفساراته السابق
*/
session_start();

require_once "../config/db_connect.php"; 
require_once "../models/perent.php";

// التحقق من وجود المستخدم، وإلا يتم توجيهه لصفحة الدخول
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php"); 
    exit();
}
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
        header("Location: inquiries.php?status=success");
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
foreach ($inquiries as $row) {
     
    $date = date("Y-m-d", strtotime($row['created_at']));

    $statusClass = ($row['status'] == 'تم الرد') ? 'replied' : 'pending';
    
    $rows_html .= "<tr>
        <td><a href='messages.php?id={$row['inquiryID']}'>
                <img src='../images/lucide-MessageSquare.svg' class='action-icon'>
            </a>
        <td>{$date}</td>
        <td>{$row['subject']}</td>
        <td><span class='status {$statusClass}'>{$row['status']}</span></td>
    </tr>";
}
// HTML دمج المحتوى البرمجي مع قالب الـ 
$html_template = file_get_contents("../HTML/inquiries.html");
echo str_replace("{{INQUIRIES}}", $rows_html, $html_template);//بالصفوف الحقيقية التي تم تجهيزها {{INQUIRIES}}استبدال الكلمة المفتاحية  

?>