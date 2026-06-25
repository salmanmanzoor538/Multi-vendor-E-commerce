<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminAnalyticsFilterRequest;
use App\Services\AdminAnalyticsService;
use Illuminate\Http\JsonResponse;

class AdminAnalyticsController extends Controller
{
    public function __construct(
        private readonly AdminAnalyticsService $adminAnalyticsService,
    ) {
    }

    public function index(AdminAnalyticsFilterRequest $request): JsonResponse
    {
        return response()->json(
            $this->adminAnalyticsService->buildDashboardData($request->validated())
        );
    }
}
