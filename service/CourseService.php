<?php

class CourseService {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ➕ CREATE
    public function createCourse($data): bool {

        $stmt = $this->pdo->prepare("
            INSERT INTO courses 
            (code, title, description, instructor, credits, capacity, schedule, category)
            VALUES (:code,:title,:description,:instructor,:credits,:capacity,:schedule,:category)
        ");

        return $stmt->execute($data);
    }

    // 📚 READ ALL
    public function getAllCourses(): array {
        return $this->pdo
            ->query("SELECT * FROM courses ORDER BY id DESC")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✏️ UPDATE
    public function updateCourse($data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE courses 
            SET code=:code, title=:title, description=:description,
                instructor=:instructor, credits=:credits,
                capacity=:capacity, schedule=:schedule,
                category=:category
            WHERE id=:id
        ");

        return $stmt->execute($data);
    }

    // ❌ DELETE
    public function deleteCourse(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM courses WHERE id=?");
        return $stmt->execute([$id]);
    }

    // 🔍 GET ONE
    public function getCourseById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM courses WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}