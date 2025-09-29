<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;

class ShopifyOAuthService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $scopes;

    public function __construct()
    {
        $this->clientId = config('services.shopify.client_id');
        $this->clientSecret = config('services.shopify.client_secret');
        $this->redirectUri = config('services.shopify.redirect');
        $this->scopes = 'read_products,read_orders,read_customers';
    }

    public function getAuthUrl($shop)
    {
        $params = [
            'client_id' => $this->clientId,
            'scope' => $this->scopes,
            'redirect_uri' => $this->redirectUri,
            'state' => csrf_token(),
        ];

        return "https://{$shop}/admin/oauth/authorize?" . http_build_query($params);
    }

    public function handleCallback(Request $request)
    {
        $shop = $request->get('shop');
        $code = $request->get('code');
        $state = $request->get('state');

        if (!$shop || !$code) {
            throw new Exception('Missing required parameters');
        }

        // Exchange code for access token
        $response = Http::post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to exchange code for access token');
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];

        // Get shop information
        $shopResponse = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->get("https://{$shop}/admin/api/2023-10/shop.json");

        if (!$shopResponse->successful()) {
            throw new Exception('Failed to get shop information');
        }

        $shopData = $shopResponse->json()['shop'];

        return (object) [
            'id' => $shopData['id'],
            'name' => $shopData['name'],
            'email' => $shopData['email'],
            'avatar' => null,
            'token' => $accessToken,
            'refreshToken' => null,
            'shop' => $shop,
        ];
    }
}
