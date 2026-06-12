<?php


class Schedule
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getScheduleByClass($classID)
    {
        $sql = "
        SELECT
            dayName,
            periodNumber,
            subjectName
        FROM schedules
        WHERE classID = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$classID]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getPeriods()
    {
        $sql = "
        SELECT
            periodNumber,
            startTime,
            endTime
        FROM periods
        ORDER BY periodNumber
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }


    /**
     * كلاس إدارة الجداول الدراسية المطور ليتوافق مع قاعدة بيانات TAWASUL الفعلية
     */

    /**
     * إضافة حصة/مادة جديدة للجدول الدراسي لصف معين
     */
    public function addSchedule($classID, $dayName, $periodNumber, $subjectName)
    {
        try {
            $sql = "INSERT INTO schedules (classID, dayName, periodNumber, subjectName) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $classID,
                $dayName,
                $periodNumber,
                $subjectName
            ]);
        } catch (PDOException $e) {
            error_log("خطأ في إضافة حصة للجدول: " . $e->getMessage());
            return false;
        }
    }
    public  function getTimes()
    {
        try {
            $sql = "SELECT * FROM  periods ORDER BY periodNumber ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب مواعيد الحصص: " . $e->getMessage());
            return [];
        }
    }
    /**
     * جلب الجدول الدراسي الأسبوعي الكامل لصف معين مرتدياً أسماء الأيام والحصص
     */
    public function getWeeklyScheduleByClass($classID)
    {
        try {
            // الاستعلام يقوم بترتيب الأيام برمجياً من الأحد إلى الخميس، ثم تصاعدياً برقم الحصة
            $sql = "SELECT * FROM schedules 
                    WHERE classID = ? 
                    ORDER BY FIELD(dayName, 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'), periodNumber ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([(int)$classID]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب جدول الصف: " . $e->getMessage());
            return [];
        }
    }

    /**
     * جلب كافة الحصص المسجلة في النظام (لشاشة الإدارة العامة)
     * يربط جدول الجداول بجدول الصفوف لعرض اسم الصف بدلاً من رقم الـ ID
     */
    public function getAllSchedulesWithClassName()
    {
        try {
            $sql = "SELECT s.*, c.className 
                    FROM schedules s 
                    LEFT JOIN classes c ON s.classID = c.classID 
                    ORDER BY s.classID, FIELD(s.dayName, 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'), s.periodNumber ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب كل الجداول: " . $e->getMessage());
            return [];
        }
    }

    /**
     * تحديث مادة حصة معينة
     */
    public function updateSchedule($id, $subjectName)
    {
        try {
            $sql = "UPDATE schedules SET subjectName = ? WHERE scheduleID = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$subjectName, (int)$id]);
        } catch (PDOException $e) {
            error_log("خطأ في تحديث الحصة: " . $e->getMessage());
            return false;
        }
    }

    /**
     * حذف حصة معينة من الجدول بواسطة الـ ID الخاص بها
     */
    public function deleteSchedule($id)
    {
        try {
            $sql = "DELETE FROM schedules WHERE scheduleID = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([(int)$id]);
        } catch (PDOException $e) {
            error_log("خطأ في حذف الحصة: " . $e->getMessage());
            return false;
        }
    }

    /**
     * جلب تفاصيل حصة معينة بواسطة المعرف (تستخدم عند الحاجة للتعديل أو التحقق)
     */
    public  function getById($id)
    {
        try {
            $sql = "SELECT * FROM schedules WHERE scheduleID = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([(int)$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب الحصة بواسطة ID: " . $e->getMessage());
            return null;
        }
    }

    public function getAllClassNames()
    {
        try {
            $sql = "SELECT classID, className FROM classes ORDER BY classID ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب أسماء الصفوف: " . $e->getMessage());
            return [];
        }
    }
}
