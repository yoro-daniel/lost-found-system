<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Item;
use App\Services\ActivityLogger;
use App\Services\CloudinaryUploadService;
use App\Services\EmailService;
use RuntimeException;

class ItemController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'location_id' => $_GET['location_id'] ?? '',
        ];

        view('items/index', [
            'title' => 'Item Registry',
            'items' => Item::search($filters),
            'categories' => Item::categories(),
            'locations' => Item::locations(),
            'filters' => $filters,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        view('items/form', [
            'title' => 'Report Item',
            'item' => null,
            'categories' => Item::categories(),
            'locations' => Item::locations(),
        ]);
    }

    public function edit(): void
    {
        $this->requireAuth();
        $item = Item::find((int) ($_GET['id'] ?? 0));
        if (!$item) {
            flash('danger', 'Item not found.');
            redirect('items');
        }

        view('items/form', [
            'title' => 'Edit Item',
            'item' => $item,
            'categories' => Item::categories(),
            'locations' => Item::locations(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        verify_csrf();
        $data = $this->validatedData();
        $data['reporter_id'] = current_user()['id'];
        $data['image_path'] = $this->uploadImage('items.create');
        $id = Item::create($data);
        $item = Item::find($id);

        ActivityLogger::log('item_created', 'Reported ' . $item['type'] . ' item: ' . $item['title']);
        $email = new EmailService();
        $email->sendReportConfirmation($item, $item['contact_email']);

        foreach (Item::findPotentialMatches($item) as $match) {
            $email->sendMatchFound($item, $match, $item['contact_email']);
            if (!empty($match['reporter_email'])) {
                $email->sendMatchFound($match, $item, $match['reporter_email']);
            }
        }

        flash('success', 'Item report saved. Matching notifications were attempted.');
        redirect('items');
    }

    public function update(): void
    {
        $this->requireAuth();
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $data = $this->validatedData(true);
        $data['image_path'] = $this->uploadImage('items.edit', ['id' => $id]);
        Item::update($id, $data);
        ActivityLogger::log('item_updated', 'Updated item #' . $id);
        flash('success', 'Item updated successfully.');
        redirect('items');
    }

    public function delete(): void
    {
        $this->requireAdmin();
        verify_csrf();
        Item::delete((int) ($_POST['id'] ?? 0));
        ActivityLogger::log('item_deleted', 'Deleted an item record.');
        flash('success', 'Item deleted.');
        redirect('items');
    }

    private function validatedData(bool $updating = false): array
    {
        $fields = [
            'type' => 'Type',
            'category_id' => 'Category',
            'location_id' => 'Location',
            'title' => 'Item title',
            'description' => 'Description',
            'date_seen' => 'Date',
            'contact_email' => 'Contact email',
        ];
        if ($updating) {
            $fields['status'] = 'Status';
        }
        $errors = Validator::required($_POST, $fields);
        $errors = array_merge($errors, Validator::email($_POST['contact_email'] ?? '', 'Contact email'));

        if (!in_array($_POST['type'] ?? '', ['lost', 'found'], true)) {
            $errors[] = 'Type must be lost or found.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                flash('danger', $error);
            }
            redirect($updating ? 'items.edit' : 'items.create', $updating ? ['id' => $_POST['id']] : []);
        }

        return [
            'type' => $_POST['type'],
            'category_id' => (int) $_POST['category_id'],
            'location_id' => (int) $_POST['location_id'],
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'date_seen' => $_POST['date_seen'],
            'contact_email' => trim($_POST['contact_email']),
            'contact_phone' => trim($_POST['contact_phone'] ?? ''),
            'status' => $_POST['status'] ?? 'open',
        ];
    }

    private function uploadImage(string $fallbackRoute, array $fallbackParams = []): ?string
    {
        if (empty($_FILES['image']['name'])) {
            return null;
        }

        try {
            return (new CloudinaryUploadService())->uploadItemImage($_FILES['image']);
        } catch (RuntimeException $exception) {
            flash('danger', $exception->getMessage());
            redirect($fallbackRoute, $fallbackParams);
        }
    }
}
