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

$flashMessage = null; // ['type' => 'success'|'error', 'text' => '...']

// ---------------------------------------------------------------------
// Handle Add / Update / Delete right on this page too, so it's fully
// self-contained (same logic/pattern as mainadmin.php).
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if (isset($_POST['Save'])) {
        $name      = trim($_POST['name'] ?? '');
        $disamount = trim($_POST['disamount'] ?? '');
        $discode   = trim($_POST['discode'] ?? '');
        $couponId  = trim($_POST['coupon_id'] ?? '');

        if ($name === '' || $disamount === '' || $discode === '') {
            $flashMessage = ['type' => 'error', 'text' => 'All fields are required.'];
        } elseif ($couponId === '') {
            $stmt = $conn->prepare("INSERT INTO coupons (name, disamount, discode) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $disamount, $discode);
            if ($stmt->execute()) {
                $flashMessage = ['type' => 'success', 'text' => 'Deal added successfully!'];
            } else {
                $flashMessage = ['type' => 'error', 'text' => 'Insert failed: ' . $conn->error];
            }
            $stmt->close();
        } else {
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
// Fetch all coupons
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

        .flash-msg {
            max-width: 700px;
            margin: 1.5rem auto 0;
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

        .deals-table-wrap {
            margin-top: 2.5rem;
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

        /* Edit modal */
        .edit-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }

        .edit-modal-backdrop.show {
            display: flex;
        }

        .edit-modal {
            background: #08160f;
            border: 1px solid rgba(16, 200, 117, 0.3);
            border-radius: 16px;
            padding: 2rem;
            width: 100%;
            max-width: 420px;
        }

        .edit-modal h3 {
            color: #d5ff88;
            margin-bottom: 1.2rem;
        }

        .edit-modal .form-group {
            margin-bottom: 1rem;
        }

        .edit-modal label {
            display: block;
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            margin-bottom: 6px;
        }

        .edit-modal input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid rgba(16, 200, 117, 0.25);
            background: rgba(255, 255, 255, 0.03);
            color: #fff;
            font-size: 14px;
            box-sizing: border-box;
        }

        .edit-modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 1.5rem;
        }

        .edit-modal-actions button {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }

        .btn-save-edit {
            background: #10c875;
            color: #040d06;
        }

        .btn-save-edit:hover {
            background: #0db567;
        }

        .btn-cancel-modal {
            background: transparent;
            color: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
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
                <span>ALL DEALS</span>
            </div>
            <h1>All deals</h1>
            <p>See, edit, or remove every discount code in the system</p>
        </header>

        <?php if ($flashMessage): ?>
            <div class="flash-msg <?php echo $flashMessage['type']; ?>">
                <?php echo htmlspecialchars($flashMessage['text']); ?>
            </div>
        <?php endif; ?>

        <div class="deals-table-wrap">
            <?php if (empty($coupons)): ?>
                <p class="no-deals">No deals yet. <a href="mainadmin.php#add-deal-section" style="color:#10c875;">Add your first one</a>.</p>
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
                                            onclick="openEditModal(
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

    <!-- Edit modal (submits back to this same page) -->
    <div class="edit-modal-backdrop" id="editModalBackdrop">
        <div class="edit-modal">
            <h3>Edit deal</h3>
            <form action="" method="post" id="editForm">
                <input type="hidden" name="coupon_id" id="edit-coupon-id" value="">
                <div class="form-group">
                    <label for="edit-name">Company name</label>
                    <input type="text" name="name" id="edit-name" required>
                </div>
                <div class="form-group">
                    <label for="edit-disamount">Discount amount</label>
                    <input type="text" name="disamount" id="edit-disamount" required>
                </div>
                <div class="form-group">
                    <label for="edit-discode">Discount code</label>
                    <input type="text" name="discode" id="edit-discode" required>
                </div>
                <div class="edit-modal-actions">
                    <button type="button" class="btn-cancel-modal" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" name="Save" class="btn-save-edit">Save changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, disamount, discode) {
            document.getElementById('edit-coupon-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-disamount').value = disamount;
            document.getElementById('edit-discode').value = discode;
            document.getElementById('editModalBackdrop').classList.add('show');
        }

        function closeEditModal() {
            document.getElementById('editModalBackdrop').classList.remove('show');
        }

        // Close modal when clicking outside it
        document.getElementById('editModalBackdrop').addEventListener('click', function (e) {
            if (e.target === this) closeEditModal();
        });
    </script>
</body>

</html>