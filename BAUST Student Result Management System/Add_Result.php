<?php
session_start();

$dsn = "mysql:host=localhost;dbname=baust_student_result_management_system";
$username = "root";
$password = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
  session_unset();
  session_destroy();
  header("Location: Teacher_home.php");
  exit;
}


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
    $teacher_id = $_POST['teacher_id'];
    $course_id = $_POST['course_id'];
    $ct_01 = $_POST['ct_01'] ?? null;
    $ct_02 = $_POST['ct_02'] ?? null;
    $ct_03 = $_POST['ct_03'] ?? null;
    $assignment = $_POST['assignment'] ?? null;
    $mid = $_POST['Mid_trem'] ?? null;
    $attendance = $_POST['attendance'] ?? null;
    $performance = $_POST['performance'] ?? null;
    $final_a = $_POST['final_part_A'] ?? null;
    $final_b = $_POST['final_part_B'] ?? null;

    $validateIds = function ($table, $id) use ($conn) {
      $stmt = $conn->prepare("SELECT COUNT(*) FROM `$table` WHERE id = ?");
      $stmt->execute([$id]);
      return $stmt->fetchColumn() > 0;
    };
    if (!$validateIds("students", $student_id)) {
      echo "<script>alert('Invalid Student ID');</script>";
      exit;
    }
    if (!$validateIds("teacher", $teacher_id)) {
      echo "<script>alert('Invalid Teacher ID');</script>";
      exit;
    }
    if (!$validateIds("courses", $course_id)) {
      echo "<script>alert('Invalid Course ID');</script>";
      exit;
    }
    $stmt = $conn->prepare("SELECT id FROM result WHERE student_id = ? AND course_id = ? AND teacher_id = ?");
    $stmt->execute([$student_id, $course_id, $teacher_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($action === 'add') {
      if ($existing) {
        echo "<script>alert('Result already exists! Use update instead.');</script>";
      } else {
        $stmt = $conn->prepare("
          INSERT INTO result (
            ct_01, ct_02, ct_03, assignment,
            Mid_trem, attendance, performance, Final_part_A, Final_part_B,
            course_id, student_id, teacher_id
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
          $ct_01,
          $ct_02,
          $ct_03,
          $assignment,
          $mid,
          $attendance,
          $performance,
          $final_a,
          $final_b,
          $course_id,
          $student_id,
          $teacher_id
        ]);
        echo "<script>alert('Result added successfully!');</script>";
      }
    } else {
      echo "<script>alert('Invalid action!');</script>";
    }
  }
} catch (PDOException $e) {
  echo "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BAUST Results</title>
  <link rel="stylesheet" href="Add_result.css?v=<?php echo time(); ?>" />
</head>

<body>
  <header class="header_color">
    <div class="navbar">
      <div class="navbar-start">
        <div>
          <img class="logo_img" src="./img/Baust_Logo.png" alt="" />
        </div>
      </div>
      <div class="navbar-center">
        <p style="text-align: center;">
          <a class="logo">Add Results</a>
        </p>
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
      <form id="resultForm" method="POST" action="">
        <input type="hidden" name="teacher_id" value="<?php echo htmlspecialchars($_SESSION['teacher_id']); ?>" />
        <input type="hidden" name="action" id="form_action" value="add" />
        <div class="form-group">
          <label for="course_id">Course No.</label>
          <select id="course_id" name="course_id" required>
            <option value="">-- Select Course --</option>
            <?php
            $stmt = $conn->query("SELECT ID, no FROM courses");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              $selected = ($row['ID'] == $selected_course_id) ? "selected" : "";
              echo "<option value='{$row['ID']}' $selected>{$row['no']}</option>";
            }
            ?>
          </select>
        </div>
        <form id="resultForm" method="POST" action="">
          <input type="hidden" name="teacher_id" value="<?php echo htmlspecialchars($_SESSION['teacher_id']); ?>" />
          <input type="hidden" name="action" id="form_action" value="add" />
          <div class="form-group">
            <label for="student_id">Student ID</label>
            <input id="student_id" name="student_id" type="text" placeholder="Student ID" required oninput="validateIntegerWithMax(this, 99999999999999999999999)" />
            <div class="error-message" id="student_id_error">Please enter a valid integer.</div>
          </div>

          <div class="form-group">
            <label>CT-1</label>
            <input name="ct_01" id="ct_01" type="text" placeholder="CT-1" oninput="validateIntegerWithMax(this, 20)" />
            <div class="error-message" id="ct_01_error">Please enter a valid integer.</div>
          </div>
          <div class="form-group">
            <label>CT-2</label>
            <input name="ct_02" id="ct_02" type="text" placeholder="CT-2" oninput="validateIntegerWithMax(this, 20)" />
            <div class="error-message" id="ct_02_error">Please enter a valid integer.</div>
          </div>
          <div class="form-group">
            <label>CT-3</label>
            <input name="ct_03" id="ct_03" type="text" placeholder="CT-3" oninput="validateIntegerWithMax(this, 20)" />
            <div class="error-message" id="ct_03_error">Please enter a valid integer.</div>
          </div>
          <div class="form-group">
            <label>Assignment</label>
            <input name="assignment" id="assignment" type="text" placeholder="Assignment" oninput="validateIntegerWithMax(this, 15)" />
            <div class="error-message" id="assignment_error">Please enter a valid integer.</div>
          </div>
          <div class="form-group">
            <label>Mid Tern</label>
            <input name="Mid_trem" id="Mid_trem" type="text" placeholder="Mid Tern" oninput="validateIntegerWithMax(this, 45)" />
            <div class="error-message" id="Mid_trem_error">Please enter a valid integer.</div>
          </div>
          <div class="form-group">
            <label>Attendance</label>
            <input name="attendance" id="attendance" type="text" placeholder="Attendance" oninput="validateIntegerWithMax(this, 10)" />
            <div class="error-message" id="attendance_error">Please enter a valid integer.</div>
          </div>
          <div class="form-group">
            <label>Performance</label>
            <input name="performance" id="performance" type="text" placeholder="Performance" oninput="validateIntegerWithMax(this, 5)" />
            <div class="error-message" id="performance_error">Please enter a valid integer.</div>
          </div>
          <div class="form-group">
            <label>Final Part A</label>
            <input name="final_part_A" id="final_part_A" type="text" placeholder="Final Part A" oninput="validateIntegerWithMax(this, 90)" />
            <div class="error-message" id="final_part_A_error">Please enter a valid integer.</div>
          </div>
          <div class="form-group">
            <label>Final Part B</label>
            <input name="final_part_B" id="final_part_B" type="text" placeholder="Final Part B" oninput="validateIntegerWithMax(this, 90)" />
            <div class="error-message" id="final_part_B_error">Please enter a valid integer.</div>
          </div>
          <div class="form-group button-group">
            <label>&nbsp;</label>
            <button class="submit-btn" type="submit" onclick="setAction('add')">Add Result</button>
          </div>
        </form>
    </div>
  </main>
  <script>
    function setAction(action) {
      document.getElementById('form_action').value = action;
    }

    function validateIntegerWithMax(input, max) {
      const value = input.value.trim();
      const id = input.id;
      const errorDiv = document.getElementById(id + "_error");

      if (value === "") {
        input.classList.remove("error");
        errorDiv.style.display = "none";
        errorDiv.textContent = "";
        return;
      }

      if (!/^\d+$/.test(value)) {
        input.classList.add("error");
        errorDiv.style.display = "block";
        errorDiv.textContent = "Please enter a valid integer.";
      } else if (Number(value) > max) {
        input.classList.add("error");
        errorDiv.style.display = "block";
        errorDiv.textContent = `You entered ${value}, but max allowed is ${max}.`;
      } else {
        input.classList.remove("error");
        errorDiv.style.display = "none";
        errorDiv.textContent = "";
      }
    }

    document.getElementById("resultForm").addEventListener("submit", function(e) {
      let valid = true;

      const fieldsWithMax = [{
          id: "ct_01",
          max: 20
        },
        {
          id: "ct_02",
          max: 20
        },
        {
          id: "ct_03",
          max: 20
        },
        {
          id: "Mid_trem",
          max: 45
        },
        {
          id: "assignment",
          max: 15
        },
        {
          id: "attendance",
          max: 10
        },
        {
          id: "performance",
          max: 5
        },
        {
          id: "final_part_A",
          max: 90
        },
        {
          id: "final_part_B",
          max: 90
        }
      ];

      fieldsWithMax.forEach(({
        id,
        max
      }) => {
        const input = document.getElementById(id);
        const errorDiv = document.getElementById(id + "_error");
        const val = input.value.trim();

        if (val === "") {
          input.classList.remove("error");
          errorDiv.style.display = "none";
          errorDiv.textContent = "";
        } else if (!/^\d+$/.test(val)) {
          input.classList.add("error");
          errorDiv.style.display = "block";
          errorDiv.textContent = "Please enter a valid integer.";
          valid = false;
        } else if (Number(val) > max) {
          input.classList.add("error");
          errorDiv.style.display = "block";
          errorDiv.textContent = `You entered ${val}, but max allowed is ${max}.`;
          valid = false;
        } else {
          input.classList.remove("error");
          errorDiv.style.display = "none";
          errorDiv.textContent = "";
        }
      });
      const studentInput = document.getElementById("student_id");
      const studentErrorDiv = document.getElementById("student_id_error");
      const studentVal = studentInput.value.trim();
      if (studentVal === "" || !/^\d+$/.test(studentVal)) {
        studentInput.classList.add("error");
        studentErrorDiv.style.display = "block";
        studentErrorDiv.textContent = "Please enter a valid integer.";
        valid = false;
      } else {
        studentInput.classList.remove("error");
        studentErrorDiv.style.display = "none";
        studentErrorDiv.textContent = "";
      }

      if (!valid) {
        e.preventDefault();
      }
    });
  </script>
</body>

</html>