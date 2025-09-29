<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

class UserTwoFactorController extends Controller
{
    public function generateTotp(Request $request, TwoFactorAuthenticationProvider $provider)
    {
        $user = $request->user();

        if ($user->two_factor_secret) {
            return response()->json(['message' => '2FA already enabled'], 400);
        }

        $secret = $provider->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
        ])->save();

        $recoveryCodes = $user->forceGenerateRecoveryCodes();

        $qrCodeUrl = $provider->qrCodeUrl(config('app.name'), $user->email, $secret);

        // ✅ v6 style
        $result = Builder::build([
            'writer' => new SvgWriter(),
            'data'   => $qrCodeUrl,
            'size'   => 250,
        ]);

        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($result->getString());

        return response()->json([
            'user'          => new UserResource($user),
            'message'       => '2FA enabled successfully',
            'qr_code_url'   => $qrCodeUrl,
            'qr'            => $qrBase64,
            'recovery_codes'=> $recoveryCodes,
        ]);
    }

    public function regenerateTotpQr(Request $request, TwoFactorAuthenticationProvider $provider)
    {
        $user = $request->user();

        if (!$user->two_factor_secret) {
            return response()->json(['message' => '2FA not enabled'], 400);
        }

        $request->validate([
            'code' => 'required|string',
        ]);

        $recoveryCode = $request->input('code');
        $valid = false;

        foreach ($user->recoveryCodes() as $code) {
            if (hash_equals($code, $recoveryCode)) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            return response()->json(['message' => 'Invalid recovery code'], 403);
        }

        $secret = decrypt($user->two_factor_secret);
        $qrCodeUrl = $provider->qrCodeUrl(config('app.name'), $user->email, $secret);

        // ✅ v6 style
        $result = Builder::build([
            'writer' => new SvgWriter(),
            'data'   => $qrCodeUrl,
            'size'   => 250,
        ]);

        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($result->getString());

        return response()->json([
            'user'           => new UserResource($user),
            'message'        => 'QR regenerated successfully',
            'qr_code_url'    => $qrCodeUrl,
            'qr'             => $qrBase64,
            'recovery_codes' => $user->recoveryCodes(),
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
                'two_factor_method' => "none",
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
