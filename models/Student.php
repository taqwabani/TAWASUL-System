<?php

class Student {

    private $studentID;
    private $studentName;
    private $classID;
    private $parentID;

    public static function getStudentsByParent($conn,$parentID)
    {
        $sql = "
        SELECT
            students.studentID,
            students.studentName,
            classes.className
        FROM students
        JOIN classes
            ON students.classID = classes.classID
        WHERE parentID = ?
        ";

        $stmt = $conn->prepare($sql);

        $stmt->execute([$parentID]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}