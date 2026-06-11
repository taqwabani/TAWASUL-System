<?php

session_start();

require_once "../config/db_connect.php";
require_once "../models/Announcement.php";


$database = Database::getInstance();
$conn = $database->getConnection();

$parentName = isset($_SESSION['name']) ? $_SESSION['name'] : "زائر";

$announcementModel =
    new Announcement();

$announcements =
    $announcementModel->getArchiveAnnouncements($conn);

$archiveHtml = "";

foreach ($announcements as $ann) {
    $formattedDate =
        date(
            "Y-m-d",
            strtotime($ann->createdAt)
        );

    $imageHtml = "";

    if (!empty($ann->imagePath)) {
        $imageHtml = "
        <div class='announcement-image-wrapper'>
            <img
            src='../{$ann->imagePath}'
            class='announcement-img'>
        </div>
        ";
    }

    $archiveHtml .= "
    <div class='announcement-dashboard-card'>

        <div class='announcement-card-content'>

            <div class='announcement-card-header'>

                <h4 class='announcement-card-title'>
                    {$ann->title}
                </h4>

                <span class='announcement-card-date'>
                    {$formattedDate}
                </span>

            </div>

            <div class='announcement-card-body'>
                {$ann->content}
            </div>

        </div>

        {$imageHtml}

    </div>
    ";
}

ob_start();
include 'includes/sidebar.php';
$sidebarHtml = ob_get_clean();

$htmlTemplate =
    file_get_contents("../HTML/archives.html");

$htmlTemplate = str_replace("{SIDEBAR}", $sidebarHtml, $htmlTemplate);
$htmlTemplate = str_replace("{{PARENT_NAME}}", htmlspecialchars($parentName), $htmlTemplate);
$htmlTemplate = str_replace("{{ARCHIVE_ANNOUNCEMENTS}}", $archiveHtml, $htmlTemplate);

echo $htmlTemplate;
