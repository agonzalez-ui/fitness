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

        $paymentQuery = Membership::whereBetween('created_at', $range);
        $payments = (clone $paymentQuery)->with(['user', 'plan'])->latest()->orderByDesc('id')
            ->paginate(15, ['*'], 'pagos')->withQueryString()->fragment('pagos');
        $previous = Membership::whereBetween('created_at', [$month->subMonthNoOverflow(), $month->subMonthNoOverflow()->endOfMonth()])->sum('price');

        $pending = User::clients()->active()->with('latestMembership.plan')
            ->withMembershipStatus(MembershipStatus::from($request->query('estado', 'expiring_soon')))
            ->orderByRaw('(select max(ends_on) from memberships where memberships.user_id = users.id)')->orderBy('id')
            ->paginate(10, ['*'], 'pendientes')->withQueryString()->fragment('pendientes');

        return view('admin.memberships.index', [
            'month' => $month,
            'payments' => $payments,
            'total' => (clone $paymentQuery)->sum('price'),
            'previous' => (float) $previous,
            'byMethod' => (clone $paymentQuery)->selectRaw('payment_method, sum(price) as amount')->groupBy('payment_method')
                ->get()->mapWithKeys(fn ($payment) => [$payment->payment_method->label() => (float) $payment->amount])->sortDesc(),
            'pending' => $pending,
            'pendingStatus' => $request->query('estado', 'expiring_soon'),
            'counts' => [
                'expiring_soon' => User::clients()->active()->withMembershipStatus(MembershipStatus::ExpiringSoon)->count(),
                'expired' => User::clients()->active()->withMembershipStatus(MembershipStatus::Expired)->count(),
            ],
        ]);
    }
}
