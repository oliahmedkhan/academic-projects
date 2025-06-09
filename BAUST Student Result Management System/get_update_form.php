<?php
if (isset($_GET['teacher_id'], $_GET['course_id'])) {
  $teacher_id = $_GET['teacher_id'];
  $course_id = $_GET['course_id'];

  $dsn = "mysql:host=localhost;dbname=baust_student_result_management_system";
  $username = "root";
  $password = "";

  try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("
    SELECT r.id, r.student_id, s.id AS roll, s.Name AS student_name,
           r.ct_01, r.ct_02, r.Mid_trem AS mid, r.assignment, r.attendance, r.performance,
           r.Final_part_A, r.Final_part_B
    FROM result r
    JOIN students s ON r.student_id = s.id
    WHERE r.teacher_id = ? AND r.course_id = ?
  ");
    $stmt->execute([$teacher_id, $course_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
      echo "<form method='post' action='update_result_handler.php'>
              <input type='hidden' name='teacher_id' value='$teacher_id'>
              <input type='hidden' name='course_id' value='$course_id'>
              <table border='1' style='width:100%; border-collapse:collapse;'>
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
        $total = $row['ct_01'] + $row['ct_02'] + $row['mid'] + $row['assignment'] +
          $row['attendance'] + $row['performance'] + $row['Final_part_A'] + $row['Final_part_B'];

        echo "<tr>
                <td>{$row['roll']}</td>
                <td>{$row['student_name']}</td>
                <td><input type='number' name='ct_01[{$row['id']}]' value='{$row['ct_01']}' required></td>
                <td><input type='number' name='ct_02[{$row['id']}]' value='{$row['ct_02']}' required></td>
                <td><input type='number' name='mid[{$row['id']}]' value='{$row['mid']}' required></td>
                <td><input type='number' name='assignment[{$row['id']}]' value='{$row['assignment']}' required></td>
                <td><input type='number' name='attendance[{$row['id']}]' value='{$row['attendance']}' required></td>
                <td><input type='number' name='performance[{$row['id']}]' value='{$row['performance']}' required></td>
                <td><input type='number' name='final_a[{$row['id']}]' value='{$row['Final_part_A']}' required></td>
                <td><input type='number' name='final_b[{$row['id']}]' value='{$row['Final_part_B']}' required></td>
                <td><input type='number' name='total[{$row['id']}]' value='{$total}' readonly></td>
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
      echo "<p>No records found to update.</p>";
    }
  } catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
  }
}
