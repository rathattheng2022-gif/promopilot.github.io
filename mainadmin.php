<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header("Location: loginAdmin.php");
    exit();
}
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';

$conn = new mysqli("localhost", "root", "", "projectui", 3306);
if ($conn->connect_error) {
    die("can't connect to databases");
}

$flashMessage = null; // ['type' => 'success'|'error', 'text' => '...']

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // ADD or UPDATE (same form, decided by whether coupon_id is set)
    if (isset($_POST['Save'])) {

        $name      = trim($_POST['name'] ?? '');
        $disamount = trim($_POST['disamount'] ?? '');
        $discode   = trim($_POST['discode'] ?? '');
        $couponId  = trim($_POST['coupon_id'] ?? '');

        if ($name === '' || $disamount === '' || $discode === '') {
            $flashMessage = ['type' => 'error', 'text' => 'All fields are required.'];
        } elseif ($couponId === '') {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO coupons (name, disamount, discode) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $disamount, $discode);
            if ($stmt->execute()) {
                $flashMessage = ['type' => 'success', 'text' => 'Deal added successfully!'];
            } else {
                $flashMessage = ['type' => 'error', 'text' => 'Insert failed: ' . $conn->error];
            }
            $stmt->close();
        } else {
            // UPDATE
            $stmt = $conn->prepare("UPDATE coupons SET name = ?, disamount = ?, discode = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $disamount, $discode, $couponId);
            if ($stmt->execute()) {
                $flashMessage = ['type' => 'success', 'text' => 'Deal updated successfully!'];
            } else {
                $flashMessage = ['type' => 'error', 'text' => 'Update failed: ' . $conn->error];
            }
            $stmt->close();
        }
    }

    // DELETE
    if (isset($_POST['Delete'])) {
        $deleteId = trim($_POST['coupon_id'] ?? '');
        if ($deleteId !== '') {
            $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
            $stmt->bind_param("i", $deleteId);
            if ($stmt->execute()) {
                $flashMessage = ['type' => 'success', 'text' => 'Deal deleted.'];
            } else {
                $flashMessage = ['type' => 'error', 'text' => 'Delete failed: ' . $conn->error];
            }
            $stmt->close();
        }
    }
}

