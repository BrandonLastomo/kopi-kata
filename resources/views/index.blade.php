<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>kopi & kata</title>

    <!-- SWIPER -->
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />

    <!-- Font Awesome CDN Link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Custom CSS File Link  -->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

</head>

<body>

    <!-- HEADER -->
    <header class="header">
        <div id="menu-btn" class="fas fa-bars"></div>

        <a href="#" class="logo">Kopi & Kata</a>

        <nav class="navbar">
            <a href="#home">home</a>
            <a href="#about">about</a>
            <a href="#menu">menu</a>
            <a href="#review">review</a>
            <a href="book.php">book</a>
        </nav>

        <div class="user-info">
            <a href="#" class="btn">Hi, {{ auth()->user()->name }} </a>
            <a href="logout.php" class="btn">logout</a>
        </div>
    </header>

    <!-- HOME -->
    <section class="home" id="home">
        <div class="row">
            <div class="content">
                <p>We got you covered with</p>
                <h1>Coffee</h1>
                <p>It is best to start your day with a cup of coffee. Discover the</p>
                <p>best flavours coffee you will ever have. We provide the best</p>
                <p>for our customers.</p>
            </div>
        </div>
    </section>

    <!-- ABOUT -->
    <section class="about" id="about">
        <h1 class="heading">about us <span>why choose us</span></h1>
        <div class="content">
            <h1 class="title">Discover the best coffee!</h1>
            <p>Bean Hub is a coffee shop that provides you with quality coffee that helps</p>
            <p>boost your productivity and helps build your mood. Having a cup of</p>
            <p>coffee is good, but having a cup of real coffee is greater. There is no</p>
            <p>doubt that you will enjoy this coffee more than others you have ever</p>
            <p>tasted.</p>
        </div>
    </section>

    <!-- MENU -->
    <section class="menu" id="menu">
        <h1 class="heading">our menu <span>popular menu</span></h1>

        <div class="box-container">
            <a href="#" class="box">
                <img src="src/images/menu-1.png" alt="">
                <div class="content">
                    <h3>Espresso</h3>
                    <p>This drink is made by extracting compressed coffee grounds using high-pressure hot water.</p>
                    <span>$8.99</span>
                </div>
            </a>

            <a href="#" class="box">
                <img src="src/images/menu-2.png" alt="">
                <div class="content">
                    <h3>Americano</h3>
                    <p>This drink is made by adding hot water to espresso.</p>
                    <span>$8.99</span>
                </div>
            </a>

            <a href="#" class="box">
                <img src="src/images/menu-3.png" alt="">
                <div class="content">
                    <h3>Caffe Latte</h3>
                    <p>Made from espresso and steamed milk, caffe latte offers a smooth and creamy taste.</p>
                    <span>$8.99</span>
                </div>
            </a>

            <a href="#" class="box">
                <img src="src/images/menu-4.png" alt="">
                <div class="content">
                    <h3>Cappuccino</h3>
                    <p>made from a combination of espresso, hot milk, and milk foam in balanced proportions.</p>
                    <span>$8.99</span>
                </div>
            </a>

            <a href="#" class="box">
                <img src="src/images/menu-5.png" alt="">
                <div class="content">
                    <h3>Flat White</h3>
                    <p>made from espresso and steamed milk, with a higher ratio of coffee to milk than a latte.</p>
                    <span>$8.99</span>
                </div>
            </a>

            <a href="#" class="box">
                <img src="src/images/menu-6.png" alt="">
                <div class="content">
                    <h3>Macchiato</h3>
                    <p>made from espresso with a little added milk foam on top.</p>
                    <span>$8.99</span>
                </div>
            </a>
        </div>
    </section>

    <!-- REVIEW -->
    <section class="review" id="review">
        <h1 class="heading">reviews <span>what people says</span></h1>

        <div class="swiper review-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide box">
                    <i class="fas fa-quote-left"></i>
                    <i class="fas fa-quote-right"></i>
                    <img src="src/images/pic-1.png" alt="">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>tempat nya nyaman</p>
                    <h3>putra raka</h3>
                    <span>satisfied client</span>
                </div>

                <div class="swiper-slide box">
                    <i class="fas fa-quote-left"></i>
                    <i class="fas fa-quote-right"></i>
                    <img src="src/images/pic-2.png" alt="">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>kopi nya enak coy</p>
                    <h3>kapka</h3>
                    <span>satisfied client</span>
                </div>

                <div class="swiper-slide box">
                    <i class="fas fa-quote-left"></i>
                    <i class="fas fa-quote-right"></i>
                    <img src="src/images/pic-3.png" alt="">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>tempatnya cozy</p>
                    <h3>ilham</h3>
                    <span>satisfied client</span>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    <!-- FOOTER -->
    <section class="footer">
        <div class="box-container">
            <div class="box">
                <h3>quick links</h3>
                <a href="#home"><i class="fas fa-arrow-right"></i> home</a>
                <a href="#about"><i class="fas fa-arrow-right"></i> about</a>
                <a href="#menu"><i class="fas fa-arrow-right"></i> menu</a>
                <a href="#review"><i class="fas fa-arrow-right"></i> review</a>
                <a href="#book"><i class="fas fa-arrow-right"></i> book</a>
            </div>

            <div class="box">
                <h3>contact info</h3>
                <a href="#"><i class="fas fa-phone"></i> +123-456-7890</a>
                <a href="#"><i class="fas fa-phone"></i> +111-222-3333</a>
                <a href="#"><i class="fas fa-envelope"></i> coffee@gmail.com</a>
                <a href="#"><i class="fas fa-envelope"></i> Karawang, Indonesia</a>
            </div>

            <div class="box">
                <h3>contact info</h3>
                <a href="#"><i class="fab fa-facebook-f"></i> facebook</a>
                <a href="#"><i class="fab fa-twitter"></i> twitter</a>
                <a href="#"><i class="fab fa-instagram"></i> instagram</a>
                <a href="#"><i class="fab fa-linkedin"></i> linkedin</a>
                <a href="#"><i class="fab fa-twitter"></i> twitter</a>
            </div>
        </div>

        <div class="credit">created by kelompok 7 | all rights reserved</div>
    </section>

    <!-- SWIPER -->
    <script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>

    <!-- Custom JS File Link  -->
    <script src="script.js"></script>

</body>

</html>