<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class AdminServiceController extends Controller
{
    public function index()
    {
        $q = trim((string)request('q', ''));
        $status = request('status');
        $priceMin = request('price_min');
        $priceMax = request('price_max');

        $query = Service::query()
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%' . $q . '%';
                $qq->where(function ($sub) use ($like) {
                    $sub->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when(is_numeric($priceMin), fn($qq) => $qq->where('price', '>=', (float)$priceMin))
            ->when(is_numeric($priceMax), fn($qq) => $qq->where('price', '<=', (float)$priceMax))
            ->latest();

        $services = $query->paginate(15)->appends(request()->query());
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'price' => 'required|integer|min:0',
        'status' => 'nullable|in:active,inactive',
    ]);

    Service::create([
        'name' => $request->name,
        'description' => $request->description,
        'price' => $request->price,
        'status' => $request->status ?? 'active',
    ]);

    return redirect()
        ->route('admin.services.index')
        ->with('success', 'Service created successfully.');
}


    public function show(Service $service)
    {
        return view('admin.services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'status' => 'in:active,inactive',
        ]);

        $service->update($request->all());

        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
    }
}
