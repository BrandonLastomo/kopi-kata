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
    <header>
        <nav>
            <p class="logo">Kopi&Kata</p>
            <div class="pageLinks">
                <a href="{{ route('home') }}/#home">home</a>
                <a href="{{ route('home') }}/#about">about</a>
                <a href="{{ route('home') }}/#menu">menu</a>
                <a href="{{ route('home') }}/#review">review</a>
                <a href="{{ route('book.index') }}">book</a>
            </div>
            <div class="userInfo">
                @if (auth()->check())
                    <p>{{ auth()->check() ? "Hi, ". auth()->user()->name : "Welcome!" }}</p> | 
                    <a href="{{ route('logout') }}" class="log">Logout</a>
                @else
                    <a href="{{ route('login') }}" class="log">Login</a>
                @endif
            </div>
        </nav>
    </header>

    <!-- HOME -->
    <section class="home" id="home">
        <div class="content">
            <p>We got you covered with Coffee.</p>
            <p>It is best to start your day with a cup of coffee.</p>
            <p>Discover the best flavours coffee you will ever have.</p>
            <p>We provide the best for our customers.</p>
        </div>
    </section>

    <!-- ABOUT -->
    <section class="about" id="about">
        <h1 class="heading">about us</h1>
        <div class="content">
            <div class="abt1">
                <h1 class="title">Discover the best coffee!</h1>
                <p><span class="logo">Kopi & Kita</span> is a coffee shop that provides you with quality coffee that helps</p>
                <p>boost your productivity and helps build your mood. Having a cup of</p>
                <p>coffee is good, but having a cup of real coffee is greater. There is no</p>
                <p>doubt that you will enjoy this coffee more than others you have ever</p>
                <p>tasted.</p>
            </div>
            <div class="abt2">
                <h1 class="title">Discover the best coffee!</h1>
                <p><span class="logo">Kopi & Kita</span> is a coffee shop that provides you with quality coffee that helps</p>
                <p>boost your productivity and helps build your mood. Having a cup of</p>
                <p>coffee is good, but having a cup of real coffee is greater. There is no</p>
                <p>doubt that you will enjoy this coffee more than others you have ever</p>
                <p>tasted.</p>
            </div>
            <div class="abt3">
                <h1 class="title">Discover the best coffee!</h1>
                <p><span class="logo">Kopi & Kita</span> is a coffee shop that provides you with quality coffee that helps</p>
                <p>boost your productivity and helps build your mood. Having a cup of</p>
                <p>coffee is good, but having a cup of real coffee is greater. There is no</p>
                <p>doubt that you will enjoy this coffee more than others you have ever</p>
                <p>tasted.</p>
            </div>
        </div>
    </section>

    <!-- MENU -->
    <section class="menu" id="menu">
        <h1 class="heading">Reserve <span class="emp">Now</span>, Refreshed <span class="emp">Now.</span></h1>

        <div class="content">
            <div class="menuItem">
                <div class="details">
                    <h3>Espresso</h3>
                    <p>This drink is made by extracting compressed coffee grounds using high-pressure hot water.</p>
                </div>
                <p class="price">$8.99</p>
                <img src="{{ asset('assets/images/menu2.png') }}" alt="menu">
            </div>

            <div class="menuItem">
                <div class="details">
                    <h3>Americano</h3>
                    <p>This drink is made by adding hot water to espresso.</p>
                </div>
                <p class="price">$8.99</p>
                <img src="{{ asset('assets/images/menu2.png') }}" alt="menu">
            </div>

            <div class="menuItem">
                <div class="details">
                    <h3>Caffe Latte</h3>
                    <p>Made from espresso and steamed milk, caffe latte offers a smooth and creamy taste.</p>
                </div>
                <p class="price">$8.99</p>
                <img src="{{ asset('assets/images/menu2.png') }}" alt="menu">
            </div>

            <div class="menuItem">
                <div class="details">
                    <h3>Cappuccino</h3>
                    <p>made from a combination of espresso, hot milk, and milk foam in balanced proportions.</p>
                </div>
                <p class="price">$8.99</p>
                <img src="{{ asset('assets/images/menu2.png') }}" alt="menu">
            </div>

            <div class="menuItem">
                <div class="details">
                    <h3>Flat White</h3>
                    <p>made from espresso and steamed milk, with a higher ratio of coffee to milk than a latte.</p>
                </div>
                <p class="price">$8.99</p>
                <img src="{{ asset('assets/images/menu2.png') }}" alt="menu">
            </div>

            <div class="menuItem">
                <div class="details">
                    <h3>Macchiato</h3>
                    <p>made from espresso with a little added milk foam on top.</p>
                </div>
                <p class="price">$8.99</p>
                <img src="{{ asset('assets/images/menu2.png') }}" alt="menu">
            </div>
        </div>
    </section>

    <!-- REVIEW -->
    <section class="review" id="review">
        <h1 class="heading">Don't Worry. We hear you.</h1>

        <div class="swiper review-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide box">
                    <i class="fas fa-quote-right"></i>
                    <p class="reviewText">tempat nya nyaman</p>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>putra rakap - satisfied client</p>
                </div>

                <div class="swiper-slide box">
                    <i class="fas fa-quote-right"></i>
                    <p class="reviewText">kopi nya enak coy</p>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>kapka - satisfied client</p>
                </div>

                <div class="swiper-slide box">
    
                    <i class="fas fa-quote-right"></i>
                    <p class="reviewText">tempatnya cozy</p>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>ilham - satisfied client</p>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
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

        <p class="credit">created by kelompok 7 | all rights reserved</p>
    </footer>

    <!-- SWIPER -->
    <script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>

    <!-- Custom JS File Link  -->
    <script src="{{ asset('assets/js/script.js') }}"></script>

</body>

</html>