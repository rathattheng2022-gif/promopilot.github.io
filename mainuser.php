<?php
session_start();
$conn = new mysqli("localhost", "root", "", "projectui", 3306);
if ($conn->connect_error) {
    die("can't connect to databases");
}

$searchTerm = trim($_GET['q'] ?? '');

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="style.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    />
    <title>PromoPilot - Best Deals</title>
  </head>
  <body>
    <div class="container-fluid menu-bar">
      <div class="container menu">
         <div>
            <img class="img" src="./img/IMG_3455-removebg-preview.png" alt="">
          </div>
        <div class="groub-bar">
          <ul class="ul-bar">
          <li><a href="mainuser.php">Home</a></li>
          <li><a href="#topdeals">Deals</a></li>
          <li><a href="#stores">Stores</a></li>
          <li><a href="#topdeals">Top Deals</a></li>
        </ul>
        <!--Menu Bar-->
        <div class="menu-btns">
            <div class="menu-right">
                <?php 
                    if(isset($_SESSION['username'])){
                        echo "Welcome ".$_SESSION['username']. " | ";
                        echo "<a href= 'logout.php'>Logout</a>"; 
                    }
                ?>
            </div>
           
        </div>
        </div>
        <span class="icon-bar" onclick="iconBar()"><i class="fa-solid fa-bars"></i></span>
        </div>
    </div>
    <div class="menu-left">
      <ul class="mt-4">
        <li><a href="main-deals.html">Home</a></li>
        <li><a href="#topdeals">Deals</a></li>
        <li><a href="#stores">Stores</a></li>
        <li><a href="#topdeals">Top Deals</a></li>
      </ul>
      <div class="menu-btns" style="color: var(--color-green);">
        <?php
if (isset($_SESSION['username'])) {
    echo "Welcome " . $_SESSION['username'] . " | ";
    echo '<a style="color: var(--color-green); text-decoration: none;" href="logout.php">Logout</a>';
}
?>
      </div>
  </div>
    <!-- Popup -->
<div class="popup-overlay" id="popupOverlay">
  <div class="popup-box" >
    <h3>🎉 Coupon Applied!</h3>
    <p>Your coupon code has been copied.</p>
    <div class="coupon-code" id="popupCode"></div>
    <button class="btn mt-3" onclick="closePopup()">
      Close
    </button>
  </div>
</div>
    <div class="container mt-5 header-text">
      <h2 class="text-center text-light">Find Best Deals in Cambodia</h2>
      <p class="text-center text text-light">
        Let our experts find the best coupons and deals for you!
      </p>
      <form action="" method="GET" class="search mt-4">
        <button
          type="submit"
          class="fa-brands fa-sistrix btn btn-search"
          style="background-color: #10c875; color: white; border: none;"
        ></button>
        <input
          type="search"
          name="q"
          value="<?php echo htmlspecialchars($searchTerm); ?>"
          placeholder="Search for store,coupons, & offer.."
          class="form-control"
          style="border-radius: 8px"
        />
      </form>
      <p class="text-ex mt-2 text-center text-light">
        E.g. Coupans, Offers, Brands, Stores, Restaurant & more...
      </p>
    </div>
    <div class="container img mt-5">
      <div class="pic">
        <img
          class="w-100"
          src="https://www.retailmenot.com/imagery/dream-bars/01kve1t8k5mkr879281mmyse6p-desktop-image.fit_limit.quality_80.size_4800x320.v1781809357.png.webp"
          alt=""
        />
      </div>
    </div>
    <div class="container content">
      <div class="topdeal mt-5" id="topdeals">
        <h1 class="">Top deals</h1>
        <h1 class="veiw">Veiw All</h1>
      </div>
      <div class="row">
        <?php
