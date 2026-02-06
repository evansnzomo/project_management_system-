<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'contractor') {
    header("Location: login.php");
    exit();
}

$contractor_id = $_SESSION['user_id'];

// Fetch assigned projects
$query = "
    SELECT p.id, p.project_name 
    FROM assignments a
    JOIN projects p ON a.project_id = p.id
    WHERE a.contractor_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $contractor_id);
$stmt->execute();
$projects = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_id = $_POST['project_id'];
    $update_text = $_POST['update_text'];
    $file_path = null;

    // Handle file upload
    if (!empty($_FILES['file']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $file_path = $targetDir . time() . "_" . basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
    }

    $query = "INSERT INTO submissions (project_id, contractor_id, update_text, file_path) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $project_id, $contractor_id, $update_text, $file_path);

    if ($stmt->execute()) {
        $success = "Progress submitted successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Progress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="text-primary">Submit Project Progress</h2>
    <?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label>Select Project</label>
            <select name="project_id" class="form-select" required>
                <?php while ($p = $projects->fetch_assoc()) { ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo $p['project_name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Progress Update</label>
            <textarea name="update_text" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label>Upload File (optional)</label>
            <input type="file" name="file" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Submit Progress</button>
        <a href="contractor_dashboard.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
