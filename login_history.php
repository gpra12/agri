<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
require_once "config.php";

// Define variables
$login_history = [];

// Get login history
$sql = "SELECT lh.id, lh.user_id, lh.username, lh.email, lh.ip_address, lh.status, lh.login_time 
        FROM login_history lh 
        ORDER BY lh.login_time DESC 
        LIMIT 100";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $login_history[] = $row;
    }
    $result->free();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login History - TREA AI</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/mega-menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .history-table th, .history-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .history-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .history-table tr:hover {
            background-color: #f1f1f1;
        }
        .status-success {
            color: green;
            font-weight: bold;
        }
        .status-failed {
            color: red;
        }
        .filters {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .filters select, .filters input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .filters button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .filters button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1><span class="highlight">TREA</span> AI</h1>
            </div>
            <nav>
                <input type="checkbox" id="check">
                <label for="check" class="checkbtn">
                    <i class="fas fa-bars"></i>
                </label>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="login_history.php" class="active">Login History</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="container" style="padding: 40px 20px;">
        <h2>Login History</h2>
        <p>View the login history for all users in the system.</p>
        
        <div class="filters">
            <input type="text" id="searchInput" placeholder="Search by username or email">
            <select id="statusFilter">
                <option value="">All Statuses</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
                <option value="failed_password">Failed Password</option>
                <option value="failed_email">Failed Email</option>
                <option value="system_error">System Error</option>
            </select>
            <button onclick="filterTable()">Filter</button>
        </div>
        
        <div class="table-responsive">
            <table class="history-table" id="historyTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>IP Address</th>
                        <th>Status</th>
                        <th>Login Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($login_history)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No login history found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($login_history as $entry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['id']); ?></td>
                                <td><?php echo htmlspecialchars($entry['username'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($entry['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($entry['ip_address']); ?></td>
                                <td class="<?php echo $entry['status'] === 'success' ? 'status-success' : 'status-failed'; ?>">
                                    <?php echo htmlspecialchars($entry['status']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($entry['login_time']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section about">
                    <h2><span class="highlight">TREA</span> AI</h2>
                    <p>Secure login and registration system with history tracking.</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> TREA AI | All Rights Reserved</p>
            </div>
        </div>
    </footer>

    <script>
        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const table = document.getElementById('historyTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) { // Start from 1 to skip header row
                const tdUser = tr[i].getElementsByTagName('td')[1];
                const tdEmail = tr[i].getElementsByTagName('td')[2];
                const tdStatus = tr[i].getElementsByTagName('td')[4];
                
                if (tdUser && tdEmail && tdStatus) {
                    const userText = tdUser.textContent || tdUser.innerText;
                    const emailText = tdEmail.textContent || tdEmail.innerText;
                    const statusText = tdStatus.textContent || tdStatus.innerText;
                    
                    const matchesSearch = userText.toUpperCase().indexOf(filter) > -1 || 
                                         emailText.toUpperCase().indexOf(filter) > -1;
                    const matchesStatus = statusFilter === '' || statusText.indexOf(statusFilter) > -1;
                    
                    if (matchesSearch && matchesStatus) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            }
        }
    </script>
</body>
</html>