<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . "/../models/Announcement.php";
class AnnouncementTest extends TestCase// إنشاء كلاس الاختبار
{
    // Constructor + Getters Tests
    public function testConstructorWithDefaultValues() // اختبار إنشاء كائن بدون تمرير أي قيم
    {
        $ann = new Announcement(); // إنشاء كائن جديد
        $this->assertNull($ann->getTitle());// التحقق من أن العنوان فارغ
        $this->assertNull($ann->getContent());// التحقق من أن المحتوى فارغ
        $this->assertNull($ann->getImagePath());// التحقق من أن مسار الصورة فارغ
        $this->assertNotNull($ann->getDate());// التحقق من وجود تاريخ تلقائي
    }
    public function testGetTitle() // اختبار استرجاع عنوان الإعلان
    {
        $ann = new Announcement("News", "Content", "img.jpg");// إنشاء إعلان ببيانات تجريبي   
        $this->assertEquals("News", $ann->getTitle());// التحقق من صحة العنوان
    }
    public function testGetContent()// اختبار استرجاع محتوى الإعلان
    {
        $ann = new Announcement("News", "Hello", "img.jpg");// إنشاء إعلان ببيانات تجريبية       
        $this->assertEquals("Hello", $ann->getContent());// التحقق من صحة المحتوى
    }
    public function testGetImagePath() // اختبار استرجاع مسار الصورة
    {   
        $ann = new Announcement("News", "Hello", "img.jpg");// إنشاء إعلان ببيانات تجريبية
       
        $this->assertEquals("img.jpg", $ann->getImagePath()); // التحقق من صحة مسار الصورة
    }
   
