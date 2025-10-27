<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kopi & Kata - Login</title>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
  <div class="sign-form-container">
    <h1 class="heading">Sign In</h1>
    @if (session('success'))
        <div id="success-message">
            {{ session('success') }}
        </div>
    @elseif ($errors->has('email'))
        <div id="error-message">
            <strong>{{ $errors->first('email') }}</strong>
        </div>
    @elseif ($errors->has('password'))
        <div id="error-message">
            <strong>{{ $errors->first('password') }}</strong>
        </div>
    @endif
    <form action="{{ route('login.post') }}" method="POST">
      @csrf
      <label for="email">email</label>
      <input type="email" name="email" id="email" placeholder="Email" required />
      <label for="password">Password</label>
      <input type="password" name="password" id="password" placeholder="Password" required />
      <button type="submit" class="form-btn">Sign In</button>
    </form>
    <p class="form-footer">Don't have an account? <a href="{{ route('register') }}">Create account</a></p>
  </div>
</body>

</html>