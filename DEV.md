# Development

### Install API with Passport

Install api and passport
```bash
php artisan install:api --passport
```

Update User model by adding `HasApiTokens` trait
```php
// app/Models/User.php
// ...
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    // ...
}
```

Update auth config to use passport
```php
// config/auth.php
// ...
'guards' => [
    // ...
    'api' => [
        'driver' => 'passport',
        'provider' => 'users',
    ],
],
// ...
```

Create passport clients
```bash
php artisan passport:client --name "Personal Token" --personal
php artisan passport:client --name "Password Token" --password
```

List oauth routes
```bash
php artisan route:list --path oauth
```

Create unit tests
```bash
php artisan make:test OAuthTest --phpunit

```

https://laravel.com/docs/11.x/passport#passport-or-sanctum