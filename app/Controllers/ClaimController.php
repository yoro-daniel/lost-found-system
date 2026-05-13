<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Claim;
use App\Models\Item;
use App\Services\ActivityLogger;
use App\Services\SmsService;

class ClaimController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        view('claims/index', ['title' => 'Claims', 'claims' => Claim::all(), 'items' => Item::search([])]);
    }

    public function store(): void
    {
        $this->requireAuth();
        verify_csrf();
        $errors = Validator::required($_POST, [
            'item_id' => 'Item',
            'claimant_name' => 'Claimant name',
            'claimant_email' => 'Claimant email',
            'proof_text' => 'Proof of ownership',
        ]);
        $errors = array_merge($errors, Validator::email($_POST['claimant_email'] ?? '', 'Claimant email'));

        if ($errors) {
            foreach ($errors as $error) {
                flash('danger', $error);
            }
            redirect('claims');
        }

        Claim::create([
            'item_id' => (int) $_POST['item_id'],
            'user_id' => current_user()['id'],
            'claimant_name' => trim($_POST['claimant_name']),
            'claimant_email' => trim($_POST['claimant_email']),
            'claimant_phone' => trim($_POST['claimant_phone'] ?? ''),
            'proof_text' => trim($_POST['proof_text']),
        ]);
        ActivityLogger::log('claim_created', 'Submitted a claim request.');
        flash('success', 'Claim request submitted.');
        redirect('claims');
    }

    public function review(): void
    {
        $this->requireAdmin();
        verify_csrf();
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['approved', 'rejected'], true)) {
            flash('danger', 'Invalid claim decision.');
            redirect('claims');
        }

        $claim = Claim::review((int) ($_POST['claim_id'] ?? 0), $status, current_user()['id']);
        if ($claim) {
            (new SmsService())->sendClaimDecision($claim, $status);
            ActivityLogger::log('claim_reviewed', 'Marked claim #' . $claim['id'] . ' as ' . $status . '.');
        }

        flash('success', 'Claim ' . $status . '. SMS notification was attempted.');
        redirect('claims');
    }
}
