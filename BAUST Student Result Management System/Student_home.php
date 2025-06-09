<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
  session_unset();
  session_destroy();
  header("Location: Student.php");
  exit;
}

if (!isset($_SESSION['student_id']) || !isset($_SESSION['student_name'])) {
  echo "Unauthorized access.";
  exit;
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

$dsn = "mysql:host=localhost;dbname=baust_student_result_management_system";
$username = "root";
$password = "";

$level_term = '';
$results = [];
$lab_results = [];
$teachers = [];
$level_term_cgpa = 0;
$overall_cgpa = 0;

function getCurrentEmail($student_id, $dsn, $username, $password)
{
  try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("SELECT E_mail FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['E_mail'] : "Not found";
  } catch (PDOException $e) {
    return "Error fetching email";
  }
}

function calculateGP($total_marks)
{
  if ($total_marks >= 80) return 4.00;
  if ($total_marks >= 75) return 3.75;
  if ($total_marks >= 70) return 3.50;
  if ($total_marks >= 65) return 3.25;
  if ($total_marks >= 60) return 3.00;
  if ($total_marks >= 55) return 2.75;
  if ($total_marks >= 50) return 2.50;
  if ($total_marks >= 45) return 2.25;
  if ($total_marks >= 40) return 2.00;
  return 0.00;
}

function calculateCGPA($conn, $student_id, $level_term_filter = null)
{
  $query_addition = $level_term_filter ? "AND s.Level_Term = ?" : "";
  $params = [$student_id];
  if ($level_term_filter) $params[] = $level_term_filter;

  $totalPoints = 0;
  $totalCredits = 0;

  $stmt = $conn->prepare("SELECT r.*, c.credits FROM result r JOIN courses c ON r.course_id = c.ID JOIN students s ON r.student_id = s.id WHERE r.student_id = ? $query_addition");
  $stmt->execute($params);
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($results as $row) {
    $ct = [$row['ct_01'], $row['ct_02'], $row['ct_03']];
    rsort($ct);
    $ct_45 = (($ct[0] + $ct[1]) / 40) * 45;
    $assignment = ($row['assignment'] / 20) * 10;
    $att_per = (($row['attendance'] + $row['performance']) / 20) * 10;
    $mid = $row['Mid_trem'];
    $total_120 = $ct_45 + $mid + $assignment + $att_per;
    $final_total = $total_120 + $row['Final_part_A'] + $row['Final_part_B'];

    $gp = calculateGP($final_total);
    $credit = $row['credits'];
    $totalPoints += $gp * $credit;
    $totalCredits += $credit;
  }

  $lab_stmt = $conn->prepare("SELECT lr.*, c.credits FROM lab_result lr JOIN courses c ON lr.course_id = c.ID JOIN students s ON lr.student_id = s.id WHERE lr.student_id = ? $query_addition");
  $lab_stmt->execute($params);
  $lab_results = $lab_stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($lab_results as $row) {
    $total = $row['attendance'] + $row['report'] + $row['quiz'] + $row['viva'] + $row['mid_trem'] + $row['Final'] + $row['parformance'];
    $gp = calculateGP($total);
    $credit = $row['credits'];
    $totalPoints += $gp * $credit;
    $totalCredits += $credit;
  }

  return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['level_term'])) {
  $level_term = $_POST['level_term'];
  try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $term_mapping = [
      "Level-1 Term-I" => "1 I",
      "Level-1 Term-II" => "1 II",
      "Level-2 Term-I" => "2 I",
      "Level-2 Term-II" => "2 II",
      "Level-3 Term-I" => "3 I",
      "Level-3 Term-II" => "3 II",
      "Level-4 Term-I" => "4 I",
      "Level-4 Term-II" => "4 II",
    ];

    $mapped_level_term = $term_mapping[$level_term] ?? '';

    $stmt = $conn->prepare("SELECT r.*, c.no AS course_no, c.title AS course_title FROM result r JOIN courses c ON r.course_id = c.ID JOIN students s ON r.student_id = s.id WHERE r.student_id = ? AND s.Level_Term = ?");
    $stmt->execute([$student_id, $mapped_level_term]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lab_stmt = $conn->prepare("SELECT lr.*, c.no AS course_no FROM lab_result lr JOIN courses c ON lr.course_id = c.ID JOIN students s ON lr.student_id = s.id WHERE lr.student_id = ? AND s.Level_Term = ?");
    $lab_stmt->execute([$student_id, $mapped_level_term]);
    $lab_results = $lab_stmt->fetchAll(PDO::FETCH_ASSOC);

    $teacher_stmt = $conn->prepare("SELECT DISTINCT t.name, t.e_mail, c.no AS course_no FROM assign a JOIN teacher t ON a.teacher_id = t.ID JOIN courses c ON a.course_id = c.ID WHERE a.level_term = ? AND a.course_id IN (SELECT course_id FROM result WHERE student_id = ?)");
    $teacher_stmt->execute([$mapped_level_term, $student_id]);
    $teachers = $teacher_stmt->fetchAll(PDO::FETCH_ASSOC);

    $level_term_cgpa = calculateCGPA($conn, $student_id, $mapped_level_term);
    $overall_cgpa = calculateCGPA($conn, $student_id);
  } catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
  $new_email = $_POST['new_email'];
  try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("UPDATE students SET E_mail = ? WHERE id = ?");
    $stmt->execute([$new_email, $student_id]);

    echo "<script>alert('Email updated successfully.');</script>";
  } catch (PDOException $e) {
    echo "<script>alert('Failed to update email: " . $e->getMessage() . "');</script>";
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>BAUST Results</title>
  <link rel="stylesheet" href="Student_home.css?v=<?php echo time(); ?>" />
</head>

<body>
  <header class="header_color">
    <div class="navbar">
      <div class="navbar-start">
        <div>
          <img class="logo_img" src="./img/Baust_Logo.png" alt="" />
        </div>
      </div>
      <div class="student-info">
        <p class="name">Name: <?= htmlspecialchars($student_name) ?></p>
        <p class="id">ID: <?= htmlspecialchars($student_id) ?></p>
      </div>
      <div class="navbar-center">
        <a class="logo">Student</a>
      </div>
      <div class="navbar-end">
        <button class="btn-circle" title="Contact" onclick="document.getElementById('contactModal').style.display='block'">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M7 8h10M7 12h4m1 8l-2-2H6a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2h-3l-2 2z" />
          </svg>
        </button>
        <button class="btn-circle" title="Update Email" onclick="document.getElementById('emailModal').style.display='block'">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M21 8v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8m18-2H3m13 9l2 2 4-4" />
          </svg>
        </button>
        <form method="post" style="display:inline;">
          <button class="btn-circle" title="Logout" name="logout" type="submit">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1m0-10v1" />
            </svg>
          </button>
        </form>
      </div>
    </div>
  </header>
  <div class="level-term-form" style="text-align:center; margin: 20px;">
    <form method="POST">
      <label for="level_term"><strong>Select Level-Term:</strong></label>
      <select name="level_term" id="level_term" required>
        <option value="">--Select Level-Term--</option>
        <?php
        $terms = [
          "Level-1 Term-I",
          "Level-1 Term-II",
          "Level-2 Term-I",
          "Level-2 Term-II",
          "Level-3 Term-I",
          "Level-3 Term-II",
          "Level-4 Term-I",
          "Level-4 Term-II"
        ];
        foreach ($terms as $term) {
          $selected = ($level_term === $term) ? "selected" : "";
          echo "<option value=\"$term\" $selected>$term</option>";
        }
        ?>
      </select>
      <button class="submit-btn" name="action" value="" type="submit">View Results</button>
    </form>
  </div>
  <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($level_term)): ?>
    <main>
      <div class="table-container">
        <table>
          <thead>
            <p class="t_title"><u>Results</u></p>
            <tr>
              <th>Course No.</th>
              <th>CT1</th>
              <th>CT2</th>
              <th>CT3</th>
              <th>Assignment</th>
              <th>Mid</th>
              <th>Attendance</th>
              <th>Performance</th>
              <th>Final Part A</th>
              <th>Final Part B</th>
              <th>120 Marks</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $row):
              $ct_scores = [$row['ct_01'], $row['ct_02'], $row['ct_03']];
              rsort($ct_scores);
              $best_two_ct_sum = $ct_scores[0] + $ct_scores[1];
              $ct_45 = ($best_two_ct_sum / 40) * 45;

              $mid = $row['Mid_trem'];

              $assignment_10 = ($row['assignment'] / 20) * 10;

              $attendance = $row['attendance'];
              $performance = $row['performance'];
              $att_per_10 = (($attendance + $performance) / 20) * 10;

              $total_120 = $ct_45 + $mid + $assignment_10 + $att_per_10;

              $final_total = $total_120 + $row['Final_part_A'] + $row['Final_part_B'];
            ?>
              <tr>
                <td><?= htmlspecialchars($row['course_no']) ?></td>
                <td><?= $row['ct_01'] ?></td>
                <td><?= $row['ct_02'] ?></td>
                <td><?= $row['ct_03'] ?></td>
                <td><?= $row['assignment'] ?></td>
                <td><?= $mid ?></td>
                <td><?= $attendance ?></td>
                <td><?= $performance ?></td>
                <td><?= $row['Final_part_A'] ?></td>
                <td><?= $row['Final_part_B'] ?></td>
                <td><?= round($total_120, 2) ?></td>
                <td><?= round($final_total, 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
      </div>
      <div class="table-container">
        <table>
          <thead>
            <p class="t_title"><u>Lab Results</u></p>
            <tr>
              <th>Course No.</th>
              <th>Attendance</th>
              <th>Lab Report</th>
              <th>Quiz</th>
              <th>Viva</th>
              <th>Mid</th>
              <th>Final</th>
              <th>Parformance</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($lab_results)): ?>
              <?php foreach ($lab_results as $lab_result):
                $lab_total = $lab_result['attendance'] + $lab_result['report'] + $lab_result['quiz'] +
                  $lab_result['viva'] + $lab_result['mid_trem'] + $lab_result['Final'] + $lab_result['parformance'];
              ?>
                <tr>
                  <td><?= htmlspecialchars($lab_result['course_no']) ?></td>
                  <td><?= $lab_result['attendance'] ?></td>
                  <td><?= $lab_result['report'] ?></td>
                  <td><?= $lab_result['quiz'] ?></td>
                  <td><?= $lab_result['viva'] ?></td>
                  <td><?= $lab_result['mid_trem'] ?></td>
                  <td><?= $lab_result['Final'] ?></td>
                  <td><?= $lab_result['parformance'] ?></td>
                  <td><?= $lab_total ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9">No lab results found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div style="text-align:center; margin-top:20px;">
        <h3>GPA Summary</h3>
        <p><strong>CGPA for <?= htmlspecialchars($level_term) ?>:</strong> <?= $level_term_cgpa ?></p>
        <p><strong>Cumulative CGPA (All Terms):</strong> <?= $overall_cgpa ?></p>
      </div>
    </main>
  <?php endif; ?>
  <div id="contactModal" style="display:none; position:fixed; top:10%; left:50%; transform:translateX(-50%); background:white; padding:20px; border:1px solid #aaa; border-radius:10px; z-index:1000;">
    <h3>Course Teachers for <?= htmlspecialchars($level_term) ?></h3>
    <table border="1" style="width:100%; margin-top:10px;">
      <thead>
        <tr>
          <th>Course No</th>
          <th>Teacher Name</th>
          <th>Email</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($teachers)): ?>
          <?php foreach ($teachers as $teacher): ?>
            <tr>
              <td><?= htmlspecialchars($teacher['course_no']) ?></td>
              <td><?= htmlspecialchars($teacher['name']) ?></td>
              <td><?= htmlspecialchars($teacher['e_mail']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="3">No teacher information available.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <button class="submit-btn" onclick="document.getElementById('contactModal').style.display='none'" style="margin-top:10px;">Close</button>
  </div>
  <div id="emailModal" style="display:none; position:fixed; top:10%; left:50%; transform:translateX(-50%); background:white; padding:20px; border:1px solid #aaa; border-radius:10px; z-index:1000;">
    <h3>Update Your Email</h3>
    <form method="POST">
      <p>Current Email: <?= htmlspecialchars(getCurrentEmail($student_id, $dsn, $username, $password)) ?></p>
      <label for="new_email">New Email:</label>
      <input type="email" name="new_email" id="new_email" required />
      <button class="submit-btn" type="submit" name="update_email" style="margin-top:10px;">Update</button>
    </form>
    <button class="submit-btn" onclick="document.getElementById('emailModal').style.display='none'" style="margin-top:10px;">Close</button>
  </div>
</body>

</html>