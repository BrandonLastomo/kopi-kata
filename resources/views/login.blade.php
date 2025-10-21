<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kopi & Kata - Login</title>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
  @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
  @endif
  <div class="form-container">
    <div class="back-btn" onclick="history.back()">&#8592;</div>
    <h2>Kopi & Kata</h2>
    <form action="{{ route('login.post') }}" method="POST">
      @csrf
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Sign in</button>
    </form>
    <div class="form-footer">
      <p>Don't have an account? <a href="{{ route('register') }}">create account</a></p>
    </div>
  </div>
</body>

</html>