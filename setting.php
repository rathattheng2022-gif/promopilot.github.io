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

/*
 * NOTE: this assumes an `admins` table shaped like:
 *   id | username | password (hashed with password_hash())
 * matching whatever your loginAdmin.php checks against.
 * If your table/columns are named differently, tell me the real
 * schema and I'll line this up exactly.
 */

$flashMessage = null;

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // ---- Update username ----
    if (isset($_POST['UpdateUsername'])) {
        $newUsername = trim($_POST['new_username'] ?? '');

        if ($newUsername === '') {
            $flashMessage = ['type' => 'error', 'text' => 'Username cannot be empty.'];
        } else {
            $stmt = $conn->prepare("UPDATE admins SET username = ? WHERE username = ?");
            $stmt->bind_param("ss", $newUsername, $_SESSION['username']);
            if ($stmt->execute()) {
                $_SESSION['username'] = $newUsername;
                $username = htmlspecialchars($newUsername);
                $flashMessage = ['type' => 'success', 'text' => 'Username updated successfully.'];
            } else {
                $flashMessage = ['type' => 'error', 'text' => 'Update failed: ' . $conn->error];
            }
            $stmt->close();
        }
    }

    // ---- Change password ----
    if (isset($_POST['ChangePassword'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $flashMessage = ['type' => 'error', 'text' => 'All password fields are required.'];
        } elseif ($newPassword !== $confirmPassword) {
            $flashMessage = ['type' => 'error', 'text' => 'New password and confirmation do not match.'];
        } elseif (strlen($newPassword) < 8) {
            $flashMessage = ['type' => 'error', 'text' => 'New password must be at least 8 characters.'];
        } else {
            $stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
            $stmt->bind_param("s", $_SESSION['username']);
            $stmt->execute();
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();
            $stmt->close();

            if ($hashedPassword && password_verify($currentPassword, $hashedPassword)) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");
                $stmt->bind_param("ss", $newHash, $_SESSION['username']);
                if ($stmt->execute()) {
                    $flashMessage = ['type' => 'success', 'text' => 'Password changed successfully.'];
                } else {
                    $flashMessage = ['type' => 'error', 'text' => 'Update failed: ' . $conn->error];
                }
                $stmt->close();
            } else {
                $flashMessage = ['type' => 'error', 'text' => 'Current password is incorrect.'];
            }
        }
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
            max-width: 700px;
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
            margin: 1.5rem 0 0;
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

        .settings-card {
            background: rgba(6, 68, 49, 0.5);
            border: 1px solid rgba(16, 200, 117, 0.2);
            border-radius: 16px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .settings-card h2 {
            color: #d5ff88;
            margin-bottom: 0.3rem;
            font-size: 1.15rem;
        }

        .settings-card .section-sub {
            color: rgba(255, 255, 255, 0.45);
            font-size: 13px;
            margin-bottom: 1.5rem;
        }

        .settings-form .form-group {
            margin-bottom: 1.2rem;
        }

        .settings-form label {
            display: block;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            margin-bottom: 6px;
        }

        .settings-form input[type="text"],
        .settings-form input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid rgba(16, 200, 117, 0.25);
            background: rgba(255, 255, 255, 0.03);
            color: #fff;
            font-size: 14px;
            box-sizing: border-box;
        }

        .settings-form input:focus {
            outline: none;
            border-color: #10c875;
        }

        .btn-save-settings {
            background: #10c875;
            color: #040d06;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-save-settings:hover {
            background: #0db567;
            transform: translateY(-1px);
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
                <span>SETTINGS</span>
            </div>
            <h1>Account settings</h1>
            <p>Manage your admin username and password</p>
        </header>

        <?php if ($flashMessage): ?>
            <div class="flash-msg <?php echo $flashMessage['type']; ?>">
                <?php echo htmlspecialchars($flashMessage['text']); ?>
            </div>
        <?php endif; ?>

        <!-- Username -->
        <div class="settings-card">
            <h2>Username</h2>
            <p class="section-sub">Currently signed in as <strong><?php echo $username; ?></strong></p>
            <form action="" method="post" class="settings-form">
                <div class="form-group">
                    <label for="new_username">New username</label>
                    <input type="text" name="new_username" id="new_username" placeholder="<?php echo $username; ?>" required>
                </div>
                <button type="submit" name="UpdateUsername" class="btn-save-settings">Update username</button>
            </form>
        </div>

        <!-- Password -->
        <div class="settings-card">
            <h2>Password</h2>
            <p class="section-sub">Choose a new password with at least 8 characters</p>
            <form action="" method="post" class="settings-form">
                <div class="form-group">
                    <label for="current_password">Current password</label>
                    <input type="password" name="current_password" id="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New password</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm new password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit" name="ChangePassword" class="btn-save-settings">Change password</button>
            </form>
        </div>
    </div>
</body>

</html>