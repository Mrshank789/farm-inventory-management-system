<?php
include('db.php');

// Initialize variables for search results
$searchResults = [];
$searchQuery = '';

// search request
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
    $searchTerm = "%" . $searchQuery . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $searchResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get Product Count
$productCount = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];

// Get Total Inventory Quantity
$totalInventory = $conn->query("SELECT SUM(quantity) as total FROM products")->fetch_assoc()['total'];

// Get Total Orders
$orderCount = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];

// Get Total Sales
$totalSales = $conn->query("SELECT SUM(quantity) as total FROM orders")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Inventory Management Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Farm Inventory Management Dashboard</h1>

        <!-- Search Bar -->
        <form action="" method="GET">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Display search results  -->
        <?php if (!empty($searchResults)): ?>
            <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($searchResults as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($product['price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($_GET['search'])): ?>
            <p>No results found for "<?php echo htmlspecialchars($searchQuery); ?>"</p>
        <?php endif; ?>

        <div class="dashboard">
            <div class="dashboard-item">
                <h2>Total Products</h2>
                <p><?php echo $productCount; ?></p>
            </div>
            <div class="dashboard-item">
                <h2>Total Inventory</h2>
                <p><?php echo $totalInventory; ?> items</p>
            </div>
            <div class="dashboard-item">
                <h2>Total Orders</h2>
                <p><?php echo $orderCount; ?></p>
            </div>
            <div class="dashboard-item">
                <h2>Total Sales</h2>
                <p><?php echo $totalSales; ?> items</p>
            </div>
        </div>

        <div class="dashboard-links">
            <a href="product.php">Manage Products</a>
            <a href="sales.php">Sales & Orders</a>
            <a href="report.php">Generate Reports</a>
        </div>

        <!-- Add Fruit Images Section -->
        <div class="fruit-images">
            <h2>Our Fresh Fruits</h2>
            <div class="fruit-gallery">
                <img src="images/bananas.jfif" alt="Bananas" class="fruit-image">
                <img src="images/mangos.jfif" alt="Mangoes" class="fruit-image">
                <img src="images/watermelon.jfif" alt="Watermelons" class="fruit-image">
                <img src="images/passion.jfif" alt="Passion Fruit" class="fruit-image">
            </div>
        </div>
    </div>

    <button class="logout-button" onclick="logout()">Logout</button>

    <script>
        function logout() {
          
        window.location.href = "log in.php";
        }
    </script>
        
</body>
</html>
