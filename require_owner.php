<?php
// require_owner.php
// Include this at the very top of pages that must be owner-only.
// It starts session and redirects non-owners to login.

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'owner') {
    // Optionally store a flash message
    $_SESSION['error'] = "You must be logged in as a Project Owner to access that page.";
    header('Location: login.php');
    exit;
}
