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
                <p class="name">{{ auth()->check() ? "Hi, ". auth()->user()->name : "Welcome!" }}</p>
                <a class="log" href="{{ route('logout') }}" class="log">Logout</a>
            @else
                <a href="{{ route('login') }}" class="log">Login</a>
            @endif
        </div>
    </nav>
</header>