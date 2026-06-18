<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Inventory\Exports\SuppliersExport;
use Modules\Inventory\Models\Supplier;

class SupplierController extends Controller
{
    /**
     * Display a list of all suppliers.
     */
    public function index(): View
    {
        $suppliers = Supplier::all();

        return view('inventory::suppliers.index', compact('suppliers'));
    }

    public function show(Supplier $supplier): View
    {
        return view('inventory::suppliers.show', compact('supplier'));
    }

    /**
     * Store a new supplier.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        try {
            Supplier::create($validatedData);

            return redirect()->route('inventory.suppliers.index')
                ->with('success', 'Supplier added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding supplier: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()
                ->with('error', 'Failed to add supplier. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier)
    {
        return view('inventory::suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,'.$supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        try {
            $supplier->update($validatedData);

            return redirect()->route('inventory.suppliers.index')
                ->with('success', 'Supplier updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating supplier: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update supplier. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();

            return redirect()->route('inventory.suppliers.index')
                ->with('success', 'Supplier deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting supplier: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to delete supplier. Please try again.');
        }
    }

    public function export()
    {
        $filename = 'suppliers-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new SuppliersExport, $filename);
    }

    public function showImport(): View
    {
        return view('inventory::suppliers.import');
    }

    public function downloadTemplate()
    {
        $headings = ['name', 'contact_person', 'email', 'phone', 'address'];

        return Excel::download(new class($headings) implements FromArray, WithHeadings
        {
            protected array $headings;

            public function __construct(array $headings)
            {
                $this->headings = $headings;
            }

            public function array(): array
            {
                return [
                    ['Example Supplier', 'John Doe', 'supplier@example.com', '080-123-4567', '123 Main Street'],
                ];
            }

            public function headings(): array
            {
                return $this->headings;
            }
        }, 'supplier-import-template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $imported = 0;
        $errors = [];

        try {
            $rows = Excel::toArray(new class implements ToArray
            {
                public function array(array $array)
                {
                    return $array;
                }
            }, $request->file('file'));

            $rows = $rows[0] ?? [];
            $headings = array_map('strtolower', $rows[0] ?? []);
            unset($rows[0]);

            $nameIdx = array_search('name', $headings);
            $contactIdx = array_search('contact_person', $headings);
            $emailIdx = array_search('email', $headings);
            $phoneIdx = array_search('phone', $headings);
            $addressIdx = array_search('address', $headings);

            if ($nameIdx === false) {
                return back()->with('error', 'The spreadsheet must have a "name" column.');
            }

            foreach ($rows as $i => $row) {
                $rowNum = $i + 2;

                if (empty(array_filter($row))) {
                    continue;
                }

                $name = trim($row[$nameIdx] ?? '');
                if (empty($name)) {
                    $errors[] = "Row {$rowNum}: name is required.";

                    continue;
                }

                try {
                    Supplier::create([
                        'name' => $name,
                        'contact_person' => $contactIdx !== false ? trim($row[$contactIdx] ?? '') : null,
                        'email' => $emailIdx !== false ? trim($row[$emailIdx] ?? '') : null,
                        'phone' => $phoneIdx !== false ? trim($row[$phoneIdx] ?? '') : null,
                        'address' => $addressIdx !== false ? trim($row[$addressIdx] ?? '') : null,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNum} ({$name}): ".$e->getMessage();
                }
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to read file: '.$e->getMessage());
        }

        $message = "Imported {$imported} supplier(s) successfully.";
        if (! empty($errors)) {
            $message .= ' '.count($errors).' error(s): '.implode('; ', array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $message .= ' (and '.(count($errors) - 10).' more)';
            }
        }

        return redirect()->route('inventory.suppliers.index')->with(
            $errors ? 'warning' : 'success',
            $message
        );
    }
}
