<?php
declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function requireAuth(): void
    {
        if (!current_user()) {
            flash('warning', 'Please sign in to continue.');
            redirect('login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!is_admin()) {
            http_response_code(403);
            exit('Admin access required.');
        }
    }
}
