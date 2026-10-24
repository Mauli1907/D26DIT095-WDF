<?php
/**
 * Practical 11 Intermediate Extension: Export Student List to CSV
 * StudentHub Portal - CHARUSAT FTE
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();

$students = db_fetch_all("SELECT id, student_id_number, full_name, email, mobile, course, year_of_study, gender, status, created_at FROM students WHERE status != 'deleted' ORDER BY id ASC");

// Set download headers
$filename = "studenthub_students_" . date('Y-m-d_His') . ".csv";
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, ['ID', 'Student ID', 'Full Name', 'Email', 'Mobile', 'Course', 'Year', 'Gender', 'Status', 'Registered Date']);

// Data rows
foreach ($students as $stu) {
    fputcsv($output, [
        $stu['id'],
        $stu['student_id_number'],
        $stu['full_name'],
        $stu['email'],
        $stu['mobile'],
        $stu['course'],
        $stu['year_of_study'],
        $stu['gender'],
        $stu['status'],
        $stu['created_at']
    ]);
}

fclose($output);
exit;
