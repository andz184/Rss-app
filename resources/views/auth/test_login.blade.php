@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center font-weight-light my-2">Test Login</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('test.login.submit') }}" id="testLoginForm">
                        @csrf

                        <div class="form-floating mb-3">
                            <input class="form-control" id="inputEmail" type="email" name="email" placeholder="name@example.com" required />
                            <label for="inputEmail">Email Address</label>
                            @error('email')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input class="form-control" id="inputPassword" type="password" name="password" placeholder="Password" required />
                            <label for="inputPassword">Password</label>
                            @error('password')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" id="inputRemember" type="checkbox" name="remember" />
                            <label class="form-check-label" for="inputRemember">Remember Password</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-lg" type="submit">Login</button>
                            <button class="btn btn-outline-secondary" type="button" id="apiLoginBtn">Login via API</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small">
                        <a href="{{ route('password.request') }}">Forgot Password?</a>
                    </div>
                    <div class="small mt-2">
                        <a href="{{ route('register') }}">Need an account? Sign up!</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const apiLoginBtn = document.getElementById('apiLoginBtn');
    const form = document.getElementById('testLoginForm');

    apiLoginBtn.addEventListener('click', function(e) {
        e.preventDefault();

        const email = document.getElementById('inputEmail').value;
        const password = document.getElementById('inputPassword').value;

        fetch('/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                email: email,
                password: password
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.access_token) {
                localStorage.setItem('access_token', data.access_token);
                localStorage.setItem('user', JSON.stringify(data.user));
                window.location.href = '/home';
            } else {
                alert('Login failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during login');
        });
    });
});
</script>
@endpush
@endsection
