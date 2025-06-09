<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
  session_unset();
  session_destroy();
  header("Location: Teacher_login.php");
  exit;
}

if (!isset($_SESSION['teacher_id'])) {
  header("Location: Teacher_login.php");
  exit;
}

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];

$dsn = "mysql:host=localhost;dbname=baust_student_result_management_system";
$username = "root";
$password = "";

try {
  $conn = new PDO($dsn, $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $conn->prepare("
    SELECT 
      a.teacher_id,
      a.course_id,
      a.section_id,
      c.no AS course_no,
      c.title AS course_title,
      s.NAME AS section_name,
      a.level_term
    FROM assign a
    JOIN courses c ON a.course_id = c.ID
    JOIN section s ON a.section_id = s.id
    WHERE a.teacher_id = ?
  ");
  $stmt->execute([$teacher_id]);
  $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
  <link rel="stylesheet" href="Teacher_home.css?v=<?php echo time(); ?>" />
</head>

<body>
  <header class="header_color">
    <div class="navbar">
      <div class="navbar-start">
        <img class="logo_img" src="./img/Baust_Logo.png" alt="BAUST Logo" />
      </div>
      <div class="navbar-center">
        <a class="logo">Welcome, <?php echo htmlspecialchars($teacher_name); ?></a>
      </div>
      <div class="navbar-end">
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

  <main>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Course No.</th>
            <th>Course Title</th>
            <th>Section</th>
            <th>Level Term</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($assignments as $row): ?>
            <?php
            $isLab = stripos($row["course_title"], "Sessional") !== false;
            $addUrl = $isLab ? "Add_lab_result.php" : "Add_Result.php";
            $viewScript = $isLab ? "get_lab_results.php" : "get_results.php";
            $updateScript = $isLab ? "get_lab_update_form.php" : "get_update_form.php";
            ?>
            <tr>
              <td><?= htmlspecialchars($row["course_no"]) ?></td>
              <td><?= htmlspecialchars($row["course_title"]) ?></td>
              <td><?= htmlspecialchars($row["section_name"]) ?></td>
              <td><?= htmlspecialchars($row["level_term"]) ?></td>
              <td>
                <a href="<?= $addUrl ?>?teacher_id=<?= $row['teacher_id'] ?>&course_id=<?= $row['course_id'] ?>&section_id=<?= $row['section_id'] ?>"
                  style="padding: 6px 12px; background-color: #2563eb; color: #fff; border-radius: 6px; font-size: 12px; font-weight: 500; text-decoration: none;">
                  Add Results
                </a>
                <button class='view-btn'
                  data-script="<?= $viewScript ?>"
                  data-teacher="<?= $row['teacher_id'] ?>"
                  data-course="<?= $row['course_id'] ?>"
                  data-section="<?= $row['section_id'] ?>"
                  style="margin-left: 6px; padding: 6px 12px; background-color: #10b981; color: #fff; border-radius: 6px; font-size: 12px; font-weight: 500; border: none; cursor: pointer;">
                  View Results
                </button>
                <button class='update-btn'
                  data-script="<?= $updateScript ?>"
                  data-teacher="<?= $row['teacher_id'] ?>"
                  data-course="<?= $row['course_id'] ?>"
                  data-section="<?= $row['section_id'] ?>"
                  style="margin-left: 6px; padding: 6px 12px; background-color: #f59e0b; color: #fff; border-radius: 6px; font-size: 12px; font-weight: 500; border: none; cursor: pointer;">
                  Update Results
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
  <div id="modal" class="hidden" style="
          position: fixed;
          top: 0; left: 0; right: 0; bottom: 0;
          background: rgba(0, 0, 0, 0.7);
          display: none;
          justify-content: center;
          align-items: center;
          z-index: 9999;
        ">
    <div style="
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-height: 90%;
            overflow-y: auto;
            position: relative;
          ">
      <button onclick="closeModal()" style="
              position: absolute;
              top: 10px;
              right: 10px;
              background: red;
              color: white;
              border: none;
              padding: 5px 10px;
              border-radius: 4px;
              cursor: pointer;
            ">X</button>
      <div id="modal-body">Loading...</div>
    </div>
  </div>
  </div>
  </div>

  <script>
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');

    function closeModal() {
      modal.style.display = 'none';
      modalBody.innerHTML = '';
    }

    document.querySelectorAll('.view-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const script = btn.dataset.script;
        const teacher = btn.dataset.teacher;
        const course = btn.dataset.course;
        const section = btn.dataset.section;

        modalBody.innerHTML = `<h3>Loading results...</h3>`;
        modal.style.display = 'flex';

        fetch(`${script}?teacher_id=${teacher}&course_id=${course}&section_id=${section}`)
          .then(res => res.text())
          .then(html => {
            modalBody.innerHTML = html;
          })
          .catch(() => {
            modalBody.innerHTML = `<p>Error loading results.</p>`;
          });
      });
    });

    document.querySelectorAll('.update-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const script = btn.dataset.script;
        const teacher = btn.dataset.teacher;
        const course = btn.dataset.course;
        const section = btn.dataset.section;

        modalBody.innerHTML = `<h3>Loading update form...</h3>`;
        modal.style.display = 'flex';

        fetch(`${script}?teacher_id=${teacher}&course_id=${course}&section_id=${section}`)
          .then(res => res.text())
          .then(html => {
            modalBody.innerHTML = html;
          })
          .catch(() => {
            modalBody.innerHTML = `<p>Error loading update form.</p>`;
          });
      });
    });
  </script>


</body>

</html>