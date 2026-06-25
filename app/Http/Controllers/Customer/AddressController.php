<?php

namespace App\Http\Controllers\Customer;

use App\Enums\AddressType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerAddressRequest;
use App\Http\Requests\Customer\UpdateCustomerAddressRequest;
use App\Models\CustomerAddress;
use App\Services\AddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function __construct(private readonly AddressService $addressService)
    {
        $this->authorizeResource(CustomerAddress::class, 'address');
    }

    public function index(): View
    {
        $addresses = $this->addressService->getForUser(auth()->user());

        return view('customer.addresses.index', [
            'addresses' => $addresses,
            'shippingAddresses' => $addresses->filter(fn (CustomerAddress $address) => $address->type === AddressType::SHIPPING),
            'billingAddresses' => $addresses->filter(fn (CustomerAddress $address) => $address->type === AddressType::BILLING),
        ]);
    }

    public function create(): View
    {
        return view('customer.addresses.create');
    }

    public function store(StoreCustomerAddressRequest $request): RedirectResponse
    {
        $this->addressService->create($request->user(), $request->validated());

        return redirect()->route('customer.addresses.index')->with('status', 'Address saved successfully.');
    }

    public function edit(CustomerAddress $address): View
    {
        return view('customer.addresses.edit', ['address' => $address]);
    }

    public function update(UpdateCustomerAddressRequest $request, CustomerAddress $address): RedirectResponse
    {
        $this->addressService->update($request->user(), $address, $request->validated());

        return redirect()->route('customer.addresses.index')->with('status', 'Address updated successfully.');
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        $this->addressService->delete(auth()->user(), $address);

        return redirect()->route('customer.addresses.index')->with('status', 'Address deleted successfully.');
    }
}
