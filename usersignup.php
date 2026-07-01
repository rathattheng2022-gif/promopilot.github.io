<?php 
    session_start();
    // user click signIn button
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirmpassword = $_POST['confirmpassword'];
        if($password !== $confirmpassword){
            $error = "Passwords do not match!";
        } else {
            $_SESSION['username'] = $username;
            $_SESSION['password'] = $password;
            header("location:main-deals.php");
            exit();
        }
        
    }

?>
<!DOCTYPE html>
<html>
<head><?php include 'head.php'; ?></head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-5">
                <div class="card shadow p-4 rounded-4">
                    <!-- Back Button -->
                    <a href="signupProcess.php" class="btn btn-outline-light" style="width: auto; margin-bottom: 15px;">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>

                    <form method="POST" action="">
                        <h3 style="text-align: center; font-size: 20px;">Sign Up As User</h3>

                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input 
                                id="username"
                                class="form-control ps-2 border-secondary-subtle" 
                                type="text" 
                                name="username"
                                placeholder="Enter username" 
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                id="password"
                                class="form-control ps-2 border-secondary-subtle" 
                                type="password" 
                                name="password"
                                placeholder="Enter password" 
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="confirmpassword" class="form-label">Confirm Password</label>
                            <input 
                                id="confirmpassword"
                                class="form-control ps-2 border-secondary-subtle" 
                                type="password"
                                name="confirmpassword"
                                placeholder="Confirm password" 
                                required>
                        </div>

                        <div class="d-grid mt-2">
                            <button type="submit" class="btn btn-primary">Sign Up</button>
                        </div>
                    </form>

                    <p class="text-center mt-3">Already have an account? <a href="signupProcess.php">Go back</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>