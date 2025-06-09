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
    r.student_id, 
    s.id AS roll, 
    s.Name AS student_name, 
    r.ct_01, 
    r.ct_02, 
    r.Mid_trem AS mid, 
    r.assignment,
    r.attendance,
    r.performance,
    r.Final_part_A, 
    r.Final_part_B,
    (r.ct_01 + r.ct_02 + r.Mid_trem + r.assignment + r.attendance + r.performance + r.Final_part_A + r.Final_part_B) AS total
  FROM result r
  JOIN students s ON r.student_id = s.id
  WHERE r.teacher_id = ? AND r.course_id = ?
");
    $stmt->execute([$teacher_id, $course_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
      echo "<table border='1' style='width:100%; border-collapse:collapse;'>
      <tr>
        <th>Roll</th>
        <th>Name</th>
        <th>CT 01</th>
        <th>CT 02</th>
        <th>Mid</th>
        <th>Assignment</th>
        <th>Attendance</th>
        <th>Performance</th>
        <th>Final A</th>
        <th>Final B</th>
        <th>Total</th>
      </tr>";
      foreach ($results as $row) {
        echo "<tr>
                <td>{$row['roll']}</td>
                <td>{$row['student_name']}</td>
                <td>{$row['ct_01']}</td>
                <td>{$row['ct_02']}</td>
                <td>{$row['mid']}</td>
                <td>{$row['assignment']}</td>
                <td>{$row['attendance']}</td>
                <td>{$row['performance']}</td>
                <td>{$row['Final_part_A']}</td>
                <td>{$row['Final_part_B']}</td>
                <td>{$row['total']}</td>
              </tr>";
      }
      echo "</table>";
    } else {
      echo "<p>No result records found.</p>";
    }
  } catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
  }
}
