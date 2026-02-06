<?php
session_start();
require 'db.php';

// Security: Only allow owners
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

// Fetch all projects
$stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
$projects = $stmt->fetchAll();

// Fetch all contractors
$stmt2 = $pdo->query("SELECT id, name FROM users WHERE role='contractor'");
$contractors = $stmt2->fetchAll();

include 'header.php';
?>
<div class="container py-4">
    <h2 class="text-primary">Owner Dashboard</h2>
    <div class="mb-3">
        <a href="add_project.php" class="btn btn-success">➕ Add Project</a>
        <a href="assign_contractor.php" class="btn btn-info">📝 Assign Contractor</a>
        <a href="view_submissions.php" class="btn btn-secondary">📑 View Submissions</a>
        <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
    </div>

    <h4>All Projects</h4>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Project Name</th>
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
</div>
<?php include 'footer.php'; ?>
