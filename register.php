<?php
session_start();
require 'db.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = ($_POST['role'] === 'owner') ? 'owner' : 'contractor';

    if ($name === '') $errors[] = "Name is required.";
    if ($username === '') $errors[] = "Username is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $password_confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1");
        $stmt->execute(['username' => $username, 'email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or email already taken.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (name, username, email, password, role) VALUES (:name,:username,:email,:password,:role)");
            $ins->execute([
                'name'=>$name,'username'=>$username,'email'=>$email,'password'=>$hash,'role'=>$role
            ]);
            $_SESSION['success']="Registration successful. Please log in.";
            header('Location: login.php'); exit;
        }
    }
}
include 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4">
      <h3 class="text-center text-primary mb-3">Register</h3>
      <?php if ($errors): ?>
        <div class="alert alert-danger"><?php foreach($errors as $e) echo "<div>$e</div>"; ?></div>
      <?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input class="form-control" name="name" required value="<?=htmlspecialchars($name??'')?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input class="form-control" name="username" required value="<?=htmlspecialchars($username??'')?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" required value="<?=htmlspecialchars($email??'')?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" class="form-control" name="password_confirm" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select class="form-select" name="role">
            <option value="contractor">Contractor</option>
            <option value="owner">Project Owner</option>
          </select>
        </div>
        <button class="btn btn-primary w-100">Register</button>
      </form>
      <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
