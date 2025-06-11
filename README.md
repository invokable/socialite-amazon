# Socialite for Login with Amazon

https://developer.amazon.com/apps-and-games/login-with-amazon

## Overview

This package provides a Laravel Socialite provider for **Login with Amazon**, allowing you to easily add Amazon OAuth authentication to your Laravel applications. It extends Laravel Socialite with support for Amazon's OAuth 2.0 service, enabling users to log in using their Amazon accounts.

**Key Features:**
- Seamless integration with Laravel Socialite
- Support for Amazon OAuth 2.0 authentication flow
- Customizable OAuth scopes (profile, postal_code, etc.)
- Retrieves user profile information from Amazon

## Requirements
- PHP >= 8.0

> No version restrictions. It may stop working in future versions.

## Installation
```
composer require revolution/socialite-amazon
```

### config/services.php

```php
    'amazon' => [
        'client_id'     => env('AMAZON_LOGIN_ID'),
        'client_secret' => env('AMAZON_LOGIN_SECRET'),
        'redirect'      => env('AMAZON_LOGIN_REDIRECT'),
    ],
```

### .env

To obtain the required credentials, visit the Amazon Developer Console and create a Login with Amazon application: https://developer.amazon.com/apps-and-games/login-with-amazon

```
AMAZON_LOGIN_ID=
AMAZON_LOGIN_SECRET=
AMAZON_LOGIN_REDIRECT=
```

## Usage

routes/web.php
```php
Route::get('/', [AmazonController::class, 'index']);
Route::get('callback', [AmazonController::class, 'callback']);
```

AmazonController

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AmazonController extends Controller
{
    public function index()
    {
        return Socialite::driver('amazon')->redirect();
    }

    public function callback()
    {
        $user = Socialite::driver('amazon')->user();
        dd($user);
    }
}

```

## LICENCE
MIT
