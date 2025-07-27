<?php

namespace App\Http\Helpers;

/*
|----------------------------------------------------------------------
| Agent Helper
|----------------------------------------------------------------------
|
| The AgentHelper class provides methods to parse user agent strings
| and return details about the browser and platform.
|
| It can be used to identify the user's browser and platform
| from the user agent string, which is useful for analytics,
| debugging, and providing tailored user experiences.
|
*/
class AgentHelper
{
    /**
     * Parse the user agent string and return details.
     *
     * @param string|null $userAgent
     * @return array
     */
    public static function parseUserAgent(?string $userAgent): array
    {
        if (!$userAgent) {
            return [
                'browser' => 'Unknown',
                'platform' => 'Unknown',
                'original' => null,
            ];
        }

        $browser = 'Unknown';
        $platform = 'Unknown';

        if (stripos($userAgent, 'Windows') !== false) {
            $platform = 'Windows';
        } elseif (stripos($userAgent, 'Macintosh') !== false) {
            $platform = 'Mac';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $platform = 'Linux';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $platform = 'Android';
        } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            $platform = 'iOS';
        }

        if (stripos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (stripos($userAgent, 'MSIE') !== false || stripos($userAgent, 'Trident') !== false) {
            $browser = 'Internet Explorer';
        } elseif (stripos($userAgent, 'Edge') !== false) {
            $browser = 'Edge';
        }

        return [
            'browser' => $browser,
            'platform' => $platform,
            'original' => $userAgent,
        ];
    }
}

                                  
