<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;

class GoogleDriveToken extends Command
{
    protected $signature = 'google:token';
    protected $description = 'Get Google Drive API Refresh Token';

    public function handle()
    {
        $clientId = env('GOOGLE_DRIVE_CLIENT_ID');
        $clientSecret = env('GOOGLE_DRIVE_CLIENT_SECRET');

        if (!$clientId || !$clientSecret) {
            $this->error('Please set GOOGLE_DRIVE_CLIENT_ID and GOOGLE_DRIVE_CLIENT_SECRET in your .env file first.');
            $this->info('You must create OAuth credentials in the Google Cloud Console (Web application, redirect URI: http://localhost)');
            return 1;
        }

        $client = new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri('http://localhost');
        $client->addScope('https://www.googleapis.com/auth/drive');
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $authUrl = $client->createAuthUrl();

        $this->info("1. Open this URL in your browser:");
        $this->line($authUrl);
        $this->info("");
        $this->info("2. Authenticate and authorize the application.");
        $this->info("3. You will be redirected to localhost (which might show a 'site cannot be reached' error, that's fine!).");
        $this->info("4. Look at the URL address bar in your browser, it will look like 'http://localhost/?code=4/0AeaY...&scope=...'");
        $this->info("5. Copy ONLY the value of the 'code' parameter.");
        
        $code = $this->ask('Pegue el código aquí (Paste the code here)');

        if (empty($code)) {
            $this->error('Code is required!');
            return 1;
        }

        // Clean up code if user accidentally copied more than just the code
        if (strpos($code, 'code=') !== false) {
            parse_str(parse_url($code, PHP_URL_QUERY) ?? $code, $params);
            $code = $params['code'] ?? $code;
        }

        try {
            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                $this->error('Error fetching token: ' . ($token['error_description'] ?? $token['error']));
                return 1;
            }

            if (isset($token['refresh_token'])) {
                $this->info("=========================================");
                $this->info("SUCCESS! Here is your Refresh Token:");
                $this->line($token['refresh_token']);
                $this->info("=========================================");
                $this->info("Add this to your .env file as:");
                $this->line("GOOGLE_DRIVE_REFRESH_TOKEN=" . $token['refresh_token']);
                $this->info("Also add GOOGLE_DRIVE_FOLDER= (can be empty for root folder, or the ID of a specific Google Drive folder).");
            } else {
                $this->warn("No refresh token received. This usually happens if you've already authorized the app.");
                $this->line("Go to your Google Account > Security > Third-party apps with account access, remove the app, and try again.");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        return 0;
    }
}
