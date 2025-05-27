<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LogsController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of logs.
     */
    public function index()
    {
        $this->authorize('viewAny', Log::class);

        $logs = Log::with('user')->latest()->paginate(20);

        return view('logs.index', compact('logs'));
    }

    private function logAction(string $action, ?string $details = null): void
    {
        Log::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
        ]);
    }
}
