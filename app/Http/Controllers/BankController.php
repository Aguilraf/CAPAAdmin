<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $banks = Bank::query()
            ->withCount('movements')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($bankQuery) use ($request) {
                    $bankQuery->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('account_number', 'like', '%' . $request->search . '%')
                        ->orWhere('account_name', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('name')
            ->orderBy('account_number')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Banks/Index', [
            'banks' => $banks,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Banks/Form');
    }

    public function store(Request $request)
    {
        Bank::create($this->validated($request));

        return redirect()->route('banks.index')->with('success', 'Banco registrado correctamente.');
    }

    public function edit(Bank $bank)
    {
        return Inertia::render('Banks/Form', ['bank' => $bank]);
    }

    public function update(Request $request, Bank $bank)
    {
        $bank->update($this->validated($request, $bank));

        return redirect()->route('banks.index')->with('success', 'Banco actualizado correctamente.');
    }

    public function destroy(Bank $bank)
    {
        if ($bank->movements()->exists()) {
            return back()->with('error', 'No se puede eliminar un banco que ya tiene movimientos importados.');
        }

        $bank->delete();

        return redirect()->route('banks.index')->with('success', 'Banco eliminado correctamente.');
    }

    private function validated(Request $request, ?Bank $bank = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('banks')->where(fn ($query) => $query->where('name', $request->name))->ignore($bank),
            ],
            'account_name' => 'nullable|string|max:255',
            'currency' => 'required|string|size:3',
            'import_template' => ['required', Rule::in(['hsbc', 'azteca', 'custom'])],
            'active' => 'boolean',
        ]);
    }
}
