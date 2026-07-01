<?php 
    session_start();
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if($_POST['username'] == $_SESSION['username']
        and $_POST['password'] == $_SESSION['password']){
            $_SESSION['loggedIn'] = true;
            header("Location:mainadmin.php");
            exit();
        } else {
           $error = "Username and password not correct!";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'head.php'; ?>
    <meta charset="utf-8">
    <title>Admin Login - PromoPilot</title>
</head>
<body>
    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow p-4 rounded-4">
                <a href="main-deals.php" class="btn btn-outline-light" style="width: auto; margin-bottom: 15px;">
                    <i class="fas fa-arrow-left"></i> Back
                </a>

                <h3 class="text-center mb-4">Admin Login</h3>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input id="username" class="form-control ps-2 border-secondary-subtle" type="text" name="username" placeholder="Enter username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" class="form-control ps-2 border-secondary-subtle" type="password" name="password" placeholder="Enter password" required>
                    </div>

                    <div class="d-grid mt-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>

                <p class="text-center mt-3">Don't have an account? <a href="signupProcess.php">Sign up now</a></p>
            </div>
        </div>
    </div>
</body>
</html>