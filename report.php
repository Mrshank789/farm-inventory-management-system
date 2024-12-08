<?php
include('db.php');

// Initialize variables
$reportResult = null;
$grandTotal = 0; // Initialize grand total variable

// Handle Form Submission for Generating Reports
if (isset($_POST['generate_report'])) {
    $report_type = $_POST['report_type'];
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : null;
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $category = isset($_POST['category']) ? $_POST['category'] : null;

    // SQL Query Based on Selected Report Type
    switch ($report_type) {
        case 'inventory':
            if ($start_date && $end_date) {
                $stmt = $conn->prepare("SELECT *, (price * quantity) AS total_price FROM products WHERE expiration_date BETWEEN ? AND ?");
                $stmt->bind_param("ss", $start_date, $end_date);
                $stmt->execute();
                $reportResult = $stmt->get_result();
                $reportLabel = "Inventory Report";
                $stmt->close();
            }
            break;
        case 'sales':
            if ($start_date && $end_date) {
                $stmt = $conn->prepare("SELECT *, price AS total_price FROM orders WHERE date BETWEEN ? AND ?");
                $stmt->bind_param("ss", $start_date, $end_date);
                $stmt->execute();
                $reportResult = $stmt->get_result();
                $reportLabel = "Sales Report";
                $stmt->close();
            }
            break;
        case 'product':
            if ($name) {
                $stmt = $conn->prepare("SELECT *, (price * quantity) AS total_price FROM products WHERE id=?");
                $stmt->bind_param("i", $name);
                $stmt->execute();
                $reportResult = $stmt->get_result();
                $reportLabel = "Product-Specific Report";
                $stmt->close();
            }
            break;
        case 'category':
            if ($category) {
                $stmt = $conn->prepare("SELECT *, (price * quantity) AS total_price FROM products WHERE category=?");
                $stmt->bind_param("s", $category);
                $stmt->execute();
                $reportResult = $stmt->get_result();
                $reportLabel = "Category-Specific Report";
                $stmt->close();
            }
            break;
        default:
            $reportResult = null;
            break;
    }
}

