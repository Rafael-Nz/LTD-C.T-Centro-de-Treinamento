<?php
namespace Core\Auth;

class AuthMiddleware {
    public function handle(): bool {
        Auth::check();
        return true;
    }
}
