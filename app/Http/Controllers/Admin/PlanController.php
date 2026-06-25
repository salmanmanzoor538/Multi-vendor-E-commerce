<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function __construct(private readonly PlanService $planService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Plan::class);

        return view('admin.plans.index', [
            'plans' => $this->planService->paginateForAdmin(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Plan::class);

        return view('admin.plans.create');
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $this->authorize('create', Plan::class);

        $this->planService->create($request->validated());

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'Plan created successfully.');
    }

    public function edit(Plan $plan): View
    {
        $this->authorize('update', $plan);

        return view('admin.plans.edit', [
            'plan' => $plan,
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $this->planService->update($plan, $request->validated());

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $this->planService->deactivate($plan);

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'Plan deactivated successfully.');
    }
}