// Fetch Products for Product-Specific Report Dropdown
$productResult = $conn->query("SELECT id, name FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Generate Reports</h1>
        <!-- Back to Dashboard Button -->
        <div class="dashboard-button">
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </div>
        <!-- Report Generation Form -->
        <h2>Select Report Criteria</h2>
        <form action="report.php" method="post">
            <label for="report_type">Report Type:</label>
            <select name="report_type" id="report_type" required>
                <option value="" disabled selected>Select Report Type</option>
                <option value="inventory">Inventory Report (By Date Range)</option>
                <option value="sales">Sales Report (By Date Range)</option>
                <option value="product">Product-Specific Report</option>
                <option value="category">Category-Specific Report</option>
            </select>
            <div id="date-range" class="conditional-input">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date">
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date">
            </div>
            <div id="product-id" class="conditional-input">
                <label for="name">Select Product:</label>
                <select name="name" id="name">
                    <option value="" disabled selected>Select Product</option>
                    <?php while ($row = $productResult->fetch_assoc()) { ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div id="category" class="conditional-input">
                <label for="category">Product Category:</label>
                <input type="text" name="category" id="category">
            </div>
            <button type="submit" name="generate_report">Generate Report</button>
        </form>
        <!-- Display Report Results -->
        <?php if ($reportResult) { ?>
            <h2><?php echo $reportLabel; ?> Results</h2>
            <!-- Print Button -->
            <button onclick="printReport()">Print Report</button>
            <div id="report-section">
                <table>
                    <tr>
                        <!-- Add relevant table headers based on report type -->
                        <?php if ($report_type == 'inventory' || $report_type == 'product' || $report_type == 'category') { ?>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Expiration Date</th>
                            <th>Price</th>
                            <th>Total Price</th>
                        <?php } elseif ($report_type == 'sales') { ?>
                            <th>Order ID</th>
                            <th>Product ID</th>
                            <th>Quantity</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Price</th>
                            <th>Total Price</th>
                        <?php } ?>
                    </tr>
                    <?php 
                    while ($row = $reportResult->fetch_assoc()) { 
                        $grandTotal += isset($row['total_price']) ? $row['total_price'] : 0; // Accumulate total price
                    ?>
                        <tr>
                            <!-- Display relevant data based on report type -->
                            <?php if ($report_type == 'inventory' || $report_type == 'product' || $report_type == 'category') { ?>
                                <td><?php echo isset($row['id']) ? $row['id'] : ''; ?></td>
                                <td><?php echo isset($row['name']) ? $row['name'] : ''; ?></td>
                                <td><?php echo isset($row['category']) ? $row['category'] : ''; ?></td>
                                <td><?php echo isset($row['quantity']) ? $row['quantity'] : ''; ?></td>
                                <td><?php echo isset($row['expiration_date']) ? $row['expiration_date'] : ''; ?></td>
                                <td><?php echo isset($row['price']) ? number_format($row['price'], 2) : ''; ?></td>
                                <td><?php echo isset($row['total_price']) ? number_format($row['total_price'], 2) : ''; ?></td>
                            <?php } elseif ($report_type == 'sales') { ?>
                                <td><?php echo isset($row['id']) ? $row['id'] : ''; ?></td>
                                <td><?php echo isset($row['product_id']) ? $row['product_id'] : ''; ?></td>
                                <td><?php echo isset($row['quantity']) ? $row['quantity'] : ''; ?></td>
                                <td><?php echo isset($row['customer']) ? $row['customer'] : ''; ?></td>
                                <td><?php echo isset($row['date']) ? $row['date'] : ''; ?></td>
                                <td><?php echo isset($row['price']) ? number_format($row['price'], 2) : ''; ?></td>
                                <td><?php echo isset($row['total_price']) ? number_format($row['total_price'], 2) : ''; ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="<?php echo ($report_type == 'sales') ? '6' : '7'; ?>" style="text-align: right;"><strong>Grand Total:</strong></td>
                        <td><strong><?php echo number_format($grandTotal, 2); ?></strong></td> <!-- Display grand total -->
                    </tr>
                </table>
            </div>
        <?php } else { ?>
            <p>No results found for the selected criteria.</p>
        <?php } ?>
    </div>
    <!-- JavaScript to dynamically show/hide inputs based on selected report type -->
    <script>
        const reportTypeSelect = document.getElementById('report_type');
        const dateRangeInputs = document.getElementById('date-range');
        const productIdInput = document.getElementById('product-id');
        const categoryInput = document.getElementById('category');

        function updateFormVisibility() {
            const reportType = reportTypeSelect.value;
            dateRangeInputs.style.display = (reportType === 'inventory' || reportType === 'sales') ? 'block' : 'none';
            productIdInput.style.display = (reportType === 'product') ? 'block' : 'none';
            categoryInput.style.display = (reportType === 'category') ? 'block' : 'none';
        }

        reportTypeSelect.addEventListener('change', updateFormVisibility);
        updateFormVisibility(); // Initialize form visibility based on default selection

        // Print Report Function
        function printReport() {
    const reportSection = document.getElementById('report-section');
    const reportLabel = "<?php echo $reportLabel; ?>";  // Pass the PHP variable to JavaScript

    // Open a new window for printing
    const newWindow = window.open('', '_blank');
    newWindow.document.write('<html><head><title>Print Report</title>');
    newWindow.document.write('<style>body { font-family: Arial, sans-serif; }</style>');
    newWindow.document.write('</head><body>');
    newWindow.document.write('<h1>' + reportLabel + '</h1>');  // Display the report label dynamically
    newWindow.document.write(reportSection.innerHTML);  // Copy the report content
    newWindow.document.write('</body></html>');

    // Close the document and trigger the print dialog
    newWindow.document.close();
    newWindow.print();
}

    </script>
</body>
</html>
