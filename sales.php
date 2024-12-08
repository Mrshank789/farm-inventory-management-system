<?php
include('db.php');

// Handle form submission
if (isset($_POST['add_order'])) {
    $customer = $_POST['customer'];
    $date = $_POST['date'];
    $products = $_POST['product_id'];
    $quantities = $_POST['quantity'];

    $orderReceipts = "";
    $grandTotal = 0; // Initialize grand total

    for ($i = 0; $i < count($products); $i++) {
        $product_id = $products[$i];
        $quantity = $quantities[$i];

        // Prepare and bind for product details
        $productStmt = $conn->prepare("SELECT name, price, quantity FROM products WHERE id = ?");
        $productStmt->bind_param("i", $product_id);
        $productStmt->execute();
        $product = $productStmt->get_result()->fetch_assoc();

        if ($product['quantity'] >= $quantity) {
            // Calculate total price
            $price = $product['price'];
            $total = $price * $quantity;
            $grandTotal += $total; // Add to grand total

            // Insert order into the database
            $stmt = $conn->prepare("INSERT INTO orders (product_id, quantity, customer, date, price) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iissi", $product_id, $quantity, $customer, $date, $total); // Bind total price

            if ($stmt->execute()) {
                $order_id = $conn->insert_id;

                // Update quantity in products table
                $newQuantity = $product['quantity'] - $quantity;
                $updateQuantityStmt = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
                $updateQuantityStmt->bind_param("ii", $newQuantity, $product_id);
                $updateQuantityStmt->execute();

                // Append each item to the order receipt
                $orderReceipts .= "
                <div class='receipt-item'>
                    <p><strong>Order ID:</strong> $order_id</p>
                    <p><strong>Product:</strong> " . htmlspecialchars($product['name']) . "</p>
                    <p><strong>Quantity:</strong> " . htmlspecialchars($quantity) . "</p>
                    <p><strong>Price (Ksh):</strong> " . htmlspecialchars($price) . "</p>
                    <p><strong>Total Cost (Ksh):</strong> " . htmlspecialchars($total) . "</p>
                </div>";
            } else {
                echo "Error inserting order: " . $conn->error;
            }
        } else {
            echo "Insufficient quantity for product " . htmlspecialchars($product['name']) . ".<br>";
        }
    }

    // Display the receipt for all items
    if ($orderReceipts) {
        echo "<div id='receipt'><h2>Receipt</h2><p><strong>Customer:</strong> " . htmlspecialchars($customer) . "</p><p><strong>Date:</strong> " . htmlspecialchars($date) . "</p>" . $orderReceipts;
        echo "<h3>Grand Total: Ksh " . htmlspecialchars($grandTotal) . "</h3>"; // Display grand total
        echo "</div>";
        echo "<button onclick='printReceipt()'>Print Receipt</button>";
    }
}

// Fetch products for dropdown
$productResult = $conn->query("SELECT id, name FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Order Form</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function addProduct() {
            const productSelect = document.querySelector('.product-group').cloneNode(true);
            document.getElementById('product-list').appendChild(productSelect);
        }

        function printReceipt() {
            var receiptContent = document.getElementById('receipt').innerHTML;
            var originalContent = document.body.innerHTML;

            document.body.innerHTML = receiptContent;
            window.print();
            document.body.innerHTML = originalContent;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Record a New Sale</h1>
        <div class="dashboard-button">
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </div>

        <form action="sales.php" method="post">
            <label for="customer">Customer:</label>
            <input type="text" name="customer" id="customer" required>
            <label for="date">Date:</label>
            <input type="date" name="date" id="date" required>

            <div id="product-list">
                <div class="product-group">
                    <label for="product_id">Product:</label>
                    <select name="product_id[]" required>
                        <?php while ($row = $productResult->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity[]" required min="1">
                </div>
            </div>
            <button type="button" onclick="addProduct()">Add Another Product</button>
            <button type="submit" name="add_order">Add Order</button>
        </form>
    </div>
</body>
</html>
