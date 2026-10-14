<?php

namespace App\Http\Controllers;

use App\Models\BulkImport;
use App\Services\BulkImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BulkImportController extends Controller
{
    public function __construct(
        protected BulkImportService $bulkImportService
    ) {}

    public function index(): View
    {
        $imports = BulkImport::with('user')->latest()->paginate(10);

        return view('admin.bulk_imports.index', compact('imports'));
    }

    public function downloadTemplate(string $type): Response
    {
        if (! in_array($type, ['outlets', 'products', 'inventory', 'staff', 'sales'], true)) {
            abort(404, 'Invalid template type requested.');
        }

        $content = $this->bulkImportService->getTemplateContent($type);
        $filename = "franchise_{$type}_import_template.csv";

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:outlets,products,inventory,staff,sales'],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'], // up to 10MB CSV
        ]);

        $result = $this->bulkImportService->processImport(
            $request->file('file'),
            $validated['type'],
            $request->user()
        );

        $msg = "Bulk import completed. {$result['imported_count']} rows imported successfully, {$result['failed_count']} failed.";

        if ($result['failed_count'] > 0) {
            return redirect()->route('admin.bulk-imports.index')
                ->with('status', $msg)
                ->with('import_errors', $result['errors']);
        }

        return redirect()->route('admin.bulk-imports.index')
            ->with('status', $msg);
    }

    public function show(BulkImport $bulkImport): View
    {
        $bulkImport->load('user');

        return view('admin.bulk_imports.show', compact('bulkImport'));
    }
}
