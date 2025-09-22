<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use SimpleSoftwareIO\QrCode\Generator;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticationProvider;

class UserTwoFactorController extends Controller
{
    public function generateTotp(Request $request, TwoFactorAuthenticationProvider $provider)
    {
        $user = $request->user();

        // if ($user->two_factor_secret) {
        //     return response()->json(['message' => '2FA already enabled'], 400);
        // }

        $secret = $provider->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
        ])->save();

        // Generate recovery codes using the trait
        $recoveryCodes = $user->forceGenerateRecoveryCodes();


          $qr_code_url = $provider->qrCodeUrl(
                config('app.name'),
                $user->email,
                $secret
          );

         $qrBinary = QrCode::size(250)
                ->format('svg')
                ->generate($qr_code_url);

            $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrBinary);

        return response()->json([
            'message' => '2FA enabled successfully',
            'qr_code_url' => $provider->qrCodeUrl(
                config('app.name'),
                $user->email,
                $secret
            ),
            'qr' => $qrBase64,
            'recovery_codes' => $recoveryCodes,
        ]);
    }


    public function updateTwoFactor(Request $request)
    {

        $request->validate([
            'method' => 'nullable|string|in:none,email,totp',
        ]);

        $user = $request->user();

        if ($request->method === 'none') {
            $user->update([
                'two_factor_method' => null,
                'two_factor_secret' => null,
            ]);

            return response()->json(['message' => 'Two-factor authentication disabled']);
        }

        $user->update([
            'two_factor_method' => $request->method,
            
        ]);

        return response()->json([
            'message' => "Two-factor authentication updated to: {$request->method}",
        ]);
    }
}
