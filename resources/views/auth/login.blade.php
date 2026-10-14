<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Franchise Management System</title>
    <!-- Offline Bundled Assets -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
            padding: 1.5rem;
        }
        .login-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 460px;
            width: 100%;
        }
        .login-header {
            background: #0f172a;
            color: #ffffff;
            padding: 2rem 1.75rem 1.75rem;
            text-align: center;
        }
        .demo-btn {
            font-size: 0.78rem;
            padding: 0.35rem 0.6rem;
            border-radius: 0.35rem;
            cursor: pointer;
            transition: all 0.15s ease;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-25 rounded-circle p-3 mb-3">
                <i class="bi bi-shop-window text-info fs-1"></i>
            </div>
            <h4 class="fw-bold mb-1 text-white">Franchise Portal</h4>
            <p class="text-secondary small mb-0">Multi-Outlet Enterprise Management Portal</p>
        </div>

        <div class="p-4 p-md-4">
            @if(session('status'))
                <div class="alert alert-info py-2 small mb-3">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-muted">Corporate Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', 'admin@franchise.local') }}" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold text-muted">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" value="Password123!" required>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                        <label class="form-check-label small text-muted" for="remember">Keep signed in</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Franchise Portal
                </button>
            </form>

            <!-- Quick Demo Accounts -->
            <div class="mt-4 pt-3 border-top">
                <div class="small fw-bold text-muted mb-2 text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                    <i class="bi bi-stars text-warning me-1"></i> 1-Click Demo Profiles (Pre-loaded)
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-outline-danger demo-btn" onclick="fillCreds('admin@franchise.local', 'Password123!')">
                        HQ Super Admin
                    </button>
                    <button type="button" class="btn btn-outline-primary demo-btn" onclick="fillCreds('owner@franchise.local', 'Password123!')">
                        Franchise Owner
                    </button>
                    <button type="button" class="btn btn-outline-info demo-btn" onclick="fillCreds('manager@franchise.local', 'Password123!')">
                        Store Manager
                    </button>
                    <button type="button" class="btn btn-outline-secondary demo-btn" onclick="fillCreds('staff@franchise.local', 'Password123!')">
                        Store Cashier
                    </button>
                </div>
            </div>

            <div class="mt-4 text-center">
                <small class="text-muted d-block" style="font-size: 0.75rem;">
                    PostgreSQL 18 &bull; Laravel 13 &bull; Internal Network Ready
                </small>
            </div>
        </div>
    </div>

    <script>
        function fillCreds(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
