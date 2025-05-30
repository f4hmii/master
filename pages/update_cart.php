<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['id'])) {
    die("Silakan login terlebih dahulu.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);

    if ($quantity < 1) {
        die("Jumlah minimal 1.");
    }

    // Update quantity di cart
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND pengguna_id = ?");
    $stmt->bind_param("iii", $quantity, $cart_id, $_SESSION['id']);
    $stmt->execute();

    header("Location: cart.php");
    exit;
} else {
    header("Location: cart.php");
    exit;
}
