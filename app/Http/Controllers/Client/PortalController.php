<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function workout(): View
    {
        return view('client.workout');
    }

    public function nutrition(Request $request): View
    {
        return view('client.nutrition', [
            'plan' => $request->user()->activeNutritionPlan()->with('meals.items.food')->first(),
        ]);
    }

    public function messages(Request $request): View
    {
        return view('client.messages', ['client' => $request->user()]);
    }
}
