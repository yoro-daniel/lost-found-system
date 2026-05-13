<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Activity;
use App\Models\Claim;
use App\Models\Item;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $stats = Item::stats();
        $stats['pending_claims'] = Claim::pendingCount();
        $stats['users'] = count(User::all());
        $byCategory = Database::connection()->query(
            'SELECT c.name, COUNT(i.id) AS total
             FROM categories c LEFT JOIN items i ON i.category_id = c.id
             GROUP BY c.id ORDER BY c.name'
        )->fetchAll();

        view('dashboard/index', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'byCategory' => $byCategory,
            'activities' => Activity::recent(8),
            'smsLogs' => Activity::smsLogs(6),
            'users' => User::all(),
        ]);
    }
}
