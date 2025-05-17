# Test Login System Guide

This document explains how to use the test login system for API testing purposes.

## Overview

A secondary login form has been created entirely separate from the main login system. This test login form provides two login methods:

1. Standard web form login (using Laravel's authentication)
2. API-based login (using JWT authentication)

## Accessing the Test Login Form

The test login form is available at:

```
/test-login
```

## Authentication Methods

### Web Form Login

The web form login uses the standard Laravel authentication system. It submits the form to the server and uses session-based authentication.

### API Login

The API Login button sends a POST request to the API endpoint using fetch API:

```
POST /api/auth/login
Content-Type: application/json
Accept: application/json
X-CSRF-TOKEN: {{csrf_token}}

{
  "email": "your-email@example.com",
  "password": "your-password"
}
```

The API responds with:

```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLC...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "your-email@example.com",
    ...
  }
}
```

On successful API login, the access token and user information are stored in localStorage and the user is redirected to the home page.

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

## Test Login Implementation

The test login form is implemented with Bootstrap floating labels and a modern UI design that's distinct from the main login form. It provides clear error messages and includes links to password reset and registration pages.

The JavaScript portion of the implementation handles the API login process, making it easy to test API authentication without writing custom code. 
