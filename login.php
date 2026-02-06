<?php
session_start();
require 'db.php';

$errors = [];
$ue = ''; // initialize for form value retention

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ue = trim($_POST['username_or_email'] ?? '');
    $pw = $_POST['password'] ?? '';

    if ($ue === '' || $pw === '') {
        $errors[] = "All fields are required.";
    } else {
        // Corrected PDO placeholders
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1");
        $stmt->execute([
            'username' => $ue,
            'email'    => $ue
        ]);
        $user = $stmt->fetch();

        if ($user && password_verify($pw, $user['password'])) {
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'owner') {
                header("Location: owner_dashboard.php");
            } else {
                header("Location: contractor_dashboard.php");
            }
            exit;
        } else {
            $errors[] = "Invalid username/email or password.";
        }
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow p-4">
      <h3 class="text-center text-primary mb-3">Login</h3>

      <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
          <?= htmlspecialchars($_SESSION['success']); ?>
        </div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="mb-3">
          <label class="form-label">Username or Email</label>
          <input type="text" class="form-control" name="username_or_email" value="<?= htmlspecialchars($ue); ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>
        <button class="btn btn-primary w-100">Login</button>
      </form>

      <p class="mt-3 text-center">
        <a href="forgot_password.php">Forgot password?</a> | 
        <a href="register.php">Register</a>
      </p>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
