<?php
session_start();

$dsn = "mysql:host=localhost;dbname=baust_student_result_management_system";
$username = "root";
$password = "";

try {
  $conn = new PDO($dsn, $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['ID'] ?? '';
    $name = $_POST['name'] ?? '';
    $pass = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM teacher WHERE ID = ? AND name = ? AND password = ?");
    $stmt->execute([$id, $name, $pass]);

    if ($stmt->rowCount() === 1) {
      $_SESSION['teacher_id'] = $id;
      $_SESSION['teacher_name'] = $name;
      header("Location: Teacher_home.php");
      exit;
    } else {
      echo "Invalid login.";
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
  <title>Teacher Login</title>
  <link rel="stylesheet" href="./Teacher_login.css" />
</head>

<body>
  <p class="text">Teacher login</p>
  <form class="input" method="POST" action="">
    <input class="name" type="text" name="name" placeholder="Name" required />
    <input class="id" type="text" name="ID" placeholder="ID" required />
    <input class="password" type="password" name="password" placeholder="Password" required />
    <button class="btn" type="submit">Login</button>
  </form>
</body>

</html>