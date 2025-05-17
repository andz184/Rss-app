# Test Login and Registration System Guide

This document explains how to use the test login and registration system for API testing purposes.

## Overview

Secondary login and registration forms have been created for testing third-party authentication APIs. These test forms provide:

1. Standard web form authentication (using Laravel's authentication)
2. External API-based authentication with aiemployee.site with visible API responses

## Accessing the Test Forms

- Test Login Form: `/test-login`
- Test Registration Form: `/test-register`

## External API Authentication

The "Login via External API" and "Register via External API" buttons send requests to the external API endpoints:

#### Login API

```
POST https://aiemployee.site/api/auth/login
Content-Type: application/json
Accept: application/json

{
  "email": "your-email@example.com",
  "password": "your-password"
}
```

#### Register API

```
POST https://aiemployee.site/api/auth/register
Content-Type: application/json
Accept: application/json

{
  "name": "Your Name",
  "email": "your-email@example.com",
  "password": "your-password",
  "password_confirmation": "your-password"
}
```

## API Response Display

A key feature of these test forms is the ability to see the API response directly on the page. When you use the "Login via External API" or "Register via External API" buttons:

1. The form makes an API call to the respective endpoint
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

## CORS Considerations

The external API endpoints have been configured to allow cross-origin requests from your application. No CSRF token is needed for these external API calls.

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
