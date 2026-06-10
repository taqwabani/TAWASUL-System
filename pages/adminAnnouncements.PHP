<?php
/**
 * صفحة التحكم بالإعلانات (للإدارة)
 */
session_start();
require_once '../config/db_connect.php';
require_once '../models/Announcement.php';

// التحقق من الصلاحيات
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// جلب اتصال قاعدة البيانات من كلاس Database (Singleton)
$database = Database::getInstance();
$conn = $database->getConnection();

// جلب جميع الإعلانات
$announcements = Announcement::getAllAnnouncements($conn);
$rows_html = "";

if (count($announcements) > 0) {
    foreach ($announcements as $row) {
        // التأكد من جلب المعرف الصحيح (حسب مسمى العمود في قاعدة بياناتك)
        $id = isset($row['announcementID']) ? $row['announcementID'] : (isset($row['announcementId']) ? $row['announcementId'] : 0);
        $title = htmlspecialchars($row['title']);
        // معالجة التاريخ بشكل مرن
        $raw_date = isset($row['createdAt']) ? $row['createdAt'] : (isset($row['created_at']) ? $row['created_at'] : 'now');
        $date = date("Y-m-d", strtotime($raw_date));
        $image = !empty($row['imagePath']) ? "../" . $row['imagePath'] : "../images/default-ad.jpg";

        $rows_html .= "
        <tr>
            <td><img src='{$image}' class='thumb-img' alt='announcement' style='width:50px; height:50px; object-fit:cover; border-radius:5px;'></td>
            <td><span class='ad-title'>{$title}</span></td>
            <td>إدارة المدرسة</td>
            <td>{$date}</td>
            <td class='actions'>
                <a href='editAnnouncement.php?id={$id}' class='action-link edit'>تعديل</a>
                <a href='deleteAnnouncement.php?id={$id}' class='action-link delete' onclick='return confirm(\"هل أنت متأكد من حذف هذا الإعلان؟\")' style='color:red;'>حذف</a>
            </td>
        </tr>";
    }
} else {
    $rows_html = "<tr><td colspan='5' style='text-align:center;'>لا توجد إعلانات حالياً</td></tr>";
}

// قراءة القالب واستبدال المحتوى
$html_template = file_get_contents("../HTML/adminAnnouncements.html");

// استبدال جزء الـ tbody الثابت بالديناميكي
// ملاحظة: قمت بتعديل القالب يدوياً هنا ليقبل الاستبدال
$table_start = "<tbody>";
$table_end = "</tbody>";
$pattern = "~" . preg_quote($table_start, '~') . ".*?" . preg_quote($table_end, '~') . "~s";
$html_template = preg_replace($pattern, $table_start . $rows_html . $table_end, $html_template);

echo $html_template;