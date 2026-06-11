<?php

class Student
{
    private $conn;

    private $studentID;
    private $studentName;
    private $classID;
    private $parentID;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getStudentsByParent($parentID)
    {
        $sql = "
        SELECT
            students.studentID,
            students.studentName,
            students.classID,
            classes.className
        FROM students
        JOIN classes
            ON students.classID = classes.classID
        WHERE parentID = ?
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([$parentID]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}