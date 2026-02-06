<?php
session_start(); require 'db.php';
$errors=[]; $success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=trim($_POST['email']??'');
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]="Provide valid email.";
  else {
    $stmt=$pdo->prepare("SELECT * FROM users WHERE email=:email LIMIT 1"); $stmt->execute(['email'=>$email]);
    $user=$stmt->fetch();
    if($user){
      $token=bin2hex(random_bytes(16));
      $expires=date('Y-m-d H:i:s',time()+3600);
      $pdo->prepare("UPDATE users SET reset_token=:t,reset_expires=:e WHERE id=:id")->execute(['t'=>$token,'e'=>$expires,'id'=>$user['id']]);
      $link="http://{$_SERVER['HTTP_HOST']}/reset_password.php?token=$token";
      mail($email,"Password Reset","Reset via: $link","From:no-reply@".$_SERVER['HTTP_HOST']);
    }
    $success="If that email exists, we’ve sent a reset link.";
  }
}
include 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card p-4">
      <h3 class="text-center text-primary mb-3">Forgot Password</h3>
      <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
      <?php if($errors) echo "<div class='alert alert-danger'>".implode('<br>',$errors)."</div>"; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" required>
        </div>
        <button class="btn btn-primary w-100">Send Reset Link</button>
      </form>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
