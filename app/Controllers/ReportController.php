<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Activity;
use App\Models\Claim;
use App\Models\Item;

class ReportController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        view('reports/index', [
            'title' => 'Reports',
            'stats' => array_merge(Item::stats(), ['pending_claims' => Claim::pendingCount()]),
            'items' => Item::search([]),
            'activities' => Activity::recent(20),
            'smsLogs' => Activity::smsLogs(20),
        ]);
    }
}
