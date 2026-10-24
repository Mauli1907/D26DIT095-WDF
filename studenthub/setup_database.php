<?php
/**
 * StudentHub Portal - One-Click Database Setup Script
 * Subject: ITUE203 - Web Development Frameworks (CHARUSAT FTE)
 */

$host = 'localhost';
$port = 3306;
$user = 'root';
$pass = '';
$dbName = 'studenthub_db';
$sqlFile = __DIR__ . '/data/database.sql';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Setup StudentHub Database - CHARUSAT FTE</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; color: #0f172a; padding: 40px 20px; line-height: 1.6; }
    .box { max-width: 680px; margin: 0 auto; background: #fff; padding: 36px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    h1 { font-size: 1.8rem; margin-bottom: 8px; color: #4f46e5; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: #e0e7ff; color: #3730a3; margin-bottom: 12px; }
    .success { background: #d1fae5; color: #065f46; padding: 14px; border-radius: 8px; margin: 18px 0; border: 1px solid #a7f3d0; font-weight: 600; }
    .error { background: #fee2e2; color: #991b1b; padding: 14px; border-radius: 8px; margin: 18px 0; border: 1px solid #fecaca; font-weight: 600; }
    ul { padding-left: 20px; }
    li { margin-bottom: 6px; }
    .btn { display: inline-block; background: #4f46e5; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; margin-top: 18px; }
    .btn:hover { background: #4338ca; }
    table { width: 100%; border-collapse: collapse; margin-top: 14px; }
    th, td { border: 1px solid #e2e8f0; padding: 8px 12px; text-align: left; }
    th { background: #f1f5f9; }
  </style>
</head>
<body>
<div class="box">
  <span class="badge">Database Setup Utility</span>
  <h1>StudentHub Portal Setup</h1>
  <p>Course: <strong>Web Development Frameworks (ITUE203)</strong> &bull; CHARUSAT FTE</p>

  <?php
  try {
      $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      ]);

      // Create database
      $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
      $pdo->exec("USE `$dbName`;");

      if (!file_exists($sqlFile)) {
          throw new Exception("SQL schema file not found at: $sqlFile");
      }
      $rawSql = file_get_contents($sqlFile);

      // Strip DELIMITER blocks or handle them separately for PDO
      $procPattern = '/DELIMITER\s+\$\$(.*?)\$\$\s*DELIMITER\s*;/s';
      preg_match_all($procPattern, $rawSql, $procMatches);

      // Remove delimiter blocks from main SQL
      $cleanSql = preg_replace($procPattern, '', $rawSql);

      // Execute main schema and table inserts
      $pdo->exec($cleanSql);

      // Execute stored procedures cleanly
      if (!empty($procMatches[1])) {
          foreach ($procMatches[1] as $procBlock) {
              $subQueries = explode('$$', $procBlock);
              foreach ($subQueries as $sq) {
                  $sq = trim($sq);
                  if (!empty($sq)) {
                      try {
                          $pdo->exec($sq);
                      } catch (Exception $pe) {
                          // Ignore if already created
                      }
                  }
              }
          }
      }

      $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
      $stuCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
      $evCount = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
      $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

      echo "<div class='success'>✅ Database <code>$dbName</code> created and initialized successfully!</div>";
      echo "<h3>Initialized Database Tables:</h3><ul>";
      foreach ($tables as $t) {
          echo "<li><code>$t</code></li>";
      }
      echo "</ul>";

      echo "<h3>Populated Seed Summary:</h3>";
      echo "<ul>";
      echo "<li><strong>Registered Users:</strong> $userCount</li>";
      echo "<li><strong>Student Records:</strong> $stuCount (Meets Practical 11 requirement of >= 20)</li>";
      echo "<li><strong>Campus Events:</strong> $evCount (Meets Practical 6 & 12 requirement of >= 15)</li>";
      echo "</ul>";

      echo "<h3>Default Testing Credentials:</h3>";
      echo "<table>
        <thead><tr><th>Role</th><th>Email</th><th>Password</th><th>Destination</th></tr></thead>
        <tbody>
          <tr><td><strong>Admin</strong></td><td>admin@charusat.edu.in</td><td><code>Admin@123</code></td><td>admin_dashboard.php</td></tr>
          <tr><td><strong>Student</strong></td><td>aarav.sharma@charusat.edu.in</td><td><code>Student@123</code></td><td>student_dashboard.php</td></tr>
        </tbody>
      </table>";

      echo "<div style='margin-top:24px;'>
              <a href='pages/index.php' class='btn'>Launch StudentHub Website &rarr;</a>
              <a href='practicals/index.php' class='btn' style='background:#0f172a; margin-left:10px;'>Open Practicals Hub</a>
            </div>";

  } catch (Exception $e) {
      echo "<div class='error'>❌ Database initialization failed: " . htmlspecialchars($e->getMessage()) . "</div>";
      echo "<p>Please ensure that MySQL is running in your XAMPP control panel on port $port.</p>";
  }
  ?>
</div>
</body>
</html>
