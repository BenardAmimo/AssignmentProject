<?php

class EnrollmentService {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // 📌 ENROLL STUDENT
    public function enroll(int $studentId, int $courseId): array {

        // check duplicate
        $stmt = $this->pdo->prepare("
            SELECT id FROM registrations 
            WHERE student_id=? AND course_id=?
        ");
        $stmt->execute([$studentId, $courseId]);

        if ($stmt->fetch()) {
            return ["success" => false, "message" => "Already enrolled"];
        }

        // limit student to 2 courses
        $countStmt = $this->pdo->prepare("
            SELECT COUNT(*) AS total FROM registrations
            WHERE student_id=?
        ");
        $countStmt->execute([$studentId]);
        $count = (int) $countStmt->fetchColumn();

        if ($count >= 2) {
            return ["success" => false, "message" => "Course limit reached. You may enroll in up to 2 courses."];
        }

        // check capacity
        $course = $this->pdo->prepare("
            SELECT capacity, enrolled 
            FROM courses WHERE id=?
        ");
        $course->execute([$courseId]);
        $c = $course->fetch(PDO::FETCH_ASSOC);

        if (!$c) {
            return ["success" => false, "message" => "Course not found"];
        }

        if ($c['enrolled'] >= $c['capacity']) {
            return ["success" => false, "message" => "Course is full"];
        }

        // insert registration
        $this->pdo->prepare("
            INSERT INTO registrations (student_id, course_id)
            VALUES (?, ?)
        ")->execute([$studentId, $courseId]);

        // update counter
        $this->pdo->prepare("
            UPDATE courses SET enrolled = enrolled + 1 WHERE id=?
        ")->execute([$courseId]);

        return ["success" => true, "message" => "Enrolled successfully"];
    }

    // 📌 DROP COURSE
    public function drop(int $studentId, int $courseId): array {

        $check = $this->pdo->prepare("
            SELECT id FROM registrations 
            WHERE student_id=? AND course_id=?
        ");
        $check->execute([$studentId, $courseId]);

        if (!$check->fetch()) {
            return ["success" => false, "message" => "Not enrolled"];
        }

        $this->pdo->prepare("
            DELETE FROM registrations 
            WHERE student_id=? AND course_id=?
        ")->execute([$studentId, $courseId]);

        $this->pdo->prepare("
            UPDATE courses 
            SET enrolled = GREATEST(enrolled - 1, 0)
            WHERE id=?
        ")->execute([$courseId]);

        return ["success" => true, "message" => "Dropped successfully"];
    }
}