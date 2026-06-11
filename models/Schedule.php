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
}

?>