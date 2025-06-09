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
      SELECT lr.id, lr.student_id, s.Name AS student_name, s.id AS roll,
             lr.attendance, lr.report, lr.mid_trem, lr.Final,
             lr.quiz, lr.viva, lr.parformance
      FROM lab_result lr
      JOIN students s ON lr.student_id = s.id
      JOIN assign a ON a.course_id = lr.course_id AND a.teacher_id = ? AND a.section_id = ?
      WHERE lr.course_id = ?
    ");
    $stmt->execute([$teacher_id, $section_id, $course_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
      echo "<h3>Update Lab Results</h3>
            <form method='post' action='update_lab_result_handler.php'>
              <input type='hidden' name='teacher_id' value='$teacher_id'>
              <input type='hidden' name='course_id' value='$course_id'>
              <input type='hidden' name='section_id' value='$section_id'>
              <table border='1' style='width:100%; border-collapse:collapse;'>
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
        $total = $row['attendance'] + $row['report'] + $row['mid_trem'] + $row['Final'] +
          $row['quiz'] + $row['viva'] + $row['parformance'];

        echo "<tr>
                <td>{$row['roll']}</td>
                <td>{$row['student_name']}</td>
                <td><input type='number' name='attendance[{$row['id']}]' value='{$row['attendance']}' required></td>
                <td><input type='number' name='report[{$row['id']}]' value='{$row['report']}' required></td>
                <td><input type='number' name='mid[{$row['id']}]' value='{$row['mid_trem']}' required></td>
                <td><input type='number' name='final[{$row['id']}]' value='{$row['Final']}' required></td>
                <td><input type='number' name='quiz[{$row['id']}]' value='{$row['quiz']}' required></td>
                <td><input type='number' name='viva[{$row['id']}]' value='{$row['viva']}' required></td>
                <td><input type='number' name='parformance[{$row['id']}]' value='{$row['parformance']}' required></td>
                <td><input type='number' value='$total' readonly></td>
              </tr>";
      }

      echo "</table>
            <br>
            <button type='submit' style='
                            margin-left: 6px;
                            padding: 6px 12px;
                            background-color: #10b981;
                            color: #fff;
                            border-radius: 6px;
                            font-size: 12px;
                            font-weight: 500;
                            border: none;
                            cursor: pointer;
                          '>Save Changes</button>
            </form>";
    } else {
      echo "<p>No lab result records found to update.</p>";
    }
  } catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
  }
} else {
  echo "<p>Missing required parameters.</p>";
}
