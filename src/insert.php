<?php
include 'db_connect.php';

$ship = $_POST['ship_name'];
$imo = $_POST['imo_number'];
$cargo = $_POST['cargo_type'];
$arrival = $_POST['arrival_time'];

$sql = "INSERT INTO arrivals (ship_name, imo_number, cargo_type, arrival_time) 
        VALUES ('$ship', '$imo', '$cargo', '$arrival')";

if ($conn->query($sql) === TRUE) {
    echo "Η καταχώρηση έγινε επιτυχώς!";
    header("Location: index.php"); // Σε γυρνάει αυτόματα στην αρχική σελίδα
} else {
    echo "Σφάλμα: " . $sql . "<​​br>" . $conn->error;
}

$conn->close();
?>
