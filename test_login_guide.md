# Test Login and Registration System Guide

This document explains how to use the test login and registration system for API testing purposes.

## Overview

Secondary login and registration forms have been created for testing third-party authentication APIs. These test forms provide:

1. Standard web form authentication (using Laravel's authentication)
2. External API-based authentication with aiemployee.site via a proxy to bypass CORS restrictions

## Accessing the Test Forms

- Test Login Form: `/test-login`
- Test Registration Form: `/test-register`

## External API Authentication via Proxy

The "Login via External API" and "Register via External API" buttons send requests to the API endpoints through a local proxy:

#### Login API

```
# Frontend calls this endpoint:
POST /api/proxy/auth/login
Content-Type: application/json
Accept: application/json
X-CSRF-TOKEN: {{csrf_token}}

# Which proxies to:
POST https://aiemployee.site/api/auth/login

{
  "email": "your-email@example.com",
  "password": "your-password"
}
```

#### Register API

```
# Frontend calls this endpoint:
POST /api/proxy/auth/register
Content-Type: application/json
Accept: application/json
X-CSRF-TOKEN: {{csrf_token}}

# Which proxies to:
POST https://aiemployee.site/api/auth/register

{
  "name": "Your Name",
  "email": "your-email@example.com",
  "password": "your-password",
  "password_confirmation": "your-password"
}
```

## API Response Display

A key feature of these test forms is the ability to see the API response directly on the page. When you use the "Login via External API" or "Register via External API" buttons:

1. The form makes an API call to the proxy which forwards it to aiemployee.site
2. A response card appears below the form with:
   - HTTP status code badge (color-coded by status type)
   - Full JSON response in a scrollable pre-formatted container
   - Copy Access Token button (appears when authentication succeeds)
3. If successful, the JWT token is stored in localStorage

This feature makes it easy to:
- Debug API issues
- Verify API functionality
- See exactly what the API returns
- Copy access tokens for use in other applications
- Confirm authentication works correctly

## CORS Issues and Solution

The system includes a proxy solution for CORS (Cross-Origin Resource Sharing) issues. When a web application makes requests to a different domain, browsers enforce CORS security policies that can block these requests.

### Why We Need a Proxy

Direct API calls to external domains (like aiemployee.site) from your browser may fail due to:

1. Browser security policies
2. Missing CORS headers on the external API
3. Preflight OPTIONS requests being rejected

While tools like Postman and n8n can access these APIs directly (they don't enforce CORS), browser JavaScript needs a workaround.

### How Our Proxy Works

1. Your frontend JavaScript makes a request to your own server at `/api/proxy/auth/*`
2. Your Laravel server receives this request
3. Laravel makes a server-side HTTP request to `https://aiemployee.site/api/auth/*`
4. The external API responds to your server
5. Your server forwards this response back to your frontend

This approach eliminates CORS issues because:
- Browser → Your Server: Same-origin request (no CORS problem)
- Your Server → External API: Server-to-server request (CORS irrelevant)

## Error Handling

The forms include comprehensive error handling:
- Input validation before submission
- Proper display of network errors
- Handling of invalid JSON responses
- Clear status code indication
- Detailed error messages

## How to Test the API

1. Fill in the form with valid credentials
2. Click the "Login via External API" or "Register via External API" button
3. View the API response in the card below
4. If successful, use the "Copy Access Token" button to copy the JWT token
5. Use the token in your applications as needed

## API Documentation

Based on the Google Doc shared, the API authentication endpoints are:

### Login
- **URL**: `/api/auth/login`
- **Method**: POST
- **Parameters**: email, password
- **Response**: JWT token with user information

### Register 
- **URL**: `/api/auth/register`
- **Method**: POST
- **Parameters**: name, email, password, password_confirmation
- **Response**: JWT token with user information

### Get Current User
- **URL**: `/api/auth/me`
- **Method**: GET
- **Headers**: Authorization: Bearer {token}
- **Response**: User information

### Logout
- **URL**: `/api/auth/logout`
- **Method**: POST
- **Headers**: Authorization: Bearer {token}
- **Response**: Success message

### Refresh Token
- **URL**: `/api/auth/refresh`
- **Method**: POST
- **Headers**: Authorization: Bearer {token}
- **Response**: New JWT token

## Test Implementation Details

Both forms are implemented with Bootstrap floating labels and modern UI design distinct from the main authentication forms. They provide clear error messages and include validation and cross-navigation between the forms.

The JavaScript portions of these implementations handle the API authentication process and response display, making it easy to test API endpoints without writing custom code. 
