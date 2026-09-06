<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerWinBackResource;
use App\Services\WinBackService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(WinBackService $service): Response
    {
        // resolve() flattens the resource collection to a plain array so the
        // Inertia props carry the customer list directly, with no data envelope.
        return Inertia::render('Dashboard', [
            'summary' => $service->summary(),
            'winBack' => CustomerWinBackResource::collection($service->winBackCandidates())->resolve(),
            'allCustomers' => CustomerWinBackResource::collection($service->allCustomersWithProgress())->resolve(),
            'config' => [
                'proximity_threshold' => config('loyalty.proximity_threshold'),
                'inactivity_days' => config('loyalty.inactivity_days'),
            ],
        ]);
    }
}
