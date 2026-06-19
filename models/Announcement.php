<?php

/**
 * كلاس الاعلانات يحتوي على خصائصه ودوال للوصول الى البيانات عند الحاجة 
 */
class Announcement
{

    private $announcementId;
    private $title;
    private $content;
    private $date;
    private $imagePath;


    public function __construct($title = null, $content = null, $imagePath = null)
    {
        $this->title = $title;
        $this->content = $content;
        $this->imagePath = $imagePath;
        $this->date = date("Y-m-d H:i:s");
    }
    // Getters للوصول للبيانات من الكلاسات الأخرى
    public function getTitle()
    {
        return $this->title;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function getImagePath()
    {
        return $this->imagePath;
    }
    public function getDate()
    {
        return $this->date;
    }

    public function getAnnouncementId()
    {
        return $this->announcementId;
    }
    public function setAnnouncementId($id)
    {
        $this->announcementId = $id;
    }

    /**
     * جلب كافة الإعلانات لشاشة الإدارة
     */
    public static function getAllAnnouncements($db)
    {
        try {
            $sql = "SELECT * FROM announcements ORDER BY createdAt DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب كافة الإعلانات: " . $e->getMessage());
            return [];
        }
    }

    /**
     * جلب إعلان محدد بواسطة المعرف
     */
    public static function getById($db, $id)
    {
        try {
            $sql = "SELECT * FROM announcements WHERE announcementId  = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([(int)$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب الإعلان: " . $e->getMessage());
            return null;
        }
    }

    public function getLatestAnnouncements($db, $limit = 5)
    {
        try {
            $sql = "SELECT title, content, imagePath, createdAt FROM announcements ORDER BY createdAt DESC LIMIT :limit";
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("خطأ في جلب الإعلانات: " . $e->getMessage());
            return [];
        }
    }
    public static function searchAnnouncements($conn, $keyword)
    {
        try {
            $sql = "
                SELECT *
                FROM announcements
                WHERE title LIKE ?
                OR content LIKE ?
                ORDER BY createdAt DESC
            ";

            $stmt = $conn->prepare($sql);

            $searchTerm = "%" . $keyword . "%";

            $stmt->execute([$searchTerm, $searchTerm]);

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("خطأ في البحث عن الإعلانات: " . $e->getMessage());
            return [];
        }
    }

    public function getArchiveAnnouncements($conn)
    {
        try {

            $sql = "
        SELECT
            announcementID,
            title,
            content,
            imagePath,
            createdAt
        FROM announcements
        ORDER BY createdAt DESC
        ";

            $stmt = $conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {

            error_log(
                "خطأ في جلب أرشيف الإعلانات: "
                    . $e->getMessage()
            );

            return [];
        }
    }
}