// ---------------------------------------------------------------------
// Fetch all coupons for the table
// ---------------------------------------------------------------------
$coupons = [];
$result = $conn->query("SELECT id, name, disamount, discode FROM coupons ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $coupons[] = $row;
    }
}
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

        .admin-dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .admin-card {
            background: rgba(6, 68, 49, 0.5);
            border: 1px solid rgba(16, 200, 117, 0.2);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .admin-card:hover {
            border-color: #10c875;
            background: rgba(6, 68, 49, 0.8);
            transform: translateY(-5px);
        }

        .admin-card-icon {
            font-size: 2rem;
            color: #10c875;
            margin-bottom: 1rem;
        }

        .admin-card-title {
            color: #d5ff88;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .admin-card-text {
            color: rgba(255, 255, 255, 0.45);
            font-size: 14px;
            margin-bottom: 1rem;
        }

        .admin-card-link {
            display: inline-block;
            background: #10c875;
            color: #040d06;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .admin-card-link:hover {
            background: #0db567;
            transform: scale(1.05);
        }

        /* Flash message */
        .flash-msg {
            max-width: 700px;
            margin: 2rem auto 0;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
        }

        .flash-msg.success {
            background: rgba(16, 200, 117, 0.15);
            border: 1px solid #10c875;
            color: #d5ff88;
        }

        .flash-msg.error {
            background: rgba(255, 80, 80, 0.12);
            border: 1px solid #ff5050;
            color: #ffb3b3;
        }

        /* Deals table */
        .deals-table-wrap {
            margin-top: 4rem;
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

        table.deals-table tr:hover td {
            background: rgba(16, 200, 117, 0.06);
        }

        .row-actions {
            display: flex;
            gap: 8px;
        }

        .btn-edit,
        .btn-delete {
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-edit {
            background: #10c875;
            color: #040d06;
        }

        .btn-edit:hover {
            background: #0db567;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: transparent;
            color: #ff8080;
            border: 1px solid #ff5050;
        }

        .btn-delete:hover {
            background: rgba(255, 80, 80, 0.15);
            transform: translateY(-1px);
        }

        .no-deals {
            text-align: center;
            color: rgba(255, 255, 255, 0.45);
            padding: 2rem;
        }

        .form-title-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .btn-cancel-edit {
            display: none;
            background: transparent;
            color: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-cancel-edit.show {
            display: inline-block;
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
        <header class="page-header">
            <div class="logo-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#10c875" aria-hidden="true">
                    <path d="M13 2 4.5 13H11l-1 9L21 11h-7z" />
                </svg>
                <span>ADMIN DASHBOARD</span>
            </div>
            <h1>Welcome back, <?php echo $username; ?>!</h1>
            <p>Manage and add discount codes for your store</p>
        </header>

        <?php if ($flashMessage): ?>
            <div class="flash-msg <?php echo $flashMessage['type']; ?>">
                <?php echo htmlspecialchars($flashMessage['text']); ?>
            </div>
        <?php endif; ?>

        <div class="admin-dashboard">
            <div class="admin-card">
                <div class="admin-card-icon"><i class="fas fa-plus-circle"></i></div>
                <div class="admin-card-title">Add New Deal</div>
                <p class="admin-card-text">Create and list a new discount code</p>
                <a href="#add-deal-section" class="admin-card-link">Go to Form</a>
            </div>
            <div class="admin-card">
                <div class="admin-card-icon"><i class="fas fa-list"></i></div>
                <div class="admin-card-title">View All Deals</div>
                <p class="admin-card-text">See all active and inactive deals</p>
                <a href="./deals.php" class="admin-card-link">View List</a>
            </div>
            <div class="admin-card">
                <div class="admin-card-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="admin-card-title">Analytics</div>
                <p class="admin-card-text">Track deal performance and usage</p>
                <a href="./analytics.php" class="admin-card-link">View Stats</a>
            </div>
            <div class="admin-card">
                <div class="admin-card-icon"><i class="fas fa-cog"></i></div>
                <div class="admin-card-title">Settings</div>
                <p class="admin-card-text">Manage your admin account and preferences</p>
                <a href="./setting.php" class="admin-card-link">Go to Settings</a>
            </div>
        </div>

        <!-- ADD / EDIT FORM -->
        <form action="" method="post" id="couponForm">
            <div id="add-deal-section" style="margin-top: 4rem;">
                <main style="display: flex; flex-direction: column; align-items: center;">
                    <div class="coupon-card" id="couponCard">
                        <div class="coupon-top">
                            <div class="ribbon">
                                <span class="ribbon-label" id="formModeLabel">New Deal</span>
                            </div>
                            <div class="form-area">
                                <div class="form-title-row">
                                    <button type="button" class="btn-cancel-edit" id="cancelEditBtn" onclick="handleReset()">Cancel edit</button>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="pp-company">Company name</label>
                                        <input type="text" name="name" id="pp-company" placeholder="e.g. Wownow" autocomplete="off">
                                    </div>
                                    <div class="form-group">
                                        <label for="pp-discount">Discount amount</label>
                                        <input type="text" name="disamount" id="pp-discount" placeholder="e.g. 30% or $25" autocomplete="off">
                                    </div>
                                    <div class="form-group">
                                        <label for="pp-code">Discount code</label>
                                        <input type="text" name="discode" id="pp-code" placeholder="e.g. JUMBO30" autocomplete="off">
                                    </div>
                                </div>
                                <!-- Hidden field: empty = add mode, filled = edit mode -->
                                <input type="hidden" name="coupon_id" id="pp-coupon-id" value="">
                            </div>
                        </div>
                        <div class="perforation">
                            <div class="notch notch-l"></div>
                            <div class="perf-line"></div>
                            <div class="notch notch-r"></div>
                        </div>
                        <div class="coupon-bottom">
                            <div class="deal-badge">
                                <svg width="15" height="15" viewBox="0 0 24 24" stroke="#10c875" stroke-width="2" fill="none" aria-hidden="true">
                                    <path d="M12 3 4 7v5c0 4.4 3.4 8.6 8 9 4.6-.4 8-4.6 8-9V7z" />
                                    <polyline points="9 12 11 14 15 10" />
                                </svg>
                                <span>Verified &amp; active</span>
                            </div>
                            <button class="btn-add" id="addBtn" name="Save" type="submit">
                                <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" fill="none" aria-hidden="true">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                <span id="addBtnLabel">Add deal</span>
                            </button>
                        </div>
                    </div>
                </main>
            </div>
        </form>

        <!-- DEALS LIST -->
        <div class="deals-table-wrap" id="deals-list">
            <h2>All Deals</h2>
            <?php if (empty($coupons)): ?>
                <p class="no-deals">No deals yet. Add your first one above.</p>
            <?php else: ?>
                <table class="deals-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Discount</th>
                            <th>Code</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $c): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($c['id']); ?></td>
                                <td><?php echo htmlspecialchars($c['name']); ?></td>
                                <td><?php echo htmlspecialchars($c['disamount']); ?></td>
                                <td><?php echo htmlspecialchars($c['discode']); ?></td>
                                <td>
                                    <div class="row-actions">
                                        <button
                                            type="button"
                                            class="btn-edit"
                                            onclick="editCoupon(
                                                '<?php echo (int)$c['id']; ?>',
                                                <?php echo htmlspecialchars(json_encode($c['name']), ENT_QUOTES, 'UTF-8'); ?>,
                                                <?php echo htmlspecialchars(json_encode($c['disamount']), ENT_QUOTES, 'UTF-8'); ?>,
                                                <?php echo htmlspecialchars(json_encode($c['discode']), ENT_QUOTES, 'UTF-8'); ?>
                                            )">
                                            Edit
                                        </button>
                                        <form action="" method="post" onsubmit="return confirm('Delete this deal? This cannot be undone.');" style="display:inline;">
                                            <input type="hidden" name="coupon_id" value="<?php echo (int)$c['id']; ?>">
                                            <button type="submit" name="Delete" class="btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Switch the shared form into "edit" mode and scroll to it.
        function editCoupon(id, name, disamount, discode) {
            document.getElementById('pp-coupon-id').value = id;
            document.getElementById('pp-company').value = name;
            document.getElementById('pp-discount').value = disamount;
            document.getElementById('pp-code').value = discode;

            document.getElementById('formModeLabel').textContent = 'Edit Deal';
            document.getElementById('addBtnLabel').textContent = 'Update deal';
            document.getElementById('cancelEditBtn').classList.add('show');

            document.getElementById('add-deal-section').scrollIntoView({ behavior: 'smooth' });
        }

        // Reset the form back to "add" mode.
        function handleReset() {
            document.getElementById('couponForm').reset();
            document.getElementById('pp-coupon-id').value = '';
            document.getElementById('formModeLabel').textContent = 'New Deal';
            document.getElementById('addBtnLabel').textContent = 'Add deal';
            document.getElementById('cancelEditBtn').classList.remove('show');
        }
    </script>
</body>

</html>