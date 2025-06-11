class VerifyCsrfToken extends Middleware
{
    // ...existing code...
    protected $except = [
        'api/*' // Exclude API routes from CSRF verification
    ];
    // ...existing code...
}
