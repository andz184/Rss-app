@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center font-weight-light my-2">Test Login</h3>
                    <div class="text-center text-white small">Using external API via proxy: aiemployee.site</div>
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
                            <button class="btn btn-outline-secondary" type="button" id="apiLoginBtn">Login via External API</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small">
                        <a href="{{ route('password.request') }}">Forgot Password?</a>
                    </div>
                    <div class="small mt-2">
                        <a href="{{ route('test.register') }}">Need an account? Try test registration!</a>
                    </div>
                </div>
            </div>

            <!-- API Response Display Section -->
            <div class="card mt-4 d-none" id="apiResponseCard">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">API Response</h5>
                    <span class="badge" id="statusBadge"></span>
                </div>
                <div class="card-body">
                    <pre id="apiResponseData" class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"></pre>
                </div>
                <div class="card-footer" id="apiResponseActions" style="display: none;">
                    <div class="d-grid">
                        <button class="btn btn-success" id="copyTokenBtn">Copy Access Token</button>
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
    const apiResponseCard = document.getElementById('apiResponseCard');
    const apiResponseData = document.getElementById('apiResponseData');
    const statusBadge = document.getElementById('statusBadge');
    const apiResponseActions = document.getElementById('apiResponseActions');
    const copyTokenBtn = document.getElementById('copyTokenBtn');

    apiLoginBtn.addEventListener('click', function(e) {
        e.preventDefault();

        const email = document.getElementById('inputEmail').value;
        const password = document.getElementById('inputPassword').value;

        // Validate inputs
        if (!email || !password) {
            showApiResponse({
                error: 'Email and password are required'
            }, 400);
            return;
        }

        // Show loading state
        apiResponseCard.classList.remove('d-none');
        apiResponseActions.style.display = 'none';
        apiResponseData.textContent = 'Sending request via proxy to aiemployee.site API...';
        statusBadge.textContent = 'Pending';
        statusBadge.className = 'badge bg-warning';

        fetch('/api/proxy/auth/login', {
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
        .then(response => {
            // First capture status code
            const statusCode = response.status;

            // Then parse the JSON
            return response.json().then(data => {
                return { data, statusCode };
            }).catch(error => {
                // Handle non-JSON responses
                return {
                    data: { error: 'Invalid JSON response', rawResponse: response.text() },
                    statusCode: statusCode
                };
            });
        })
        .then(({ data, statusCode }) => {
            showApiResponse(data, statusCode);

            if (data.access_token) {
                localStorage.setItem('access_token', data.access_token);
                if (data.user) {
                    localStorage.setItem('user', JSON.stringify(data.user));
                }

                // Show the copy token button
                apiResponseActions.style.display = 'block';

                // Add success message in the response display
                apiResponseData.innerHTML += '\n\n✅ Login successful! Token stored in localStorage.';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showApiResponse({
                error: 'Network error or server not responding',
                details: error.toString()
            }, 500);
        });
    });

    copyTokenBtn.addEventListener('click', function() {
        const token = localStorage.getItem('access_token');
        if (token) {
            navigator.clipboard.writeText(token).then(function() {
                copyTokenBtn.textContent = 'Token Copied!';
                setTimeout(() => {
                    copyTokenBtn.textContent = 'Copy Access Token';
                }, 2000);
            }, function(err) {
                console.error('Could not copy token: ', err);
            });
        }
    });

    function showApiResponse(data, statusCode) {
        apiResponseCard.classList.remove('d-none');
        apiResponseData.textContent = JSON.stringify(data, null, 2);

        statusBadge.textContent = statusCode;

        if (statusCode >= 200 && statusCode < 300) {
            statusBadge.className = 'badge bg-success';
        } else if (statusCode >= 400 && statusCode < 500) {
            statusBadge.className = 'badge bg-danger';
        } else if (statusCode >= 500) {
            statusBadge.className = 'badge bg-secondary';
        } else {
            statusBadge.className = 'badge bg-primary';
        }
    }
});
</script>
@endpush
@endsection
