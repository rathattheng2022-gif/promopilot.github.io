<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'head.php'; ?>
    <meta charset="utf-8">
    <title>Login - Choose Account Type</title>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-5">
                <div class="card shadow p-4 rounded-4">
                    <!-- Back Button -->
                    <a href="main-deals.php" class="btn btn-outline-light" style="width: auto; margin-bottom: 15px;">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>

                    <h3 style="text-align: center; font-size: 20px;">Choose Login Type</h3>
                    <p style="text-align: center; color: rgba(255, 255, 255, 0.7); margin-bottom: 30px;">
                        Select how you'd like to login
                    </p>

                    <div class="d-grid mt-2">
                        <a href="loginUser.php" class="btn btn-primary" style="text-decoration: none;">
                            <i class="fas fa-user"></i> Login As User
                        </a>
                    </div>

                    <div class="d-grid mt-2">
                        <a href="loginAdmin.php" class="btn btn-primary" style="text-decoration: none;">
                            <i class="fas fa-shield-alt"></i> Login As Admin
                        </a>
                    </div>

                    <p class="text-center mt-3">Don't have an account? <a href="signupProcess.php">Sign up now</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
