<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Callback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .container {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #F77F00;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        @if($success)
            <h2>Login Successful!</h2>
            <p>Redirecting you to the admin panel...</p>
        @else
            <h2>Login Failed</h2>
            <p>{{ $error ?? 'An error occurred during authentication.' }}</p>
            <p>Redirecting you back to login...</p>
        @endif
    </div>

    <script>
        // Handle OAuth callback
        @if($success)
            // Store authentication data
            const token = @json($token);
            const user = @json($user);
            const provider = @json($provider);
            
            // If this is opened in a popup, post message to parent
            if (window.opener) {
                window.opener.postMessage({
                    type: 'OAUTH_SUCCESS',
                    token: token,
                    user: user,
                    provider: provider
                }, '*');
                window.close();
            } else {
                // Direct redirect with URL parameters
                const frontendUrl = @json($frontendUrl);
                const redirectUrl = `${frontendUrl}/oauth/callback?token=${encodeURIComponent(token)}&login=success&provider=${provider}`;
                window.location.href = redirectUrl;
            }
        @else
            // Handle error
            const error = @json($error ?? 'Authentication failed');
            
            if (window.opener) {
                window.opener.postMessage({
                    type: 'OAUTH_ERROR',
                    error: error
                }, '*');
                window.close();
            } else {
                // Redirect to login with error
                const frontendUrl = @json($frontendUrl);
                const redirectUrl = `${frontendUrl}/login?error=${encodeURIComponent(error)}`;
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 3000);
            }
        @endif
    </script>
</body>
</html>
