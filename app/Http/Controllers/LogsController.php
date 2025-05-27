<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LogsController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of logs
     */
    public function index()
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        // Use pagination
        $logs = Log::with('user')->latest()->paginate(10); // or any number

        return view('logs.index', compact('logs'));
    }
}
