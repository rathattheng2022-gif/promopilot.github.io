<?php
session_start();
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header("Location: loginAdmin.php");
    exit();
}
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';

$conn = new mysqli("localhost", "root", "", "projectui", 3306);
if ($conn->connect_error) {
    die("can't connect to databases");
}

$totalDeals = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM coupons");
if ($result && $row = $result->fetch_assoc()) {
    $totalDeals = (int)$row['total'];
}

$coupons = [];
$result = $conn->query("SELECT id, name, disamount, discode FROM coupons ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $coupons[] = $row;
    }
}

// Simple breakdown: how many deals per company
$byCompany = [];
foreach ($coupons as $c) {
    $key = $c['name'] !== '' ? $c['name'] : 'Unnamed';
    $byCompany[$key] = ($byCompany[$key] ?? 0) + 1;
}
arsort($byCompany);
$maxByCompany = !empty($byCompany) ? max($byCompany) : 1;
?>
<!DOCTYPE html>
<html lang="en">

<head><?php include 'head.php'; ?>
    <link rel="stylesheet" href="admin.css" />
    <style>
        .admin-navbar {
            background: linear-gradient(135deg, #040d06 0%, #064431 100%);
            border-bottom: 1px solid rgba(16, 200, 117, 0.2);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .admin-navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .admin-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #10c875;
            text-decoration: none;
            font-weight: 600;
            font-size: 18px;
        }

        .admin-logo:hover {
            color: #d5ff88;
        }

        .admin-user-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            color: #d5ff88;
            font-weight: 600;
            margin: 0;
        }

        .user-role {
            color: rgba(255, 255, 255, 0.45);
            font-size: 12px;
            margin: 0;
        }

        .logout-btn {
            background: #10c875;
            color: #040d06;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background: #0db567;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 200, 117, 0.3);
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 1.5rem;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: #10c875;
        }

        /* Stat cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .stat-card {
            background: rgba(6, 68, 49, 0.5);
            border: 1px solid rgba(16, 200, 117, 0.2);
            border-radius: 16px;
            padding: 1.5rem;
        }

        .stat-card .stat-label {
            color: rgba(255, 255, 255, 0.45);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .stat-card .stat-value {
            color: #d5ff88;
            font-size: 2.2rem;
            font-weight: 700;
        }

        /* Notice banner */
        .notice-banner {
            margin-top: 2.5rem;
            background: rgba(213, 255, 136, 0.06);
            border: 1px solid rgba(213, 255, 136, 0.25);
            border-radius: 12px;
            padding: 14px 18px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            line-height: 1.5;
        }

        .notice-banner strong {
            color: #d5ff88;
        }

        /* Breakdown bars */
        .breakdown-section {
            margin-top: 3rem;
        }

        .breakdown-section h2 {
            color: #d5ff88;
            margin-bottom: 1rem;
        }

        .bar-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .bar-label {
            width: 160px;
            flex-shrink: 0;
            color: #fff;
            font-size: 14px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .bar-track {
            flex: 1;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 8px;
            height: 18px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #10c875, #d5ff88);
            border-radius: 8px;
        }

        .bar-count {
            width: 28px;
            text-align: right;
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            flex-shrink: 0;
        }

        /* Deals table (reuse style) */
        .deals-table-wrap {
            margin-top: 3rem;
        }

        .deals-table-wrap h2 {
            color: #d5ff88;
            margin-bottom: 1rem;
        }

        table.deals-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(6, 68, 49, 0.35);
            border: 1px solid rgba(16, 200, 117, 0.2);
            border-radius: 12px;
            overflow: hidden;
        }

        table.deals-table th,
        table.deals-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid rgba(16, 200, 117, 0.15);
            color: #fff;
            font-size: 14px;
        }

        table.deals-table th {
            color: #10c875;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.05em;
            background: rgba(4, 13, 6, 0.4);
        }

        table.deals-table tr:last-child td {
            border-bottom: none;
        }

        .no-deals {
            text-align: center;
            color: rgba(255, 255, 255, 0.45);
            padding: 2rem;
        }
    </style>
</head>

<body>
    <nav class="admin-navbar">
        <div class="admin-navbar-content">
            <a href="mainadmin.php" class="admin-logo">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#10c875" aria-hidden="true">
                    <path d="M13 2 4.5 13H11l-1 9L21 11h-7z" />
                </svg>
                PromoPilot Admin
            </a>
            <div class="admin-user-section">
                <div class="user-info">
                    <p class="user-name"><?php echo $username; ?></p>
                    <p class="user-role">Administrator</p>
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <a href="mainadmin.php" class="back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none">
                <polyline points="15 18 9 12 15 6" />
            </svg>
            Back to dashboard
        </a>

        <header class="page-header">
            <div class="logo-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#10c875" aria-hidden="true">
                    <path d="M13 2 4.5 13H11l-1 9L21 11h-7z" />
                </svg>
                <span>ANALYTICS</span>
            </div>
            <h1>Deal analytics</h1>
            <p>An overview of the discount codes currently in the system</p>
        </header>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">Total deals</div>
                <div class="stat-value"><?php echo $totalDeals; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Companies with deals</div>
                <div class="stat-value"><?php echo count($byCompany); ?></div>
            </div>
        </div>

        <div class="notice-banner">
            <strong>Heads up:</strong> the <code>coupons</code> table doesn't currently track redemptions or clicks,
            so real "usage" metrics (how often a code was used, conversion, etc.) aren't available yet. The numbers
            above are counts of deals stored in the database. If you want true usage tracking, I can help you add a
            <code>redemptions</code> table (or a counter column) and log an event each time a code is applied.
        </div>

        <?php if (!empty($byCompany)): ?>
            <div class="breakdown-section">
                <h2>Deals by company</h2>
                <?php foreach ($byCompany as $company => $count): ?>
                    <div class="bar-row">
                        <div class="bar-label" title="<?php echo htmlspecialchars($company); ?>">
                            <?php echo htmlspecialchars($company); ?>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?php echo round(($count / $maxByCompany) * 100); ?>%;"></div>
                        </div>
                        <div class="bar-count"><?php echo $count; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="deals-table-wrap">
            <h2>All deals</h2>
            <?php if (empty($coupons)): ?>
                <p class="no-deals">No deals yet.</p>
            <?php else: ?>
                <table class="deals-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Discount</th>
                            <th>Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $c): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($c['id']); ?></td>
                                <td><?php echo htmlspecialchars($c['name']); ?></td>
                                <td><?php echo htmlspecialchars($c['disamount']); ?></td>
                                <td><?php echo htmlspecialchars($c['discode']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>