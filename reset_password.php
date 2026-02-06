<?php
session_start(); require 'db.php';
$token=$_GET['token']??$_POST['token']??''; $errors=[]; $success='';
$stmt=$pdo->prepare("SELECT * FROM users WHERE reset_token=:t LIMIT 1"); $stmt->execute(['t'=>$token]); $user=$stmt->fetch();
if(!$user) exit('Invalid token');
if(new DateTime()>new DateTime($user['reset_expires'])) exit('Token expired.');
if($_SERVER['REQUEST_METHOD']==='POST'){
  $p=$_POST['password']??''; $pc=$_POST['password_confirm']??'';
  if(strlen($p)<6) $errors[]="Password ≥6 chars."; if($p!==$pc) $errors[]="Passwords don’t match.";
  if(!$errors){$hash=password_hash($p,PASSWORD_DEFAULT); $pdo->prepare("UPDATE users SET password=:p,reset_token=NULL,reset_expires=NULL WHERE id=:id")->execute(['p'=>$hash,'id'=>$user['id']]); $success="Password reset successful. <a href='login.php'>Login</a>";}
}
include 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card p-4">
      <h3 class="text-center text-primary mb-3">Reset Password</h3>
      <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
      <?php if($errors) echo "<div class='alert alert-danger'>".implode('<br>',$errors)."</div>"; ?>
      <?php if(!$success): ?>
      <form method="post">
        <input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" class="form-control" name="password_confirm" required>
        </div>
        <button class="btn btn-primary w-100">Reset Password</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
