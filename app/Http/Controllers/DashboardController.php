<?php

namespace App\Http\Controllers;

use App\Services\WinBackService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(WinBackService $service): Response
    {
        return Inertia::render('Dashboard', [
            'summary' => $service->summary(),
            'winBack' => $service->winBackCandidates(),
            'allCustomers' => $service->allCustomersWithProgress(),
            'config' => [
                'proximity_threshold' => config('loyalty.proximity_threshold'),
                'inactivity_days' => config('loyalty.inactivity_days'),
            ],
        ]);
    }
}
