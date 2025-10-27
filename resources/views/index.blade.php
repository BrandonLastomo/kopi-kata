@extends('app')

@section('content')

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

    <!-- SWIPER -->
    <script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>

    <!-- Custom JS File Link  -->
    <script src="{{ asset('assets/js/script.js') }}"></script>
@endsection