<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'contractor') {
    header("Location: login.php");
    exit();
}

$contractor_id = $_SESSION['user_id'];

$query = "
    SELECT s.id, p.project_name, s.update_text, s.file_path, s.status, s.submitted_at
    FROM submissions s
    JOIN projects p ON s.project_id = p.id
    WHERE s.contractor_id = ?
    ORDER BY s.submitted_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $contractor_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Submissions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="text-primary">My Submissions</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Project</th>
                <th>Update</th>
                <th>File</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['project_name']; ?></td>
                <td><?php echo $row['update_text']; ?></td>
                <td>
                    <?php if ($row['file_path']) { ?>
                        <a href="<?php echo $row['file_path']; ?>" target="_blank">View File</a>
                    <?php } ?>
                </td>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td><?php echo $row['submitted_at']; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
