<?php
// Test Database Connection for Art Gallery Management System
// Place this file in your root directory and access via browser

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test - Art Gallery</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .test-section {
            margin: 20px 0;
            padding: 20px;
            border-radius: 12px;
            border-left: 5px solid #667eea;
        }
        .success {
            background: linear-gradient(135deg, #d5f4e6, #a8e6cf);
            border-left-color: #27ae60;
        }
        .error {
            background: linear-gradient(135deg, #fadbd8, #f5b7b1);
            border-left-color: #e74c3c;
        }
        .warning {
            background: linear-gradient(135deg, #fdeaa7, #f9e79f);
            border-left-color: #f39c12;
        }
        .info {
            background: linear-gradient(135deg, #d6eaf8, #aed6f1);
            border-left-color: #3498db;
        }
        .test-title {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .test-result {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-success { background: #27ae60; color: white; }
        .status-error { background: #e74c3c; color: white; }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 Art Gallery Database Connection Test</h1>

        <?php
        // Initialize variables
        $conn = null;
        $database = null;
        $configExists = false;
        
        // Test 1: Check if config file exists
        echo '<div class="test-section">';
        echo '<div class="test-title">📁 Test 1: Configuration File Check</div>';
        
        if (file_exists('config/database.php')) {
            echo '<div class="success">';
            echo '<div class="test-result">✅ config/database.php file found</div>';
            $configExists = true;
            try {
                require_once 'config/database.php';
                echo '<div class="test-result">✅ Configuration file loaded successfully</div>';
            } catch (Exception $e) {
                echo '<div class="test-result">❌ Error loading config: ' . $e->getMessage() . '</div>';
                $configExists = false;
            }
            echo '</div>';
        } else {
            echo '<div class="error">';
            echo '<div class="test-result">❌ config/database.php file not found!</div>';
            echo '<div class="test-result">Please make sure the config folder and database.php file exist.</div>';
            echo '</div>';
        }
        echo '</div>';

        // Test 2: Check PHP Extensions
        echo '<div class="test-section info">';
        echo '<div class="test-title">🔧 Test 2: PHP Extensions Check</div>';
        
        $extensions = ['pdo', 'pdo_mysql', 'json'];
        $extensionStatus = [];
        $allExtensionsLoaded = true;
        
        foreach ($extensions as $ext) {
            $loaded = extension_loaded($ext);
            if (!$loaded) $allExtensionsLoaded = false;
            $extensionStatus[] = [
                'extension' => $ext,
                'status' => $loaded ? 'Loaded' : 'Missing',
                'class' => $loaded ? 'status-success' : 'status-error'
            ];
        }
        
        echo '<table>';
        echo '<tr><th>Extension</th><th>Status</th></tr>';
        foreach ($extensionStatus as $ext) {
            echo "<tr><td>{$ext['extension']}</td><td><span class='status-badge {$ext['class']}'>{$ext['status']}</span></td></tr>";
        }
        echo '</table>';
        
        if (!$allExtensionsLoaded) {
            echo '<div class="error">';
            echo '<div class="test-result">❌ Some required PHP extensions are missing!</div>';
            echo '</div>';
        }
        echo '</div>';

        // Test 3: Database Connection
        echo '<div class="test-section">';
        echo '<div class="test-title">🗄️ Test 3: Database Connection</div>';
        
        if (!$configExists || !$allExtensionsLoaded) {
            echo '<div class="error">';
            echo '<div class="test-result">❌ Cannot test database connection - prerequisites not met</div>';
            echo '</div>';
        } else {
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                if ($conn && $conn instanceof PDO) {
                    echo '<div class="success">';
                    echo '<div class="test-result">✅ Database connection successful!</div>';
                    
                    // Get database info
                    $stmt = $conn->query("SELECT DATABASE() as db_name, VERSION() as version, USER() as user");
                    $info = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo '<table>';
                    echo '<tr><th>Property</th><th>Value</th></tr>';
                    echo "<tr><td>Database Name</td><td>" . ($info['db_name'] ?: 'Not selected') . "</td></tr>";
                    echo "<tr><td>MySQL Version</td><td>{$info['version']}</td></tr>";
                    echo "<tr><td>Connected User</td><td>{$info['user']}</td></tr>";
                    echo '</table>';
                    echo '</div>';
                } else {
                    throw new Exception("Connection returned invalid object");
                }
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<div class="test-result">❌ Database connection failed!</div>';
                echo '<div class="test-result">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<div class="test-result">Please check your database credentials in config/database.php</div>';
                echo '</div>';
                $conn = null; // Reset connection on failure
            }
        }
        echo '</div>';

        // Test 4: Check Tables (only if connection successful)
        echo '<div class="test-section">';
        echo '<div class="test-title">📋 Test 4: Database Tables Check</div>';
        
        if (!$conn) {
            echo '<div class="error">';
            echo '<div class="test-result">❌ Cannot check tables - no database connection</div>';
            echo '</div>';
        } else {
            $requiredTables = [
                'customers', 'artists', 'rooms', 'staff', 
                'exhibitions', 'artworks', 'tickets', 'purchases'
            ];
            
            try {
                $stmt = $conn->query("SHOW TABLES");
                $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                echo '<table>';
                echo '<tr><th>Table Name</th><th>Status</th><th>Record Count</th></tr>';
                
                foreach ($requiredTables as $table) {
                    $exists = in_array($table, $existingTables);
                    $status = $exists ? 'Exists' : 'Missing';
                    $statusClass = $exists ? 'status-success' : 'status-error';
                    $count = 'N/A';
                    
                    if ($exists) {
                        try {
                            $countStmt = $conn->query("SELECT COUNT(*) FROM `$table`");
                            $count = $countStmt->fetchColumn();
                        } catch (Exception $e) {
                            $count = 'Error: ' . $e->getMessage();
                        }
                    }
                    
                    echo "<tr>";
                    echo "<td>$table</td>";
                    echo "<td><span class='status-badge $statusClass'>$status</span></td>";
                    echo "<td>$count</td>";
                    echo "</tr>";
                }
                echo '</table>';
                
                $missingTables = array_diff($requiredTables, $existingTables);
                if (!empty($missingTables)) {
                    echo '<div class="warning">';
                    echo '<div class="test-result">⚠️ Missing tables: ' . implode(', ', $missingTables) . '</div>';
                    echo '<div class="test-result">Please run the database/schema.sql script to create missing tables.</div>';
                    echo '</div>';
                } else {
                    echo '<div class="success">';
                    echo '<div class="test-result">✅ All required tables exist!</div>';
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<div class="test-result">❌ Error checking tables: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '</div>';
            }
        }
        echo '</div>';

        // Test 5: API Endpoints Test
        echo '<div class="test-section">';
        echo '<div class="test-title">🔌 Test 5: API Endpoints Check</div>';
        
        $apiFiles = [
            'base_api.php', 'customers.php', 'artists.php', 'rooms.php',
            'staff.php', 'exhibitions.php', 'artworks.php', 'tickets.php',
            'purchases.php', 'dashboard.php', 'index.php'
        ];
        
        echo '<table>';
        echo '<tr><th>API File</th><th>Status</th></tr>';
        
        $allApiFilesExist = true;
        foreach ($apiFiles as $file) {
            $exists = file_exists("api/$file");
            if (!$exists) $allApiFilesExist = false;
            $status = $exists ? 'Found' : 'Missing';
            $statusClass = $exists ? 'status-success' : 'status-error';
            
            echo "<tr>";
            echo "<td>api/$file</td>";
            echo "<td><span class='status-badge $statusClass'>$status</span></td>";
            echo "</tr>";
        }
        echo '</table>';
        
        if (!$allApiFilesExist) {
            echo '<div class="warning">';
            echo '<div class="test-result">⚠️ Some API files are missing. Please upload all API files to the api/ folder.</div>';
            echo '</div>';
        }
        echo '</div>';

        // Test 6: Simple API Test (only if everything else works)
        echo '<div class="test-section">';
        echo '<div class="test-title">🚀 Test 6: Basic API Test</div>';
        
        if (!$conn || !file_exists('api/base_api.php')) {
            echo '<div class="error">';
            echo '<div class="test-result">❌ Cannot test API - database connection or base API file missing</div>';
            echo '</div>';
        } else {
            echo '<div class="info">';
            echo '<div class="test-result">📊 API files are present and database is connected.</div>';
            echo '<div class="test-result">✅ You can now test the APIs manually by visiting:</div>';
            echo '<div class="test-result">• <a href="api/customers.php" target="_blank">api/customers.php</a></div>';
            echo '<div class="test-result">• <a href="api/dashboard.php" target="_blank">api/dashboard.php</a></div>';
            echo '</div>';
        }
        echo '</div>';

        // Final Status
        $overallStatus = $configExists && $allExtensionsLoaded && $conn && $allApiFilesExist;
        
        echo '<div class="test-section ' . ($overallStatus ? 'success' : 'warning') . '">';
        echo '<div class="test-title">' . ($overallStatus ? '🎉 System Ready!' : '⚠️ Setup Incomplete') . '</div>';
        
        if ($overallStatus) {
            echo '<div class="test-result">✅ All tests passed! Your Art Gallery Management System is ready to use.</div>';
            echo '<div style="text-align: center; margin-top: 20px;">';
            echo '<a href="index.html" class="btn">🎨 Go to Art Gallery System</a>';
            echo '<a href="api/dashboard.php" class="btn">📊 Test Dashboard API</a>';
            echo '</div>';
        } else {
            echo '<div class="test-result">❌ Some issues need to be resolved before the system can work properly.</div>';
            echo '<div class="test-result">Please fix the errors shown above and run this test again.</div>';
        }
        echo '</div>';
        ?>
    </div>
</body>
</html>
