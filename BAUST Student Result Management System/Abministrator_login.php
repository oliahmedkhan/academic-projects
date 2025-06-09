<?php
session_start();

$dsn = "mysql:host=localhost;dbname=baust_student_result_management_system";
$username = "root";
$password = "";

try {
  $conn = new PDO($dsn, $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $error = "";

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deptName = $_POST['department'] ?? '';
    $adminId = $_POST['admin_id'] ?? '';
    $adminPass = $_POST['admin_password'] ?? '';

    $stmt = $conn->prepare("
      SELECT a.id, a.password 
      FROM administrator a
      JOIN department d ON d.admin_id = a.id
      WHERE d.Name = ? AND a.id = ? AND a.password = ?
    ");
    $stmt->execute([$deptName, $adminId, $adminPass]);

    if ($stmt->rowCount() === 1) {
      session_start();
      $_SESSION['admin_id'] = $adminId; 
      header("Location: Administrstor_home.php");
      exit;
    } else {
      $error = "❌ Invalid login. Please try again.";
    }
  }
} catch (PDOException $e) {
  $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BAUST Result</title>
  <link rel="stylesheet" href="Abministrator_login.css?v=1" />
</head>

<body>
  <p class="text">Administrator login</p>

  <?php if (!empty($error)): ?>
    <p style="color: red; text-align: center;"><?= $error ?></p>
  <?php endif; ?>

  <form class="input" method="POST" action="">
    <input class="name" type="text" name="department" placeholder="Name" required />
    <input class="id" type="text" name="admin_id" placeholder="ID" required />
    <input class="password" type="password" name="admin_password" placeholder="Password" required />
    <button class="btn" type="submit">Login</button>
  </form>
</body>

</html>