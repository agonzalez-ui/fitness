<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipStatus;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['mes' => ['nullable', 'date_format:Y-m'], 'estado' => ['nullable', 'in:expiring_soon,expired']]);
        $month = CarbonImmutable::createFromFormat('Y-m-d', ($request->query('mes') ?? now()->format('Y-m')).'-01')->startOfMonth();
        $range = [$month, $month->endOfMonth()];

        $payments = Membership::with(['user', 'plan'])->whereBetween('created_at', $range)->latest()->get();
        $previous = Membership::whereBetween('created_at', [$month->subMonthNoOverflow(), $month->subMonthNoOverflow()->endOfMonth()])->sum('price');

        $pending = User::clients()->active()->with('latestMembership.plan')
            ->withMembershipStatus(MembershipStatus::from($request->query('estado', 'expiring_soon')))
            ->get()->sortBy('latestMembership.ends_on');

        return view('admin.memberships.index', [
            'month' => $month,
            'payments' => $payments,
            'total' => $payments->sum('price'),
            'previous' => (float) $previous,
            'byMethod' => $payments->groupBy(fn ($p) => $p->payment_method->label())->map->sum('price')->sortDesc(),
            'pending' => $pending,
            'pendingStatus' => $request->query('estado', 'expiring_soon'),
            'counts' => [
                'expiring_soon' => User::clients()->active()->withMembershipStatus(MembershipStatus::ExpiringSoon)->count(),
                'expired' => User::clients()->active()->withMembershipStatus(MembershipStatus::Expired)->count(),
            ],
        ]);
    }
}
