<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Kopi & Kata</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
    <div class="coffee-bg"></div>
    <div class="admin-login-container">
        <h2>Admin Login</h2>

        <form action="{{ route('login.admin.post') }}" method="POST">
            @csrf
            <input type="email" name="email" placeholder="Email" class="box" required>
            <input type="password" name="password" placeholder="Password" class="box" required>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>

</html>