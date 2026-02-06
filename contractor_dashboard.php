<?php
session_start();
require 'db.php';

// Security: Only contractors
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'contractor') {
    header("Location: login.php");
    exit();
}

$contractor_id = $_SESSION['user_id'];

// Fetch assigned projects
$stmt = $pdo->prepare("
    SELECT p.id, p.title, p.status, p.start_date, p.end_date
    FROM assignments a
    JOIN projects p ON a.project_id = p.id
    WHERE a.contractor_id = ?
");
$stmt->execute([$contractor_id]);
$projects = $stmt->fetchAll();

include 'header.php';
?>
<div class="container py-5">
    <h2 class="text-primary">Welcome, <?= htmlspecialchars($_SESSION['name']); ?></h2>
    <div class="mb-3">
        <a href="submit_progress.php" class="btn btn-success">📤 Submit Progress</a>
        <a href="view_my_submissions.php" class="btn btn-info">📑 View My Submissions</a>
        <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">My Assigned Projects</div>
        <div class="card-body">
            <?php if ($projects): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['title']); ?></td>
                            <td><?= htmlspecialchars($p['status']); ?></td>
                            <td><?= $p['start_date']; ?></td>
                            <td><?= $p['end_date']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No projects assigned yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
