<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head')
    </head>
    <body class="login-page bg-body-tertiary">
        <div class="login-box">
            <div class="card card-outline card-primary">
                <div class="card-header text-center">
                    <span class="h1"><b>TKP</b> AG</span>
                </div>
                <div class="card-body login-card-body">
                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email" required autofocus>
                            <div class="input-group-text"><span class="bi bi-envelope"></span></div>
                        </div>
                        @error('email')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                        <div class="input-group mb-3">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                            <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
        @include('layouts.partials.scripts')
    </body>
</html>
