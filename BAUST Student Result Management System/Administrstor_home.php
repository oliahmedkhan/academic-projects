<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
  header("Location: Abministrator_login.php");
  exit;
}

$admin_id = $_SESSION['admin_id'];
$host = 'localhost';
$dbname = 'baust_student_result_management_system';
$username = 'root';
$password = '';

try {
  $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_no = isset($_POST['course_no']) ? trim($_POST['course_no']) : '';
    $teacher_name = isset($_POST['teacher_name']) ? trim($_POST['teacher_name']) : '';
    $section_name = isset($_POST['section']) ? trim($_POST['section']) : '';
    $semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';
    $level_term = isset($_POST['level_term']) ? trim($_POST['level_term']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $action = $_POST['action'] ?? 'insert';

    $stmt = $conn->prepare("SELECT ID FROM courses WHERE no = :course_no");
    $stmt->execute(['course_no' => $course_no]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT ID FROM teacher WHERE name = :teacher_name");
    $stmt->execute(['teacher_name' => $teacher_name]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT id FROM section WHERE NAME = :section_name");
    $stmt->execute(['section_name' => $section_name]);
    $section = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course || !$teacher || !$section) {
      die("❌ Course/Teacher/Section not found. Please check input.");
    }

    if ($action === 'insert') {
      $stmt = $conn->prepare("
        INSERT INTO assign (teacher_id, course_id, section_id, semester, level_term, description)
        VALUES (:teacher_id, :course_id, :section_id, :semester, :level_term, :description)
      ");
      $stmt->execute([
        'teacher_id' => $teacher['ID'],
        'course_id' => $course['ID'],
        'section_id' => $section['id'],
        'semester' => $semester,
        'level_term' => $level_term,
        'description' => $description
      ]);
      echo "<script>alert('✅ Assignment inserted successfully');</script>";
    } elseif ($action === 'update') {
      $stmt = $conn->prepare("
        UPDATE assign SET 
          semester = :semester,
          level_term = :level_term,
          description = :description
        WHERE teacher_id = :teacher_id AND course_id = :course_id AND section_id = :section_id
      ");
      $stmt->execute([
        'teacher_id' => $teacher['ID'],
        'course_id' => $course['ID'],
        'section_id' => $section['id'],
        'semester' => $semester,
        'level_term' => $level_term,
        'description' => $description
      ]);
      echo "<script>alert('✅ Assignment updated successfully');</script>";
    } elseif ($action === 'delete') {
      $stmt = $conn->prepare("
        DELETE FROM assign 
        WHERE teacher_id = :teacher_id AND course_id = :course_id AND section_id = :section_id
      ");
      $stmt->execute([
        'teacher_id' => $teacher['ID'],
        'course_id' => $course['ID'],
        'section_id' => $section['id']
      ]);
      echo "<script>alert('✅ Assignment deleted successfully');</script>";
    }
  }
} catch (PDOException $e) {
  echo "❌ PDO Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BAUST Result</title>
  <link rel="stylesheet" href="Administrstor_home.css?v=<?php echo time(); ?>" />
</head>

<body>
  <header class="header_color">
    <div class="navbar">
      <div class="navbar-start">
        <div>
          <img
            class="logo_img"
            src="./img/Baust_Logo.png"
            alt="" />
        </div>
      </div>
      <div class="navbar-center">
        <a class="logo">Administrator</a>
      </div>
      <div class="navbar-end">
        <form method="get" action="Abministrator_login.php" style="display:inline;">
          <button class="btn-circle" title="Go Logout" type="submit">
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
        <input type="hidden" name="action" id="form_action" value="insert" />

        <div class="form-group">
          <label for="course_no">Course No.</label>
          <input id="course_no" name="course_no" type="text" placeholder="Course No." required />
        </div>

        <div class="form-group">
          <label for="teacher_name">Teacher Name</label>
          <input id="teacher_name" name="teacher_name" type="text" placeholder="Teacher Name" required />
        </div>

        <div class="form-group">
          <label for="section">Section</label>
          <input id="section" name="section" type="text" placeholder="Section.." required />
        </div>

        <div class="form-group">
          <label for="semester">Semester</label>
          <input id="semester" name="semester" type="text" placeholder="Semester" required />
        </div>

        <div class="form-group">
          <label for="level_term">Level Term</label>
          <input id="level_term" name="level_term" type="text" placeholder="Level term.." required />
        </div>

        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" placeholder="Description .."></textarea>
        </div>

        <div class="form-group full-width">
          <button class="submit-btn" type="submit" onclick="setAction('insert')">Insert</button>
          <button class="submit-btn" type="submit" onclick="setAction('update')">Update</button>
          <button class="submit-btn" type="submit" onclick="setAction('delete')">Delete</button>
        </div>
        <div class="form-group full-width" style="text-align: center; margin-top: 10px;">
          <button class="submit-btn" type="button" onclick="toggleModal()">📋 View Current Assignments</button>
        </div>
      </form>
    </div>
  </main>
  <div id="assignmentModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
    background-color:rgba(0,0,0,0.5); z-index:1000;">
    <div style="background:white; margin:5% auto; padding:20px; width:90%; max-width:900px; border-radius:10px; position:relative;">
      <span onclick="toggleModal()" style="position:absolute; top:10px; right:20px; font-size:20px; cursor:pointer;">&times;</span>
      <h2>📋 Assigned Courses</h2>
      <div style="overflow-x:auto;">
        <table border="1" cellpadding="8" cellspacing="0" width="100%">
          <thead>
            <tr style="background:#f2f2f2;">
              <th>Teacher</th>
              <th>Course No</th>
              <th>Course Title</th>
              <th>Section</th>
              <th>Semester</th>
              <th>Level Term</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $stmt = $conn->query("
            SELECT 
              t.name AS teacher_name,
              c.no AS course_no,
              c.title AS course_title,
              s.NAME AS section_name,
              a.semester,
              a.level_term,
              a.description
            FROM assign a
            JOIN teacher t ON a.teacher_id = t.ID
            JOIN courses c ON a.course_id = c.ID
            JOIN section s ON a.section_id = s.id
            ORDER BY t.name, c.no
          ");

            $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($assignments) {
              foreach ($assignments as $row) {
                echo "<tr>
                <td>{$row['teacher_name']}</td>
                <td>{$row['course_no']}</td>
                <td>{$row['course_title']}</td>
                <td>{$row['section_name']}</td>
                <td>{$row['semester']}</td>
                <td>{$row['level_term']}</td>
                <td>{$row['description']}</td>
              </tr>";
              }
            } else {
              echo "<tr><td colspan='7'>❗ No assignments found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    function setAction(action) {
      document.getElementById('form_action').value = action;
    }

    function toggleModal() {
      const modal = document.getElementById('assignmentModal');
      modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
    }
  </script>
</body>

</html>