<?php
session_start();

$conn = new mysqli("localhost", "root", "", "eazycart");

if ($conn->connect_error) {
    die("Database connection failed");
}

$conn->set_charset("utf8mb4");

$action = isset($_GET["action"]) ? $_GET["action"] : "";

if ($action == "total") {
    $session_id = session_id();

    $sql = "SELECT c.quantity, p.price
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.session_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $total = 0;

    while ($row = $result->fetch_assoc()) {
        $total += $row["quantity"] * $row["price"];
    }

    header("Content-Type: application/json");
    echo json_encode(array("total" => $total));
    exit;
}

if ($action == "place") {
    $session_id = session_id();

    $name = $_POST["customer_name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $city = $_POST["city"];
    $pincode = $_POST["pincode"];
    $payment = $_POST["payment_method"];

    $sql = "SELECT c.product_id, c.quantity, p.price
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.session_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "Cart is empty";
        exit;
    }

    $items = array();
    $total = 0;

    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
        $total += $row["quantity"] * $row["price"];
    }

    $order_number = "EZ" . date("YmdHis");

    $order_status = "Order Placed";

    $insert = $conn->prepare("INSERT INTO orders
        (order_number, session_id, customer_name, email, phone, address, city, pincode, payment_method, total_amount, order_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $insert->bind_param(
        "sssssssssds",
        $order_number,
        $session_id,
        $name,
        $email,
        $phone,
        $address,
        $city,
        $pincode,
        $payment,
        $total,
        $order_status
    );

    if (!$insert->execute()) {
        echo "Order could not be placed";
        exit;
    }

    $order_id = $conn->insert_id;

    $item_insert = $conn->prepare("INSERT INTO order_items
        (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

    foreach ($items as $item) {
        $item_insert->bind_param(
            "iiid",
            $order_id,
            $item["product_id"],
            $item["quantity"],
            $item["price"]
        );
        $item_insert->execute();
    }

    $delete = $conn->prepare("DELETE FROM cart WHERE session_id = ?");
    $delete->bind_param("s", $session_id);
    $delete->execute();

    echo $order_number;
    exit;
}

if ($action == "tracking") {
    $session_id = session_id();

    $stmt = $conn->prepare("SELECT order_number, customer_name, total_amount, order_status
                            FROM orders
                            WHERE session_id = ?
                            ORDER BY order_id DESC LIMIT 1");

    $stmt->bind_param("s", $session_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    header("Content-Type: application/json");
    echo json_encode($order ? $order : array());
    exit;
}

$conn->close();
?>
