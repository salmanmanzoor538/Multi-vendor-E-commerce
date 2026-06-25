<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreAttributeRequest;
use App\Http\Requests\Vendor\UpdateAttributeRequest;
use App\Models\Attribute;
use App\Services\AttributeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttributeController extends Controller
{
    public function __construct(private readonly AttributeService $attributeService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Attribute::class);

        return view('vendor.attributes.index', [
            'attributes' => $this->attributeService->paginateForVendor(auth()->user()),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Attribute::class);

        return view('vendor.attributes.create');
    }

    public function store(StoreAttributeRequest $request): RedirectResponse
    {
        $this->authorize('create', Attribute::class);
        $this->attributeService->create($request->user(), $request->validated());

        return redirect()->route('vendor.attributes.index')->with('status', 'Attribute created successfully.');
    }

    public function edit(Attribute $attribute): View
    {
        $this->authorize('update', $attribute);

        return view('vendor.attributes.edit', [
            'attribute' => $attribute->load('values'),
        ]);
    }

    public function update(UpdateAttributeRequest $request, Attribute $attribute): RedirectResponse
    {
        $this->authorize('update', $attribute);
        $this->attributeService->update($attribute, $request->validated());

        return redirect()->route('vendor.attributes.index')->with('status', 'Attribute updated successfully.');
    }

    public function destroy(Attribute $attribute): RedirectResponse
    {
        $this->authorize('delete', $attribute);
        $this->attributeService->delete($attribute);

        return redirect()->route('vendor.attributes.index')->with('status', 'Attribute deleted successfully.');
    }
}
