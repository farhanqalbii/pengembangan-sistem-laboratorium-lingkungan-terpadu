<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BakuMutu;
use App\Models\LogAktivitas;
use App\Models\Parameter;
use Illuminate\Http\Request;

class ParameterController extends Controller
{
    public function index(Request $request)
    {
        $query = Parameter::with('qualityStandard');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('test_method', 'like', "%{$search}%");
            });
        }

        $parameters = $query->paginate(10)->withQueryString();
        $categories = Parameter::distinct('category')->pluck('category');

        return view('admin.parameters.index', compact('parameters', 'categories'));
    }

    public function create()
    {
        return view('admin.parameters.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'unit' => ['required', 'string'],
            'test_method' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            // Baku mutu
            'min_value' => ['nullable', 'numeric'],
            'max_value' => ['nullable', 'numeric'],
            'regulation_source' => ['required', 'string'],
        ]);

        $param = Parameter::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'unit' => $validated['unit'],
            'test_method' => $validated['test_method'],
            'price' => $validated['price'],
        ]);

        BakuMutu::create([
            'parameter_id' => $param->id,
            'min_value' => $validated['min_value'] !== null ? (float) $validated['min_value'] : null,
            'max_value' => $validated['max_value'] !== null ? (float) $validated['max_value'] : null,
            'unit' => $validated['unit'],
            'regulation_source' => $validated['regulation_source'],
            'status' => 'Aktif',
        ]);

        LogAktivitas::record('Tambah Parameter Uji', 'Master Parameter', "Menambah parameter uji baru: {$param->name} ({$param->category})", auth()->id());

        return redirect()->route('admin.parameters.index')
            ->with('success', "Parameter uji {$param->name} dan Baku Mutu berhasil ditambahkan.");
    }

    public function edit(Parameter $parameter)
    {
        $parameter->load('qualityStandard');

        return view('admin.parameters.edit', compact('parameter'));
    }

    public function update(Request $request, Parameter $parameter)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'unit' => ['required', 'string'],
            'test_method' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            // Baku mutu
            'min_value' => ['nullable', 'numeric'],
            'max_value' => ['nullable', 'numeric'],
            'regulation_source' => ['required', 'string'],
        ]);

        $parameter->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'unit' => $validated['unit'],
            'test_method' => $validated['test_method'],
            'price' => $validated['price'],
        ]);

        $qs = $parameter->qualityStandard;
        if ($qs) {
            $qs->update([
                'min_value' => $validated['min_value'] !== null ? (float) $validated['min_value'] : null,
                'max_value' => $validated['max_value'] !== null ? (float) $validated['max_value'] : null,
                'unit' => $validated['unit'],
                'regulation_source' => $validated['regulation_source'],
            ]);
        } else {
            BakuMutu::create([
                'parameter_id' => $parameter->id,
                'min_value' => $validated['min_value'] !== null ? (float) $validated['min_value'] : null,
                'max_value' => $validated['max_value'] !== null ? (float) $validated['max_value'] : null,
                'unit' => $validated['unit'],
                'regulation_source' => $validated['regulation_source'],
                'status' => 'Aktif',
            ]);
        }

        LogAktivitas::record('Update Parameter Uji', 'Master Parameter', "Memperbarui parameter uji: {$parameter->name}", auth()->id());

        return redirect()->route('admin.parameters.index')
            ->with('success', "Parameter uji {$parameter->name} berhasil diperbarui.");
    }

    public function destroy(Parameter $parameter)
    {
        $name = $parameter->name;
        $parameter->delete();

        LogAktivitas::record('Hapus Parameter Uji', 'Master Parameter', "Menghapus parameter uji: {$name}", auth()->id());

        return redirect()->route('admin.parameters.index')
            ->with('success', "Parameter uji {$name} berhasil dihapus.");
    }
}
