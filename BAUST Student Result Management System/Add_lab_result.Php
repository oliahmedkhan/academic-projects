<?php
session_start();

$dsn = "mysql:host=localhost;dbname=baust_student_result_management_system";
$username = "root";
$password = "";

if (!isset($_SESSION['teacher_id'])) {
  header("Location: Teacher_home.php");
  exit;
}

$teacher_id = $_SESSION['teacher_id'];
$selected_course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';

try {
  $conn = new PDO($dsn, $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $report = $_POST['report'];
    $mid_term = $_POST['mid_term'];
    $Final = $_POST['Final'];
    $quiz = $_POST['quiz'];
    $viva = $_POST['viva'];
    $attendance = $_POST['attendance'];
    $performance = $_POST['performance'];

    if ($action === 'insert') {
      $check = $conn->prepare("SELECT COUNT(*) FROM lab_result WHERE student_id = ? AND course_id = ?");
      $check->execute([$student_id, $course_id]);
      if ($check->fetchColumn() > 0) {
        echo "<script>alert('⚠️ Lab result already exists for this student and course. Use update instead.');</script>";
      } else {
        $stmt = $conn->prepare("
          INSERT INTO lab_result (
             attendance, report, mid_trem, Final, quiz, viva, parformance, student_id, course_id
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
          $attendance,
          $report,
          $mid_term,
          $Final,
          $quiz,
          $viva,
          $performance,
          $student_id,
          $course_id
        ]);
        echo "<script>alert('✅ Lab result added successfully!');</script>";
      }
    }
  }
} catch (PDOException $e) {
  echo "<script>alert('❌ Database error: " . $e->getMessage() . "');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BAUST Lab Results</title>
  <link rel="stylesheet" href="./Add_lab_result.css?v=<?php echo time(); ?>" />
  <style>

  </style>
</head>

<body>
  <header class="header_color">
    <div class="navbar">
      <div class="navbar-start">
        <img class="logo_img" src="./img/Baust_Logo.png" alt="BAUST Logo" />
      </div>
      <div class="navbar-center">
        <a class="logo">Add Lab Results</a>
      </div>
      <div class="navbar-end">
        <form method="get" action="Teacher_home.php" style="display:inline;">
          <button class="btn-circle" title="Go Home" type="submit">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18m-6-6l6 6-6 6" />
            </svg>
          </button>
        </form>
      </div>
    </div>
  </header>
  <main>
    <div>
      <form method="POST" action="">
        <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($teacher_id) ?>" />

        <div class="form-group">
          <label for="course_id">Course No.</label>
          <select id="course_id" name="course_id" required>
            <option value="">-- Select Course --</option>
            <?php
            $stmt = $conn->query("SELECT ID, no, title FROM courses WHERE title LIKE '%Sessional'");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              $selected = ($row['ID'] == $selected_course_id) ? "selected" : "";
              echo "<option value='{$row['ID']}' $selected>{$row['no']}</option>";
            }
            ?>
          </select>
        </div>

        <?php
        $fields = [
          "student_id" => "Student ID",
          "attendance" => "Attendance",
          "report" => "Report writing/Assignment/Programming",
          "mid_term" => "Mid Term",
          "Final" => "Final",
          "quiz" => "Quiz",
          "viva" => "Viva-Voce/Presentation",
          "performance" => "Performance"
        ];

        foreach ($fields as $name => $label) {
          echo <<<HTML
          <div class="form-group">
            <label for="$name">$label</label>
            <input id="$name" name="$name" type="number" placeholder="$label" required oninput="validateInteger(this, '$name')" />
            <div class="error-message" id="{$name}_error">Please enter a valid integer.</div>
          </div>
          HTML;
        }
        ?>

        <div class="form-group full-width">
          <button class="submit-btn" name="action" value="insert" type="submit">Insert</button>
        </div>
      </form>
    </div>
  </main>

  <script>
    function validateInteger(input, id) {
      const value = input.value;
      const error = document.getElementById(id + '_error');
      if (!/^\d+$/.test(value)) {
        error.style.display = 'block';
      } else {
        error.style.display = 'none';
      }
    }
  </script>
</body>

</html>