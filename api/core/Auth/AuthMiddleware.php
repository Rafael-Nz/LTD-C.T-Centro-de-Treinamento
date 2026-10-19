<?php

namespace Core\Auth;

class AuthMiddleware
{
    public function __construct(private ?string $guard = null) {}

    public static function guard(string $guard): self
    {
        return new self($guard);
    }

    public function handle(): bool
    {
        $guard = $this->guard ?? Auth::context();
        Auth::check($guard);

        if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            Csrf::validate();
        }

        return true;
    }
}