if ($searchTerm !== '') {
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE name LIKE ? OR discode LIKE ? ORDER BY id DESC");
    $likeTerm = '%' . $searchTerm . '%';
    $stmt->bind_param("ss", $likeTerm, $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM coupons ORDER BY id DESC LIMIT 5";
    $result = $conn->query($sql);
}

if($result->num_rows > 0){

    while($row = $result->fetch_assoc()){
?>

<div class="col-12 mt-4">
    <figure>
        <div class="d-flex justify-content-center align-items-center">
            <div class="coupon d-flex">

                <div class="coupon-badge d-flex align-items-center justify-content-center">
                    <?php echo $row['disamount']; ?>% OFF
                </div>

                <div class="flex-fill p-3 d-flex flex-column gap-2">

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold fs-6 text-light">
                            <?php echo $row['name']; ?>
                        </span>

                        <button
    class="btn btn-link apply-btn p-0 text-decoration-none"
    onclick="showPopup('<?php echo $row['discode']; ?>')">
    APPLY
</button>
                    </div>

                    <div class="saving-text">
                        Coupon Code:
                        <strong><?php echo $row['discode']; ?></strong>
                    </div>

                    <hr class="coupon-divider my-1">

                    <p class="text-light">
                        Get <?php echo $row['disamount']; ?>% discount by using this coupon.
                    </p>

                </div>

            </div>
        </div>
    </figure>
</div>

<?php
    }
}else{
    echo "<h3>No coupons available.</h3>";
}
?>
      </div>
    </div>
    <div class="container topshop" id="stores">
      <div class="topshop-text">
        <h1>Stores</h1>
        <h1 class="veiw">Veiw All</h1>
      </div>
      <section class="coupon-section py-5 container">
    <div class="container position-relative">


        <div class="row g-4">

            <!-- Card 1 -->
            <div class="col-lg-3 col-md-6 col-12">
                <div class="coupon-card">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSsz-t8D6vMa_WYXFX_wrbbczMyW9E0xrirGJrScrOTS7gEg-sKAb_0DHQ4&s=10"
                        alt="KFC">

                    <h3>50% OFF</h3>

                    <p class="coupon-text">
                        Get Flat 50% OFF On First Order
                    </p>

                    <span class="expire">
                        <i class="bi bi-clock"></i>
                        Ends 09.15.2027
                    </span>
                </div>
            </div>
            
            

            <!-- Card 2 -->
            <div class="col-lg-3 col-md-6 col-12">
                <div class="coupon-card">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/74/Dominos_pizza_logo.svg"
                        alt="Dominos">

                    <h3>Buy 1 Get 1 Free</h3>

                    <p class="coupon-text">
                        Get Flat 50% OFF On First Order
                    </p>

                    <span class="expire">
                        <i class="bi bi-clock"></i>
                        Ends 09.15.2027
                    </span>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="col-lg-3 col-md-6 col-12">
                <div class="coupon-card">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSFfk3QY7EteZA3QzE20Ftyr_TVIRjjTBSy1Lo-5HhtVJhqAJD3ryAlSsNE&s=10"
                        alt="Burger King">

                    <h3>Free Drink</h3>

                    <p class="coupon-text">
                        Get Flat 50% OFF On First Order
                    </p>

                    <span class="expire">
                        <i class="bi bi-clock"></i>
                        Ends 09.15.2027
                    </span>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="col-lg-3 col-md-6 col-12">
                <div class="coupon-card">
                    <img src="https://www.techoairport.com.kh/tia-backend/locators/1753854813934-Logo-md.jpg"
                        alt="7up">

                    <h3>80% OFF</h3>

                    <p class="coupon-text">
                        Get Flat 50% OFF On First Order
                    </p>

                    <span class="expire">
                        <i class="bi bi-clock"></i>
                        Ends 09.15.2027
                    </span>
                </div>
            </div>

        </div>


    </div>
</section>
  
    </div>
    <footer class="footer-section">
    <div class="container">

        <!-- Top Footer -->
        <div class="row gy-5">

            <!-- Logo -->
            <div class="col-lg-3">
                <div class="footer-logo">
                    <h2>Promo</h2>
                    <p>PILOT.COM</p>
                </div>
            </div>

            <!-- Links -->
            <div class="col-lg-3 col-md-6">
                <ul class="footer-links">
                    <li><a href="#">Stores</a></li>
                    <li><a href="#">Koi</a></li>
                    <li><a href="#">Ten11</a></li>
                    <li><a href="#">Pendro</a></li>
                    <li><a href="#">Brown Coffee</a></li>
                    <li><a href="#">KFC</a></li>
                </ul>
            </div>

            <!-- My RMN -->
            <div class="col-lg-3 col-md-6">
                <h5>MY RMN</h5>
                <ul class="footer-links">
                    <li><a href="#">My Account + Rewards</a></li>
                    <li><a href="#">Promo Community</a></li>
                    <li><a href="#">Submit a Coupon</a></li>
                    <li><a href="#">Get Help</a></li>
                </ul>
            </div>

            <!-- App Download -->
            <div class="col-lg-3">
                <h5>DOWNLOAD THE APP</h5>

                <p class="footer-desc">
                    Get app-only offers and the best deals.
                </p>

                <div class="d-flex gap-3 align-items-center flex-wrap">
                    
                  
                        <img
                            src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg"
                            class="store-btn mb-2"
                            alt="App Store">

                        <img
                            src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                            class="store-btn"
                            alt="Google Play">
                    </div>
                </div>
            </div>

        </div>

        <hr>

        <!-- Social + Policies -->
        <div class="footer-bottom">

            <div class="social-icons">
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-pinterest"></i></a>
                <a href="#"><i class="bi bi-twitter-x"></i></a>
                <a href="#"><i class="bi bi-tiktok"></i></a>
            </div>

            <div class="policy-links " style="margin-right: 20px;">
                <a href="#"></a>
                <a href="#"></a>
                <a href="#"></a>
                <a href="#"></a>
            </div>

        </div>

        <!-- Copyright -->
        <div class="copyright text-center ">
            Copyright © Your Website 2026
        </div>

    </div>
</footer>
  </body>
  <script src="./main.js"></script>
</html>