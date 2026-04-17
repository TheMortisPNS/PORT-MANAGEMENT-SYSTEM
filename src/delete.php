<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM arrivals WHERE id = $id");
}

header("Location: index.php?deleted=1");
exit();
?>
