<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BillingController extends Controller
{
    /**
     * Start a Polar checkout for the paid Team plan on the given billing cycle.
     */
    public function subscribe(Request $request, string $cycle): Response
    {
        abort_unless(in_array($cycle, ['monthly', 'yearly'], true), 404);

        $productId = config("polar.plans.team.{$cycle}");
        abort_if(blank($productId), 404, 'Plan is not configured.');

        // Already subscribed → send them to manage their plan instead.
        if ($request->user()->subscribed()) {
            return $request->user()->redirectToCustomerPortal();
        }

        return $request->user()
            ->checkout([$productId])
            ->withSuccessUrl(route('schemas.index'))
            ->toResponse($request);
    }

    /**
     * Redirect to the Polar customer portal to manage billing.
     */
    public function portal(Request $request): Response
    {
        return $request->user()->redirectToCustomerPortal();
    }
}
