<?php
session_start();
require_once "../config/db_connect.php";
require_once "../models/Notification.php"; 

if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

try {
    $database = Database::getInstance();
    $conn = $database->getConnection();

    $parentName = isset($_SESSION['name']) ? $_SESSION['name'] : "ولي الأمر";

    $notifyManager = new NotificationManager($conn);
    $notifications = $notifyManager->getUserNotifications($_SESSION['userID']);

    $notifHtml = "";
    if (empty($notifications)) {
        $notifHtml = "<div class='no-data'>لا توجد إشعارات جديدة.</div>";
    } else {
        foreach ($notifications as $note) {
            $borderClass = ($note['source'] == 'admin') ? 'border-admin' : 'border-system';
    
    $notifHtml .= "
    <div class='notification-card {$borderClass}'>
        <div class='notif-content'>
            <h4 class='notif-title'>" . htmlspecialchars($note['title']) . "</h4>
            <p class='notif-message'>" . htmlspecialchars($note['message']) . "</p>
        </div>
        <div class='notif-footer'>
            <span class='notif-time'> 🕒 " . date('H:i', strtotime($note['created_at'])) . "</span>
        </div>
    </div>";
    
}
    }

    ob_start();
    include 'includes/sidebar.php'; 
    $sidebarHtml = ob_get_clean(); 
    $htmlTemplate = file_get_contents("../HTML/notifications.html");

    $finalOutput = str_replace("{SIDEBAR}", $sidebarHtml, $htmlTemplate);
    $finalOutput = str_replace('{{PARENT_NAME}}', htmlspecialchars($parentName), $finalOutput);
    $finalOutput = str_replace("{{NOTIFICATIONS_LIST}}", $notifHtml, $finalOutput); // يجب وضع هذا الرمز في الـ HTML
    
    echo $finalOutput;

} catch (Exception $e) {
    die("حدث خطأ في تحميل صفحة الإشعارات: " . $e->getMessage());
}
?>