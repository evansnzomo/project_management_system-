<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $submission_id = $_GET['id'];
    $status = $_GET['status'];

    $query = "UPDATE submissions SET status=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $submission_id);

    if ($stmt->execute()) {
        header("Location: view_submissions.php?msg=success");
    } else {
        header("Location: view_submissions.php?msg=error");
    }
}
?>
