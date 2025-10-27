<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kopi & Kata - Sign Up</title>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
  <div class="sign-form-container">
    <h1 class="heading">Create Your Account</h1>
    @if ($errors->has('email'))
        <div id="error-message">
            <strong>{{ $errors->first('email') }}</strong>
        </div>
    @elseif ($errors->has('password'))
        <div id="error-message">
            <strong>{{ $errors->first('password') }}</strong>
        </div>
    @endif
    <form action="{{ route('register.post') }}" method="POST">
      @csrf
      <label for="name">Name</label>
      <input type="text" name="name" id="name" placeholder="Username" required />
      <label for="email">Email Address</label>
      <input type="email" name="email" id="email" placeholder="Email" required />
      <label for="password">Password</label>
      <input type="password" name="password" id="password" placeholder="Password" required />
      {{-- <input type="password" name="confirm_password" placeholder="Confirm Password" required /> --}}
        <button type="submit" class="form-btn">Register</button>
    </form>
    <p class="form-footer">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
  </div>
  <script src="{{ asset('assets/js/script.js') }}"></script>
</body>

</html>