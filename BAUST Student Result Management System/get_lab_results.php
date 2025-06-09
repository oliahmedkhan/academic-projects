<?php
if (isset($_GET['teacher_id'], $_GET['course_id'], $_GET['section_id'])) {
  $teacher_id = $_GET['teacher_id'];
  $course_id = $_GET['course_id'];
  $section_id = $_GET['section_id'];

  $dsn = "mysql:host=localhost;dbname=baust_student_result_management_system";
  $username = "root";
  $password = "";

  try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("
      SELECT 
        s.id AS roll,
        s.Name AS student_name, 
        lr.attendance, 
        lr.report, 
        lr.mid_trem AS mid, 
        lr.Final, 
        lr.quiz, 
        lr.viva, 
        lr.parformance AS performance,
        (lr.attendance + lr.report + lr.mid_trem + lr.Final + lr.quiz + lr.viva + lr.parformance) AS total
      FROM lab_result lr
      JOIN students s ON lr.student_id = s.id
      JOIN assign a ON lr.course_id = a.course_id AND s.id = lr.student_id
      WHERE a.teacher_id = ? AND a.course_id = ? AND a.section_id = ?
      ORDER BY s.id
    ");
    $stmt->execute([$teacher_id, $course_id, $section_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
      echo "<table border='1' style='width:100%; border-collapse:collapse;'>
        <tr>
          <th>Roll</th>
          <th>Name</th>
          <th>Attendance</th>
          <th>Report</th>
          <th>Mid</th>
          <th>Final</th>
          <th>Quiz</th>
          <th>Viva</th>
          <th>Performance</th>
          <th>Total</th>
        </tr>";
      foreach ($results as $row) {
        echo "<tr>
                <td>{$row['roll']}</td>
                <td>{$row['student_name']}</td>
                <td>{$row['attendance']}</td>
                <td>{$row['report']}</td>
                <td>{$row['mid']}</td>
                <td>{$row['Final']}</td>
                <td>{$row['quiz']}</td>
                <td>{$row['viva']}</td>
                <td>{$row['performance']}</td>
                <td>{$row['total']}</td>
              </tr>";
      }
      echo "</table>";
    } else {
      echo "<p>No lab result records found.</p>";
    }
  } catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
  }
} else {
  echo "<p>Missing required parameters.</p>";
}
