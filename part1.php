<?php
session_start();

$conn = new mysqli("localhost", "root", "", "eazycart");

if ($conn->connect_error) {
    die("Database connection failed");
}

$conn->set_charset("utf8mb4");

$action = isset($_GET["action"]) ? $_GET["action"] : "";

if ($action == "products") {
    $result = $conn->query("SELECT product_id, product_name, category, price, description, image FROM products");
    $products = array();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }

    header("Content-Type: application/json");
    echo json_encode($products);
    exit;
}

if ($action == "add") {
    $product_id = intval($_POST["product_id"]);

    $session_id = session_id();

    $check = $conn->prepare("SELECT cart_id FROM cart WHERE session_id = ? AND product_id = ?");
    $check->bind_param("si", $session_id, $product_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $update = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE session_id = ? AND product_id = ?");
        $update->bind_param("si", $session_id, $product_id);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert->bind_param("si", $session_id, $product_id);
        $insert->execute();
    }

    echo "Product added";
    exit;
}

if ($action == "cart") {
    $session_id = session_id();

    $sql = "SELECT c.cart_id, c.product_id, c.quantity, p.product_name, p.price, p.image
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.session_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = array();

    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    header("Content-Type: application/json");
    echo json_encode($items);
    exit;
}

if ($action == "remove") {
    $cart_id = intval($_POST["cart_id"]);
    $session_id = session_id();

    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND session_id = ?");
    $stmt->bind_param("is", $cart_id, $session_id);
    $stmt->execute();

    echo "Removed";
    exit;
}

$conn->close();
?>
