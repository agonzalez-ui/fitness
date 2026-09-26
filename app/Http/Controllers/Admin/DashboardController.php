<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Message;
use App\Models\User;
use App\Models\WorkoutLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $clients = User::clients()->active()
            ->with(['latestMembership.plan', 'activeWorkoutPlan', 'latestProgress'])
            ->orderBy('name')->get();

        $byStatus = $clients->groupBy(fn (User $client) => $client->membership_status->value);
        $expiring = $byStatus->get(MembershipStatus::ExpiringSoon->value, collect())->sortBy('latestMembership.ends_on');
        $expired = $byStatus->get(MembershipStatus::Expired->value, collect())->sortByDesc('latestMembership.ends_on');

        return view('admin.dashboard', [
            'stats' => [
                'clients' => $clients->count(),
                'active' => $byStatus->get(MembershipStatus::Active->value, collect())->count() + $expiring->count(),
                'income' => Membership::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('price'),
                'unread' => $request->user()->unreadMessagesCount(),
            ],
            'expiring' => $expiring,
            'expired' => $expired->take(6),
            'expiredCount' => $expired->count(),
            'withoutPlan' => $clients->filter(fn (User $client) => ! $client->activeWorkoutPlan)->take(6),
            'inactive' => $clients->filter(fn (User $client) => ! $client->latestProgress || $client->latestProgress->recorded_on->lt(today()->subDays(14)))->take(6),
            'recentWorkouts' => WorkoutLog::with(['user', 'day'])->whereNotNull('finished_at')->latest('finished_at')->take(6)->get(),
            'recentMessages' => Message::with('sender')->whereHas('sender', fn ($q) => $q->where('role', Role::Client))->latest()->take(5)->get(),
            'newClients' => User::clients()->where('created_at', '>=', today()->subDays(30))->count(),
        ]);
    }
}
