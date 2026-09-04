<?php

namespace Core\Auth;

class AuthMiddleware
{
    public function handle(): bool
    {
        Auth::check();

        if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            Csrf::validate();
        }

        return true;
    }
}
