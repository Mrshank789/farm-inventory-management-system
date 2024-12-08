<?php
include('db.php');

// Initialize variables
$product_id = "";
$name = "";
$category = "";
$quantity = "";
$price = "";
$expiration_date = "";

// Handle Update Product form submission
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $expiration_date = $_POST['expiration_date'];

    // Update the product in the database
    $update_sql = "UPDATE products SET name = ?, category = ?, quantity = ?, price = ?, expiration_date = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssidsi", $name, $category, $quantity, $price, $expiration_date, $product_id);

    if ($stmt->execute()) {
        echo "Product updated successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Handle Delete Product form submission
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];

    // Delete the product from the database
    $delete_sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        echo "Product deleted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Fetch all products to display in the inventory
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// Handle Edit button click (retrieve product details)
if (isset($_GET['edit_id'])) {
    $product_id = $_GET['edit_id'];
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        $name = $product['name'];
        $category = $product['category'];
        $quantity = $product['quantity'];
        $price = $product['price'];
        $expiration_date = $product['expiration_date'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // Function to make the input fields editable
        function enableEdit(productId) {
            document.getElementById('name' + productId).disabled = false;
            document.getElementById('category' + productId).disabled = false;
            document.getElementById('quantity' + productId).disabled = false;
            document.getElementById('price' + productId).disabled = false;
            document.getElementById('expiration_date' + productId).disabled = false;
            document.getElementById('save' + productId).style.display = 'inline';
            document.getElementById('edit' + productId).style.display = 'none';
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Manage Inventory</h1>
        <div class="dashboard-button">
            <a href="dashboard.php" class="btn">Back to Dashboard</a>

        <!-- Add/Edit Product Form -->
        <h2><?php echo $product_id ? 'Edit Product' : 'Add New Product'; ?></h2>
        <form action="product.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">

            <label for="name">Product Name:</label>
            <input type="text" name="name" id="name" value="<?php echo $name; ?>" required>

            <label for="category">Category:</label>
            <input type="text" name="category" id="category" value="<?php echo $category; ?>" required>

            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" value="<?php echo $quantity; ?>" required min="1">

            <label for="price">Price (in Ksh):</label>
            <input type="number" name="price" id="price" value="<?php echo $price; ?>" required step="0.01" min="0">

            <label for="expiration_date">Expiration Date:</label>
            <input type="date" name="expiration_date" id="expiration_date" value="<?php echo $expiration_date; ?>">

            <button type="submit" name="<?php echo $product_id ? 'update_product' : 'add_product'; ?>">
                <?php echo $product_id ? 'Update Product' : 'Add Product'; ?>
            </button>
        </form>

        <!-- Display Inventory List -->
        <h2>Inventory List</h2>
        <?php
        if ($result->num_rows > 0) {
            echo "<table><tr><th>ID</th><th>Name</th><th>Category</th><th>Quantity</th><th>Price (Ksh)</th><th>Expiration Date</th><th>Actions</th></tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<form id='editForm" . $row["id"] . "' action='product.php' method='post'>";
                echo "<td>" . $row["id"]. "</td>";
                echo "<td><input type='text' id='name" . $row["id"] . "' name='name' value='" . $row["name"] . "' disabled></td>";
                echo "<td><input type='text' id='category" . $row["id"] . "' name='category' value='" . $row["category"] . "' disabled></td>";
                echo "<td><input type='number' id='quantity" . $row["id"] . "' name='quantity' value='" . $row["quantity"] . "' disabled min='1'></td>";
                echo "<td><input type='number' id='price" . $row["id"] . "' name='price' value='" . $row["price"] . "' disabled step='0.01' min='0'></td>";
                echo "<td><input type='date' id='expiration_date" . $row["id"] . "' name='expiration_date' value='" . $row["expiration_date"] . "' disabled></td>";
                echo "<td>";
                echo "<button type='button' id='edit" . $row["id"] . "' onclick='enableEdit(" . $row["id"] . ")'>Edit</button>";
                echo "<button type='submit' id='save" . $row["id"] . "' name='update_product' style='display:none;'>Save</button>";
                echo "<form action='product.php' method='post' style='display:inline;'>";
                echo "<input type='hidden' name='product_id' value='" . $row["id"] . "'>";
                echo "<button type='submit' name='delete_product' style='background-color:red; color:white;'>Delete</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No products found in inventory.";
        }
        ?>
    </div>
</body>
</html>
