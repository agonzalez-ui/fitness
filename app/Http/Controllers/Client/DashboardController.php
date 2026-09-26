<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load([
            'latestMembership.plan',
            'activeWorkoutPlan.days.items.exercise',
            'activeNutritionPlan.meals.items.food',
            'latestProgress',
        ]);

        $plan = $user->activeWorkoutPlan;
        $finishedCount = $user->workoutLogs()->whereNotNull('finished_at')->count();
        $today = $plan?->dayFor(today(), $finishedCount);
        $weekLogs = $user->workoutLogs()->whereNotNull('finished_at')
            ->whereBetween('performed_on', [today()->startOfWeek(), today()->endOfWeek()])->count();

        return view('client.dashboard', [
            'user' => $user,
            'membership' => $user->latestMembership,
            'plan' => $plan,
            'today' => $today,
            'todayDone' => $today && $user->workoutLogs()->where('workout_day_id', $today->id)->whereDate('performed_on', today())->whereNotNull('finished_at')->exists(),
            'nextDay' => $plan && ! $today ? $plan->nextDayAfter(today()) : null,
            'weekLogs' => $weekLogs,
            'weekTarget' => $plan?->days->count() ?? 0,
            'firstProgress' => $user->progressEntries()->whereNotNull('weight_kg')->first(),
            'latestProgress' => $user->latestProgress,
            'nutrition' => $user->activeNutritionPlan,
            'unread' => $user->unreadMessagesCount(),
        ]);
    }
}
