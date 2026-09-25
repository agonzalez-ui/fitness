<?php

use App\Enums\MembershipStatus;
use App\Enums\PaymentMethod;
use App\Models\Plan;
use App\Services\MembershipService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-09-25');
    $this->plan = Plan::create(['name' => 'Mensual', 'duration_months' => 1, 'price' => 25]);
    $this->service = app(MembershipService::class);
});

it('starts today when the client has no membership', function () {
    $membership = $this->service->renew($client = client(), $this->plan, PaymentMethod::Sinpe, admin());

    expect($membership->starts_on->toDateString())->toBe('2026-09-25')
        ->and($membership->ends_on->toDateString())->toBe('2026-10-24')
        ->and($membership->price)->toBe('25.00')
        ->and($client->fresh()->membership_status)->toBe(MembershipStatus::Active);
});

it('chains early renewals so no days are lost', function () {
    $client = client();
    $this->service->renew($client, $this->plan, PaymentMethod::Cash, $coach = admin());
    $second = $this->service->renew($client, $this->plan, PaymentMethod::Cash, $coach);

    expect($second->starts_on->toDateString())->toBe('2026-10-25');
});

it('starts today when the previous membership already expired', function () {
    $client = client();
    $client->memberships()->create(['plan_id' => $this->plan->id, 'starts_on' => '2026-01-01', 'ends_on' => '2026-01-31', 'price' => 25, 'payment_method' => 'cash']);

    expect($client->fresh()->membership_status)->toBe(MembershipStatus::Expired);

    $renewed = $this->service->renew($client, $this->plan, PaymentMethod::Cash, admin());
    expect($renewed->starts_on->toDateString())->toBe('2026-09-25');
});

it('computes status from the end date, never from a stored flag', function (string $endsOn, MembershipStatus $expected) {
    expect(MembershipStatus::fromEndDate(Carbon::parse($endsOn)))->toBe($expected);
})->with([
    ['2026-10-30', MembershipStatus::Active],
    ['2026-10-02', MembershipStatus::ExpiringSoon],
    ['2026-09-25', MembershipStatus::ExpiringSoon],
    ['2026-09-24', MembershipStatus::Expired],
]);

it('handles month-end dates without overflowing', function () {
    Carbon::setTestNow('2026-01-31');
    $membership = $this->service->renew(client(), $this->plan, PaymentMethod::Cash, admin());

    expect($membership->ends_on->toDateString())->toBe('2026-02-27');
});
