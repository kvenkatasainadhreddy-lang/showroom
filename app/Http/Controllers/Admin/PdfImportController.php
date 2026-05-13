<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PdfImport;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

class PdfImportController extends Controller
{
    public function index()
    {
        $imports = PdfImport::with('supplier', 'creator')->latest()->paginate(20);
        return view('admin.pdf-imports.index', compact('imports'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('admin.pdf-imports.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pdf'         => 'required|file|mimes:pdf|max:10240',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes'       => 'nullable|string',
        ]);

        $file     = $request->file('pdf');
        $filename = $file->getClientOriginalName();
        $path     = $file->store('pdf-imports', 'local');

        [$items, $parseNotes] = $this->parsePdf(Storage::disk('local')->path($path));

        $import = PdfImport::create([
            'filename'     => $filename,
            'path'         => $path,
            'status'       => count($items) > 0 ? 'processed' : 'failed',
            'parsed_items' => $items,
            'supplier_id'  => $request->supplier_id,
            'notes'        => $parseNotes,
            'created_by'   => auth()->id(),
        ]);

        if (count($items) === 0) {
            return redirect()->route('admin.pdf-imports.show', $import)
                ->with('error', 'Could not extract items from this PDF. ' . $parseNotes);
        }

        return redirect()->route('admin.pdf-imports.show', $import)
            ->with('success', count($items) . ' items extracted. Please review and confirm.');
    }

    public function show(PdfImport $pdfImport)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.pdf-imports.show', compact('pdfImport', 'categories'));
    }

    public function confirm(Request $request, PdfImport $pdfImport)
    {
        if ($pdfImport->status === 'confirmed') {
            return back()->with('error', 'This import has already been confirmed.');
        }

        $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.name'        => 'required|string',
            'items.*.sku'         => 'nullable|string',
            'items.*.qty'         => 'required|numeric|min:0.01',
            'items.*.price'       => 'required|numeric|min:0',
            'items.*.category_id' => 'nullable|exists:categories,id',
            'items.*.branch_id'   => 'nullable|exists:branches,id',
        ]);

        foreach ($request->items as $row) {
            $product = Product::firstOrCreate(
                ['sku' => $row['sku'] ?: ('IMP-' . Str::upper(Str::random(6)))],
                [
                    'name'           => $row['name'],
                    'type'           => 'part',
                    'category_id'    => $row['category_id'] ?? null,
                    'purchase_price' => $row['price'],
                    'selling_price'  => round($row['price'] * 1.15, 2),
                ]
            );

            $branchId = $row['branch_id'] ?? null;
            $inv = Inventory::firstOrCreate(
                ['product_id' => $product->id, 'branch_id' => $branchId],
                ['quantity' => 0, 'min_quantity' => 0]
            );
            $before = $inv->quantity;
            $inv->increment('quantity', $row['qty']);

            StockMovement::create([
                'product_id'      => $product->id,
                'branch_id'       => $branchId,
                'type'            => 'in',
                'quantity'        => $row['qty'],
                'before_quantity' => $before,
                'after_quantity'  => $before + $row['qty'],
                'reference_type'  => 'pdf_import',
                'reference_id'    => $pdfImport->id,
                'notes'           => 'PDF Import: ' . $pdfImport->filename,
                'created_by'      => auth()->id(),
            ]);
        }

        $pdfImport->update(['status' => 'confirmed']);

        return redirect()->route('admin.pdf-imports.index')
            ->with('success', 'Import confirmed — ' . count($request->items) . ' items added to stock.');
    }

    private function parsePdf(string $filePath): array
    {
        $items = [];
        $notes = '';

        try {
            $parser = new Parser();
            $pdf    = $parser->parseFile($filePath);
            $text   = $pdf->getText();

            if (empty(trim($text))) {
                return [[], 'PDF appears to be image-based (scanned). Text extraction not possible. Please enter items manually.'];
            }

            $lines = preg_split('/\r?\n/', $text);
            foreach ($lines as $line) {
                $line = trim($line);
                if (strlen($line) < 5) continue;

                if (preg_match('/^(.+?)\s+(\d{1,5})\s+([\d,]+\.?\d{0,2})\s*$/', $line, $m)) {
                    $name  = trim($m[1]);
                    $qty   = (float) $m[2];
                    $price = (float) str_replace(',', '', $m[3]);

                    if ($qty <= 0 || $price <= 0 || $qty > 9999) continue;
                    if (preg_match('/total|subtotal|tax|gst|discount|amount|invoice|bill|date|sl\.?\s*no/i', $name)) continue;

                    $sku = null;
                    if (preg_match('/^(.+?)\s{2,}([A-Z0-9\-\/]+)$/', $name, $nm)) {
                        $name = trim($nm[1]);
                        $sku  = trim($nm[2]);
                    }

                    $items[] = ['name' => $name, 'sku' => $sku, 'qty' => $qty, 'price' => $price];
                }
            }

            if (empty($items)) {
                $notes = 'Text extracted but no item rows matched the expected pattern (Name  Qty  Price). You can enter items manually below.';
            } else {
                $notes = 'Extracted ' . count($items) . ' item(s). Please verify before confirming.';
            }

        } catch (\Throwable $e) {
            $notes = 'Parse error: ' . $e->getMessage();
        }

        return [$items, $notes];
    }
}
