<?php
session_start();
require_once '../config/db_connect.php';
require_once '../models/Admin.php';
require_once '../models/Announcement.php';
//التاكد من صلاحية الادمن
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: adminAnnouncements.php");
    exit();
}

$id = (int)$_GET['id'];
$announcement = Announcement::getById($conn, $id);

if ($announcement) {
    // حذف ملف الصورة من السيرفر إذا وجد
    if (!empty($announcement['imagePath'])) {
        $fullPath = "../" . $announcement['imagePath'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    $admin = new Admin($conn);
    if ($admin->deleteAnnouncement($id)) {
        $_SESSION['msg'] = "تم حذف الإعلان بنجاح";
    } else {
        $_SESSION['msg'] = "فشل حذف الإعلان";
    }
}

header("Location: adminAnnouncements.php");
exit();