<?php
namespace Core;

class AuthMiddleware {
    public function handle(): bool {
        // Usa a classe Auth do Core para verificar a sessão
        Auth::check(); 
        return true;
    }
}