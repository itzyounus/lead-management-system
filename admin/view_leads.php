<?php
session_start();

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Rest of your existing code...
$host = 'localhost';
// ... continue with existing code

// Database configuration
$host = 'localhost';
$dbname = 'lead_management';     // ✅ Your actual database name
$username = 'root';               // ✅ XAMPP default username
$password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch all leads
    $stmt = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC");
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .stats h2 {
            font-size: 36px;
            margin-bottom: 5px;
        }

        .stats p {
            opacity: 0.9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background: #f8f9fa;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        td {
            color: #333;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .date {
            color: #999;
            font-size: 14px;
        }

        .export-btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .export-btn:hover {
            background: #5568d3;
        }

        .action-btn {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .action-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Lead Dashboard</h1>
        <div style="text-align: right; margin-bottom: 20px;">
            <a href="?logout=1" style="color: #e74c3c; text-decoration: none; font-weight: 600;">🚪 Logout</a>
        </div>
        <p style="color: #666; margin-bottom: 20px;">View and manage all submitted leads</p>

        <div class="stats">
            <h2><?php echo count($leads); ?></h2>
            <p>Total Leads Captured</p>
        </div>

        <button class="export-btn" onclick="exportToCSV()">📥 Export to CSV</button>

        <?php if (count($leads) > 0): ?>
            <table id="leadsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Phone</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($lead['id']); ?></td>
                            <td><?php echo htmlspecialchars($lead['name']); ?></td>
                            <td><?php echo htmlspecialchars($lead['email']); ?></td>
                            <td><?php echo htmlspecialchars($lead['company']); ?></td>
                            <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                            <td class="date"><?php echo date('M d, Y H:i', strtotime($lead['created_at'])); ?></td>
                            <td>
                                <button class="action-btn" onclick="deleteLead(<?php echo $lead['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <p>📭 No leads yet. They will appear here once submitted.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function exportToCSV() {
            const table = document.getElementById('leadsTable');
            let csv = [];
            
            // Get headers
            const headers = Array.from(table.querySelectorAll('thead th'))
                .map(th => th.textContent.trim());
            csv.push(headers.join(','));
            
            // Get data
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cols = Array.from(row.querySelectorAll('td'))
                    .slice(0, -1) // Exclude action column
                    .map(td => `"${td.textContent.trim()}"`);
                csv.push(cols.join(','));
            });
            
            // Download
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'leads_' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
        }

        function deleteLead(id) {
            if (confirm('Are you sure you want to delete this lead?')) {
                fetch('delete_lead.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting lead');
                    }
                });
            }
        }
    </script>
</body>
</html>