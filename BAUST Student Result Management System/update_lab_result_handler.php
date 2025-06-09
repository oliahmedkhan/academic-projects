<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $dsn = "mysql:host=localhost;dbname=baust_student_result_management_system";
  $username = "root";
  $password = "";

  try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    foreach ($_POST['attendance'] as $id => $attendance) {
      $stmt = $conn->prepare("
        UPDATE lab_result SET
          attendance = ?,
          report = ?,
          mid_trem = ?,
          Final = ?,
          quiz = ?,
          viva = ?,
          parformance = ?
        WHERE id = ?
      ");
      $stmt->execute([
        $_POST['attendance'][$id],
        $_POST['report'][$id],
        $_POST['mid'][$id],
        $_POST['final'][$id],
        $_POST['quiz'][$id],
        $_POST['viva'][$id],
        $_POST['parformance'][$id],
        $id
      ]);
    }

    echo "Lab results updated successfully.";
  } catch (PDOException $e) {
    echo "Update failed: " . $e->getMessage();
  }
}
