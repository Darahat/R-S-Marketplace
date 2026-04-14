<?php

namespace App\Services;

class DeviceDetectionService
{
    public function parseDeviceName(string $userAgent): string
    {
        $agent = trim($userAgent);

        if ($agent === '') {
            return 'Unknown Device';
        }

        $browser = 'Unknown Browser';
        if (str_contains($agent, 'Edg/')) {
            $browser = 'Microsoft Edge';
        } elseif (str_contains($agent, 'OPR/') || str_contains($agent, 'Opera')) {
            $browser = 'Opera';
        } elseif (str_contains($agent, 'Chrome/')) {
            $browser = 'Google Chrome';
        } elseif (str_contains($agent, 'Firefox/')) {
            $browser = 'Mozilla Firefox';
        } elseif (str_contains($agent, 'Safari/') && !str_contains($agent, 'Chrome/')) {
            $browser = 'Safari';
        } elseif (str_contains($agent, 'MSIE') || str_contains($agent, 'Trident/')) {
            $browser = 'Internet Explorer';
        }

        $platform = 'Unknown OS';
        if (str_contains($agent, 'Windows NT 10.0')) {
            $platform = 'Windows 10/11';
        } elseif (str_contains($agent, 'Windows NT 6.3')) {
            $platform = 'Windows 8.1';
        } elseif (str_contains($agent, 'Windows NT 6.2')) {
            $platform = 'Windows 8';
        } elseif (str_contains($agent, 'Windows NT 6.1')) {
            $platform = 'Windows 7';
        } elseif (str_contains($agent, 'Android')) {
            $platform = 'Android';
        } elseif (str_contains($agent, 'iPhone') || str_contains($agent, 'iPad')) {
            $platform = 'iOS';
        } elseif (str_contains($agent, 'Mac OS X')) {
            $platform = 'macOS';
        } elseif (str_contains($agent, 'Linux')) {
            $platform = 'Linux';
        }

        return $browser . ' on ' . $platform;
    }
}
