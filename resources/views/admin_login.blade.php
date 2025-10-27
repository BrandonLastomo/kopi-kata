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
    <div class="sign-form-container">
        <h1 class="heading">Admin Sign In</h1>

        <form action="{{ route('login.admin.post') }}" method="POST">
            @csrf
      <label for="email">Email Address</label>
      <input type="email" name="email" id="email" placeholder="Email" required />
      <label for="password">Password</label>
      <input type="password" name="password" id="password" placeholder="Password" required />
            <button type="submit" class="form-btn">Sign In</button>
        </form>
    </div>
</body>

</html>