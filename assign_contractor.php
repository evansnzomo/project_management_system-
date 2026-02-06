<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];

// Fetch owner projects
$stmt = $pdo->prepare("SELECT * FROM projects WHERE owner_id = ?");
$stmt->execute([$owner_id]);
$projects = $stmt->fetchAll();

// Fetch contractors
$stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'contractor'");
$contractors = $stmt->fetchAll();

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_id = $_POST['project_id'];
    $contractor_id = $_POST['contractor_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO assignments (project_id, contractor_id) VALUES (?, ?)");
        $stmt->execute([$project_id, $contractor_id]);
        $success = "Contractor assigned successfully!";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Contractor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="text-primary">Assign Contractor</h2>
    <?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label>Select Project</label>
            <select name="project_id" class="form-select" required>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= htmlspecialchars($p['id']) ?>">
                        <?= htmlspecialchars($p['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Select Contractor</label>
            <select name="contractor_id" class="form-select" required>
                <?php foreach ($contractors as $c): ?>
                    <option value="<?= htmlspecialchars($c['id']) ?>">
                        <?= htmlspecialchars($c['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-warning">Assign Contractor</button>
        <a href="owner_dashboard.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
