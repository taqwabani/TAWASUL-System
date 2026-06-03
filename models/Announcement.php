<?php

/**
 * كلاس الاعلانات يحتوي على خصائصه ودوال للوصول الى البيانات عند الحاجة 
 */
class Announcement {

    private $announcementId;
    private $title;
    private $content;
    private $date;
    private $imagePath;

 
    public function __construct($title, $content, $imagePath = null) {// عند إنشاء إعلان جديد، يتم تمرير العنوان والمحتوى ومسار الصورة (اختياري)
        $this->title = $title;
        $this->content = $content;
        $this->imagePath = $imagePath;
        $this->date = date ("Y-m-d H:i:s"); // تعيين التاريخ الحالي
    }

    // Getters للوصول للبيانات من الكلاسات الأخرى
    public function getTitle() { return $this->title; }
    public function getContent() { return $this->content; }
    public function getImagePath() { return $this->imagePath; }
    public function getDate() { return $this->date; }
    }
?>
