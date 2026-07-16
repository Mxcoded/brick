<?php

namespace App\Listeners;

use App\Models\UserLoginLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    protected Request $request;

    /**
     * Create the event listener.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $userAgent = $this->request->userAgent();
        $parsedAgent = $this->parseUserAgent($userAgent);

        UserLoginLog::create([
            'user_id' => $event->user->id,
            'session_id' => $this->request->session()->getId(),
            'ip_address' => $this->request->ip(),
            'user_agent' => $userAgent,
            'browser' => $parsedAgent['browser'],
            'platform' => $parsedAgent['platform'],
            'device_type' => $parsedAgent['device_type'],
            'login_type' => 'web',
            'status' => 'success',
            'logged_in_at' => now(),
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Parse user agent string to extract browser, platform, and device type.
     */
    private function parseUserAgent(?string $userAgent): array
    {
        $browser = 'Unknown';
        $platform = 'Unknown';
        $deviceType = 'desktop';

        if (empty($userAgent)) {
            return compact('browser', 'platform', 'device_type');
        }

        // Detect browser
        if (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Edg/i', $userAgent)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            $browser = 'Opera';
        }

        // Detect platform
        if (preg_match('/Windows NT 10/i', $userAgent)) {
            $platform = 'Windows 10/11';
        } elseif (preg_match('/Windows NT 6.3/i', $userAgent)) {
            $platform = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6.2/i', $userAgent)) {
            $platform = 'Windows 8';
        } elseif (preg_match('/Windows NT 6.1/i', $userAgent)) {
            $platform = 'Windows 7';
        } elseif (preg_match('/Windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/iPhone/i', $userAgent)) {
            $platform = 'iOS (iPhone)';
        } elseif (preg_match('/iPad/i', $userAgent)) {
            $platform = 'iOS (iPad)';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $platform = 'Android';
        }

        // Detect device type
        if (preg_match('/Mobile|Android.*Mobile|iPhone|iPod/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/Tablet|iPad|Android(?!.*Mobile)/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        return [
            'browser' => $browser,
            'platform' => $platform,
            'device_type' => $deviceType,
        ];
    }
}
