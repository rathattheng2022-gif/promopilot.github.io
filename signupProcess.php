<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? null;

    if ($role === 'user') {
        $_SESSION['role'] = 'user';
        header('Location: usersignup.php');
        exit();
    } elseif ($role === 'admin') {
        $_SESSION['role'] = 'admin';
        header('Location: AdminSignup.php'); 
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include 'head.php'; ?></head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-5">
                <div class="card shadow p-4 rounded-4">
                    <!-- ✅ Back Button goes to main-deals -->
                    <a href="main-deals.php" class="btn btn-outline-light" style="width: auto; margin-bottom: 15px;">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>

                    <form method="POST" action="">
                        <h3 style="text-align: center; font-size: 20px;">Sign Up Process</h3>
                        <p style="text-align: center; color: rgba(255, 255, 255, 0.7); margin-bottom: 30px;">
                            Choose your account type to continue
                        </p>

                        <div class="d-grid mt-2">
                            <button type="submit" name="role" value="user" class="btn btn-primary">
                                <i class="fas fa-user"></i> Sign As User
                            </button>
                        </div>

                        <div class="d-grid mt-2">
                            <button type="submit" name="role" value="admin" class="btn btn-primary">
                                <i class="fas fa-shield-alt"></i> Sign As Admin
                            </button>
                        </div>
                    </form>

                    <p class="text-center mt-3">Already have an account? <a href="loginChoice.php">Go to login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
