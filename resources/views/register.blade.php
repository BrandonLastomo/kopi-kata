<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kopi & Kata - Sign Up</title>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
  <div class="form-container">
    <div class="back-btn" onclick="location.href='index.html'">&#8592;</div>
    <h2>Kopi & Kata</h2>
    <form action="{{ route('register.post') }}" method="POST">
      @csrf
      <input type="text" name="name" placeholder="Username" required />
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      {{-- <input type="password" name="confirm_password" placeholder="Confirm Password" required /> --}}
      <button type="submit">Sign up</button>
    </form>
    <div class="form-footer">
      <p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
  </div>
</body>

</html>