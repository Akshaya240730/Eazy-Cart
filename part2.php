<?php
session_start();

$conn = new mysqli("localhost", "root", "", "eazycart");

if ($conn->connect_error) {
    die("Database connection failed");
}

$conn->set_charset("utf8mb4");

$action = isset($_GET["action"]) ? $_GET["action"] : "";

if ($action == "product") {
    $id = intval($_GET["id"]);

    $stmt = $conn->prepare("SELECT product_id, product_name, category, price, description, image FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    header("Content-Type: application/json");
    echo json_encode($product ? $product : array());
    exit;
}

if ($action == "summary") {
    $session_id = session_id();

    $sql = "SELECT c.quantity, p.product_name, p.price
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.session_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $items = array();
    $total = 0;

    while ($row = $result->fetch_assoc()) {
        $row["amount"] = $row["price"] * $row["quantity"];
        $total += $row["amount"];
        $items[] = $row;
    }

    header("Content-Type: application/json");
    echo json_encode(array("items" => $items, "total" => $total));
    exit;
}

$conn->close();
?>
