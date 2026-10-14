<?php

declare(strict_types=1);

namespace EduSphere\Services;

use EduSphere\Core\Database;

class GradingService
{
    public static function calculateTotal(float $ca1, float $ca2, float $exam): float
    {
        return round($ca1 + $ca2 + $exam, 2);
    }

    public static function determineGrade(int $schoolId, float $totalScore): array
    {
        $scales = Database::query(
            "SELECT * FROM grading_scales WHERE school_id = ? ORDER BY min_score DESC",
            [$schoolId]
        );

        if (empty($scales)) {
            // Default standard grading fallback
            if ($totalScore >= 75) return ['grade' => 'A1', 'remark' => 'Excellent', 'point' => 4.0];
            if ($totalScore >= 70) return ['grade' => 'B2', 'remark' => 'Very Good', 'point' => 3.5];
            if ($totalScore >= 65) return ['grade' => 'B3', 'remark' => 'Good', 'point' => 3.0];
            if ($totalScore >= 60) return ['grade' => 'C4', 'remark' => 'Credit', 'point' => 2.5];
            if ($totalScore >= 55) return ['grade' => 'C5', 'remark' => 'Credit', 'point' => 2.0];
            if ($totalScore >= 50) return ['grade' => 'C6', 'remark' => 'Credit', 'point' => 1.5];
            if ($totalScore >= 45) return ['grade' => 'D7', 'remark' => 'Pass', 'point' => 1.0];
            if ($totalScore >= 40) return ['grade' => 'E8', 'remark' => 'Pass', 'point' => 0.5];
            return ['grade' => 'F9', 'remark' => 'Fail', 'point' => 0.0];
        }

        foreach ($scales as $scale) {
            if ($totalScore >= (float)$scale['min_score'] && $totalScore <= (float)$scale['max_score']) {
                return [
                    'grade' => $scale['grade_name'],
                    'remark' => $scale['remark'],
                    'point' => (float)$scale['grade_point'],
                ];
            }
        }

        // If below lowest, return lowest scale item
        $lowest = end($scales);
        return [
            'grade' => $lowest['grade_name'],
            'remark' => $lowest['remark'],
            'point' => (float)$lowest['grade_point'],
        ];
    }

    /**
     * Compute and update class rankings and term summary for all students in a class arm
     */
    public static function computeClassPositions(int $schoolId, int $sessionId, int $termId, int $classArmId): void
    {
        // Get all students enrolled in this arm
        $students = Database::query(
            "SELECT DISTINCT s.id FROM students s
             WHERE s.school_id = ? AND s.class_arm_id = ? AND s.status = 'enrolled'",
            [$schoolId, $classArmId]
        );

        if (empty($students)) {
            return;
        }

        $studentAverages = [];

        foreach ($students as $stu) {
            $stuId = (int)$stu['id'];
            $scores = Database::query(
                "SELECT total_score FROM exam_scores 
                 WHERE school_id = ? AND session_id = ? AND term_id = ? AND class_arm_id = ? AND student_id = ? AND status IN ('approved', 'published', 'locked')",
                [$schoolId, $sessionId, $termId, $classArmId, $stuId]
            );

            $subjectCount = count($scores);
            $sum = 0.0;
            foreach ($scores as $sc) {
                $sum += (float)$sc['total_score'];
            }

            $avg = $subjectCount > 0 ? round($sum / $subjectCount, 2) : 0.0;
            $studentAverages[$stuId] = [
                'total' => $sum,
                'avg' => $avg,
            ];
        }

        // Sort by average descending
        uasort($studentAverages, fn($a, $b) => $b['avg'] <=> $a['avg']);

        $rank = 1;
        $totalStudents = count($studentAverages);

        foreach ($studentAverages as $stuId => $data) {
            $exists = Database::fetch(
                "SELECT id FROM student_term_summaries WHERE school_id = ? AND session_id = ? AND term_id = ? AND student_id = ?",
                [$schoolId, $sessionId, $termId, $stuId]
            );

            if ($exists) {
                Database::execute(
                    "UPDATE student_term_summaries 
                     SET total_score = ?, average_score = ?, position = ?, total_students = ?, class_arm_id = ?, updated_at = NOW() 
                     WHERE id = ?",
                    [$data['total'], $data['avg'], $rank, $totalStudents, $classArmId, $exists['id']]
                );
            } else {
                Database::execute(
                    "INSERT INTO student_term_summaries 
                     (school_id, session_id, term_id, student_id, class_arm_id, total_score, average_score, position, total_students, promotion_status, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())",
                    [$schoolId, $sessionId, $termId, $stuId, $classArmId, $data['total'], $data['avg'], $rank, $totalStudents]
                );
            }
            $rank++;
        }
    }
}
