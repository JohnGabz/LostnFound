<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Claim;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Item statistics
        $lostCount = Item::where('type', 'lost')->where('status', 'pending')->count();
        $foundCount = Item::where('type', 'found')->where('status', 'pending')->count();
        $claimedCount = Item::where('status', 'claimed')->count();

        $totalActivePosts = $lostCount + $foundCount;
        $totalPosts = $totalActivePosts + $claimedCount;

        $lostPercentage = $totalPosts > 0 ? round(($lostCount / $totalPosts) * 100) : 0;
        $foundPercentage = $totalPosts > 0 ? round(($foundCount / $totalPosts) * 100) : 0;
        $claimedPercentage = $totalPosts > 0 ? round(($claimedCount / $totalPosts) * 100) : 0;

        // Insights and Trends
        $insights = $this->getInsights();
        $weeklyTrend = $this->getWeeklyTrend();
        $topCategories = $this->getTopCategories();
        $recentActivity = $this->getRecentActivity();

        // User statistics
        $totalUsers = User::count();
        $recentUsers = User::latest()->take(5)->get();

        return view('dashboard', compact(
            'lostCount',
            'foundCount',
            'claimedCount',
            'totalPosts',
            'totalActivePosts',
            'lostPercentage',
            'foundPercentage',
            'claimedPercentage',
            'insights',
            'weeklyTrend',
            'topCategories',
            'recentActivity',
            'totalUsers',
            'recentUsers'
        ));
    }
    private function getInsights()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeek = Carbon::now()->startOfWeek();
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();

        return [
            'today_posts' => Item::whereDate('created_at', $today)->count(),
            'yesterday_posts' => Item::whereDate('created_at', $yesterday)->count(),
            'week_posts' => Item::where('created_at', '>=', $thisWeek)->count(),
            'last_week_posts' => Item::whereBetween('created_at', [$lastWeek, $thisWeek])->count(),
            'pending_claims' => Claim::where('status', 'pending')->count(),
            'success_rate' => $this->calculateSuccessRate(),
            'active_users' => $this->getActiveUsersCount(),
        ];
    }

    private function getWeeklyTrend()
    {
        $dates = [];
        $lostData = [];
        $foundData = [];
        $claimedData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format('M j');

            $lostData[] = Item::where('type', 'lost')
                ->whereDate('created_at', $date)
                ->count();

            $foundData[] = Item::where('type', 'found')
                ->whereDate('created_at', $date)
                ->count();

            // Count items that were marked as claimed on this date
            $claimedData[] = Item::where('status', 'claimed')
                ->whereDate('updated_at', $date)
                ->count();
        }

        return [
            'dates' => $dates,
            'lost' => $lostData,
            'found' => $foundData,
            'claimed' => $claimedData,
        ];
    }

    private function getTopCategories()
    {
        return Item::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
    }

    private function getRecentActivity()
    {
        // Get recent items
        $recentItems = Item::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'item_' . $item->type,
                    'title' => $item->title,
                    'user' => $item->user->name ?? 'Anonymous',
                    'time' => $item->created_at->diffForHumans(),
                    'id' => $item->item_id,
                    'action' => 'posted',
                    'created_at' => $item->created_at,
                ];
            });

        // Get recent claims
        $recentClaims = Item::with('user')
            ->where('status', 'claimed')
            ->orderBy('updated_at', 'desc')
            ->limit(2)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'claimed',
                    'title' => $item->title,
                    'user' => $item->user->name ?? 'Anonymous',
                    'time' => $item->updated_at->diffForHumans(),
                    'id' => $item->item_id,
                    'action' => 'claimed',
                    'created_at' => $item->updated_at,
                ];
            });

        // Merge and sort by time
        $allActivity = $recentItems->concat($recentClaims)
            ->sortByDesc('created_at')
            ->take(5);

        return $allActivity;
    }

    private function calculateSuccessRate()
    {
        $totalItems = Item::count();
        $claimedItems = Item::where('status', 'claimed')->count();

        return $totalItems > 0 ? round(($claimedItems / $totalItems) * 100, 1) : 0;
    }

    private function getActiveUsersCount()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);

        return User::where('created_at', '>=', $sevenDaysAgo)->count();
    }
}