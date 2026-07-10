<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in · Armani Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #101215; color: #fff; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #17191d; border: 1px solid #22262b; border-radius: 12px; padding: 32px; width: 100%; max-width: 380px; }
        .brand { text-align: center; margin-bottom: 28px; }
        .brand img { max-width: 200px; height: auto; }
        .brand small { display: block; color: #8b8f96; font-weight: 400; text-transform: uppercase; letter-spacing: 1.2px; font-size: 11px; margin-top: 10px; }
        .form-control { background: #101215; border-color: #22262b; color: #fff; }
        .form-control:focus { background: #101215; color: #fff; border-color: #c9a04a; box-shadow: none; }
        .form-label { color: #b7bac0; font-size: 13px; }
        .btn-primary { background: #c9a04a; border-color: #c9a04a; color: #101215; font-weight: 600; }
        .btn-primary:hover { background: #b78d3a; border-color: #b78d3a; color: #101215; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand">
        <img src="{{ asset('logo.png') }}" alt="Armani Properties">
        <small>Sign in</small>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" type="password" id="password" name="password" required>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="remember" name="remember">
            <label class="form-check-label text-muted" for="remember" style="font-size: 13px;">Remember me</label>
        </div>
        <button class="btn btn-primary w-100" type="submit">Log in</button>
    </form>
</div>
</body>
</html>
