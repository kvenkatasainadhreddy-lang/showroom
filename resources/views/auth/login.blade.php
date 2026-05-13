<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Showroom ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0d1b2a 0%, #1b2838 50%, #0d6efd22 100%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .login-card {
            background: rgba(255,255,255,.97); border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,.35); padding: 2.5rem; width: 100%; max-width: 420px;
        }
        .brand-badge {
            width: 56px; height: 56px; background: #0d6efd; border-radius: 16px;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;
        }
        .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 .2rem rgba(13,110,253,.15); }
        .btn-login { background: #0d6efd; border: none; padding: .7rem; font-weight: 600; letter-spacing: .02em; border-radius: 10px; }
        .btn-login:hover { background: #0b5ed7; }
        .input-group-text { background: #f8f9fa; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <div class="brand-badge">
            <i class="bi bi-truck text-white fs-3"></i>
        </div>
        <h4 class="fw-700 mb-1">Showroom ERP</h4>
        <p class="text-muted small">Sign in to your account</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-sm py-2 small">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-500">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', 'admin@showroom.com') }}" placeholder="you@showroom.com" required autofocus>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-500">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required id="passInput">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePass()">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Remember me</label>
            </div>
        </div>
        <button type="submit" class="btn btn-login text-white w-100">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
        </button>
    </form>

    <div class="text-center mt-4 pt-3 border-top">
        <p class="text-muted small mb-1">Demo Accounts:</p>
        <div class="d-flex gap-2 justify-content-center">
            <span class="badge bg-primary-subtle text-primary">admin@showroom.com</span>
            <span class="badge bg-success-subtle text-success">sales@showroom.com</span>
        </div>
        <p class="text-muted" style="font-size:.7rem">Password: <code>password</code></p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass() {
    const inp = document.getElementById('passInput');
    const ico = document.getElementById('eyeIcon');
    if (inp.type === 'password') { inp.type = 'text'; ico.className = 'bi bi-eye-slash'; }
    else { inp.type = 'password'; ico.className = 'bi bi-eye'; }
}
</script>
</body>
</html>
