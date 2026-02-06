<?php
session_start();
require 'db.php';

// Security: Only contractors
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'contractor') {
    header("Location: login.php");
    exit();
}

$contractor_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT s.id, p.project_name, s.update_text, s.file_path, s.status, s.submitted_at
    FROM submissions s
    JOIN projects p ON s.project_id = p.id
    WHERE s.contractor_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->execute([$contractor_id]);
$submissions = $stmt->fetchAll();

include 'header.php';
?>
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
        <?php foreach ($submissions as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['project_name']); ?></td>
                <td><?= htmlspecialchars($row['update_text']); ?></td>
                <td>
                    <?php if ($row['file_path']): ?>
                        <a href="<?= $row['file_path']; ?>" target="_blank">View File</a>
                    <?php endif; ?>
                </td>
                <td><?= ucfirst($row['status']); ?></td>
                <td><?= $row['submitted_at']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>
