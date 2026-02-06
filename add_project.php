<?php
// add_project.php
require 'require_owner.php';   // starts session and enforces owner role
require 'db.php';             // provides $pdo

$errors = [];
$success = '';

// CSRF token (simple)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Invalid form submission.";
    } else {
        // Collect + sanitize
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $budget_raw = str_replace(',', '', trim($_POST['budget'] ?? '')); // remove commas if any
        $start_date = trim($_POST['start_date'] ?? '');
        $end_date = trim($_POST['end_date'] ?? '');

        // Validation
        if ($title === '') $errors[] = "Project title is required.";
        if ($budget_raw !== '' && !is_numeric($budget_raw)) $errors[] = "Budget must be a number.";
        // validate dates (allow empty)
        try {
            $sd = $start_date ? new DateTime($start_date) : null;
            $ed = $end_date ? new DateTime($end_date) : null;
            if ($sd && $ed && $sd > $ed) $errors[] = "Start date must be before or equal to end date.";
        } catch (Exception $e) {
            $errors[] = "Provide valid start and end dates (YYYY-MM-DD).";
        }

        if (empty($errors)) {
            $budget = $budget_raw === '' ? null : number_format((float)$budget_raw, 2, '.', '');
            $insert = $pdo->prepare("
                INSERT INTO projects (owner_id, title, description, budget, start_date, end_date, status)
                VALUES (:owner_id, :title, :description, :budget, :start_date, :end_date, 'Pending')
            ");
            $insert->execute([
                'owner_id' => $_SESSION['user_id'],
                'title' => $title,
                'description' => $description ?: null,
                'budget' => $budget,
                'start_date' => $sd ? $sd->format('Y-m-d') : null,
                'end_date' => $ed ? $ed->format('Y-m-d') : null
            ]);

            $success = "Project created successfully.";
            // Rotate CSRF token to prevent double submissions
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $csrf_token = $_SESSION['csrf_token'];
        }
    }
}

// Fetch projects for this owner to show below the form
$stmt = $pdo->prepare("
    SELECT p.*, u.username AS contractor_username
    FROM projects p
    LEFT JOIN users u ON p.contractor_id = u.id
    WHERE p.owner_id = :owner_id
    ORDER BY p.created_at DESC
");
$stmt->execute(['owner_id' => $_SESSION['user_id']]);
$projects = $stmt->fetchAll();

include 'header.php';
?>
<div class="row">
  <div class="col-md-8 offset-md-2">
    <div class="card p-4 mb-4">
      <h4 class="text-primary mb-3">Add Project / Tender</h4>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $e) echo "<div>" . htmlspecialchars($e) . "</div>"; ?>
        </div>
      <?php endif; ?>

      <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="mb-3">
          <label class="form-label">Project Title <span class="text-danger">*</span></label>
          <input name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Budget (optional)</label>
          <input name="budget" class="form-control" placeholder="e.g. 150000.00" value="<?= htmlspecialchars($_POST['budget'] ?? '') ?>">
          <div class="form-text">Enter amount in your currency (no symbols).</div>
        </div>

        <div class="row mb-3">
          <div class="col">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
          </div>
          <div class="col">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
          </div>
        </div>

        <button class="btn btn-primary">Create Project</button>
        <a href="owner_dashboard.php" class="btn btn-outline-secondary ms-2">Back to Dashboard</a>
      </form>
    </div>

    <div class="card p-3">
      <h5 class="text-primary">My Projects</h5>
      <?php if (empty($projects)): ?>
        <p class="text-muted">No projects yet. Create one above.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead class="table-light">
              <tr>
                <th>Title</th>
                <th>Budget</th>
                <th>Contractor</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($projects as $p): ?>
                <tr>
                  <td><?= htmlspecialchars($p['title']) ?></td>
                  <td><?= $p['budget'] !== null ? number_format($p['budget'],2) : '<span class="text-muted">—</span>' ?></td>
                  <td><?= $p['contractor_username'] ? htmlspecialchars($p['contractor_username']) : '<em class="text-muted">Not assigned</em>' ?></td>
                  <td><?= htmlspecialchars($p['status']) ?></td>
                  <td><?= htmlspecialchars((new DateTime($p['created_at']))->format('Y-m-d')) ?></td>
                  <td>
                    <a href="assign_contractor.php?project_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Assign</a>
                    <a href="edit_project.php?project_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <a href="view_project.php?project_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-info">View</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