    public function testDateNotNull() // اختبار وجود تاريخ للإعلان
    {
        
        $ann = new Announcement("News", "Hello", "img.jpg");// إنشاء إعلان جديد
    
        $this->assertNotNull($ann->getDate());// التأكد من أن التاريخ ليس فارغاً
    }
    public function testSetAndGetAnnouncementId() // اختبار Setter و Getter الخاص بالرقم التعريفي
    {
        $ann = new Announcement();
        // تعيين رقم للإعلان
        $ann->setAnnouncementId(50);
       // التحقق من صحة الرقم
        $this->assertEquals(50, $ann->getAnnouncementId());
    }
    // اختبار جلب إعلان بواسطة الرقم التعريفي
    public function testGetById()
    {
        // إنشاء Statement وهمي
        $stmt = $this->createMock(PDOStatement::class);
        // محاكاة نجاح التنفيذ
        $stmt->method('execute')->willReturn(true);
        // محاكاة البيانات المرجعة من قاعدة البيانات
        $stmt->method('fetch')->willReturn([
            "announcementId" => 1,
            "title" => "Test Title",
            "content" => "Hello"
        ]);
        // إنشاء اتصال قاعدة بيانات وهمي
        $db = $this->createMock(PDO::class);
        // عند استدعاء prepare يتم إرجاع Statement الوهمي
        $db->method('prepare')->willReturn($stmt);
        // استدعاء الدالة المراد اختبارها
        $result = Announcement::getById($db, 1);
        // التأكد من أن النتيجة عبارة عن مصفوفة
        $this->assertIsArray($result);
        // التأكد من صحة العنوان
        $this->assertEquals("Test Title", $result["title"]);
    }
    // اختبار جلب جميع الإعلانات
    public function testGetAllAnnouncements()
    {
        // إنشاء Statement وهمي
        $stmt = $this->createMock(PDOStatement::class);
        // محاكاة نجاح التنفيذ
        $stmt->method('execute')->willReturn(true);
        // محاكاة وجود إعلانين
        $stmt->method('fetchAll')->willReturn([
            ["title" => "A1"],
            ["title" => "A2"]
        ]);
        // إنشاء قاعدة بيانات وهمية
        $db = $this->createMock(PDO::class);
        // إرجاع Statement الوهمي
        $db->method('prepare')->willReturn($stmt);
        // استدعاء الدالة
        $result = Announcement::getAllAnnouncements($db);
        // التحقق من عدد النتائج
        $this->assertCount(2, $result);
    }
    // اختبار البحث عن إعلان
    public function testSearchAnnouncements()
    {
        // إنشاء Statement وهمي
        $stmt = $this->createMock(PDOStatement::class);
        // محاكاة نجاح التنفيذ
        $stmt->method('execute')->willReturn(true);
        // محاكاة نتيجة بحث واحدة
        $stmt->method('fetchAll')->willReturn([
            (object)[
                "title" => "Libya News",
                "content" => "Hello"
            ]
        ]);
        // إنشاء قاعدة بيانات وهمية
        $db = $this->createMock(PDO::class);
        // إرجاع Statement الوهمي
        $db->method('prepare')->willReturn($stmt);
        // تنفيذ البحث
        $result = Announcement::searchAnnouncements($db, "Libya");
        // التأكد من وجود نتيجة واحدة
        $this->assertCount(1, $result);
    }
    // اختبار جلب أحدث الإعلانات
    public function testGetLatestAnnouncements()
    {
        // إنشاء Statement وهمي
        $stmt = $this->createMock(PDOStatement::class);
        // محاكاة نجاح التنفيذ
        $stmt->method('execute')->willReturn(true);
        // التحقق من تمرير قيمة limit
        $stmt->expects($this->once())
             ->method('bindValue')
             ->with(':limit', 5, PDO::PARAM_INT);
        // محاكاة نتيجتين
        $stmt->method('fetchAll')->willReturn([
            (object)["title" => "News 1"],
            (object)["title" => "News 2"]
        ]);
        // إنشاء قاعدة بيانات وهمية
        $db = $this->createMock(PDO::class);
        // إرجاع Statement الوهمي
        $db->method('prepare')->willReturn($stmt);
        // إنشاء كائن من الصنف
        $ann = new Announcement();
        // استدعاء الدالة
        $result = $ann->getLatestAnnouncements($db, 5);
        // التحقق من عدد النتائج
        $this->assertCount(2, $result);
    }
    // اختبار جلب الإعلانات المؤرشفة
    public function testGetArchiveAnnouncements()
    {
        $stmt = $this->createMock(PDOStatement::class);// إنشاء Statement وهمي
        // محاكاة نجاح التنفيذ
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn([// محاكاة نتيجة واحدة
            (object)["title" => "Old News"]
        ]);
        $db = $this->createMock(PDO::class);// إنشاء قاعدة بيانات وهمية
        $db->method('prepare')->willReturn($stmt);// إرجاع Statement الوهمي
        $ann = new Announcement();// إنشاء كائن من الصنف
        $result = $ann->getArchiveAnnouncements($db);// استدعاء الدالة
        $this->assertCount(1, $result); // التحقق من عدد النتائج
    }
    // اختبار البحث مع أكثر من نتيجة
    public function testSearchAnnouncementsMultipleResults()
    {
        // إنشاء Statement وهمي
        $stmt = $this->createMock(PDOStatement::class);
        // محاكاة نجاح التنفيذ
        $stmt->method('execute')->willReturn(true);
        // محاكاة نتيجتين للبحث
        $stmt->method('fetchAll')->willReturn([
            (object)["title" => "Libya News"],
            (object)["title" => "Libya Sport"]
        ]);
        $db = $this->createMock(PDO::class);        // إنشاء قاعدة بيانات وهمية
        $db->method('prepare')->willReturn($stmt);    // إرجاع Statement الوهمي
        $result = Announcement::searchAnnouncements($db, "Libya");  // تنفيذ البحث
        $this->assertCount(2, $result);   // التحقق من عدد النتائج
            }
    // Failure Cases (Try-Catch)
    // اختبار فشل getById
    public function testGetByIdFailure()
    {
        $db = $this->createMock(PDO::class); // إنشاء قاعدة بيانات وهمية
        // إجبار prepare على رمي استثناء
        $db->method('prepare')->willThrowException(new PDOException("DB error"));
        $result = Announcement::getById($db, 1);// استدعاء الدالة
        $this->assertNull($result); // التحقق من القيمة المرجعة
    }
    // اختبار فشل جلب جميع الإعلانات
    public function testGetAllAnnouncementsFailure()
    {
        $db = $this->createMock(PDO::class);
        $db->method('prepare')->willThrowException(new PDOException("DB error"));
        $result = Announcement::getAllAnnouncements($db);
        $this->assertEquals([], $result);
    }
    // اختبار فشل جلب أحدث الإعلانات
    public function testGetLatestAnnouncementsFailure()
    {
        $db = $this->createMock(PDO::class);
        $db->method('prepare')->willThrowException(new PDOException("DB error"));
        $ann = new Announcement();
        $result = $ann->getLatestAnnouncements($db, 5);
        $this->assertEquals([], $result);
    }
    // اختبار فشل البحث
    public function testSearchAnnouncementsFailure()
    {
        $db = $this->createMock(PDO::class);
        $db->method('prepare')
           ->willThrowException(new PDOException("DB error"));
        $result = Announcement::searchAnnouncements($db, "test");
       $this->assertEquals([], $result);
    }
    // اختبار فشل جلب الأرشيف
    public function testGetArchiveAnnouncementsFailure()
    {
        $db = $this->createMock(PDO::class);
        $db->method('prepare')->willThrowException(new PDOException("DB error"));
        $ann = new Announcement();
        $result = $ann->getArchiveAnnouncements($db);
        $this->assertEquals([], $result);
    }
}
