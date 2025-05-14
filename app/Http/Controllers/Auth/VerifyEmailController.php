<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $emailVerificationRequest): RedirectResponse
    {
        if ($emailVerificationRequest->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false).'?verified=1');
        }

        if ($emailVerificationRequest->user()->markEmailAsVerified()) {
            /** @var MustVerifyEmail $user */
            $user = $emailVerificationRequest->user();

            event(new Verified($user));
        }

        return redirect()->intended(route('home', absolute: false).'?verified=1');
    }
}
