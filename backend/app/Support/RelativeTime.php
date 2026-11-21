<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class RelativeTime
{
    public static function format(CarbonInterface $when): string
    {
        $seconds = Carbon::now()->getTimestamp() - $when->getTimestamp();

        if ($seconds < 60) {
            return 'Just now';
        }

        $minutes = intdiv($seconds, 60);
        if ($minutes < 60) {
            return $minutes . ' ' . ($minutes === 1 ? 'min' : 'mins') . ' ago';
        }

        $hours = intdiv($minutes, 60);
        if ($hours < 24) {
            return $hours . ' ' . ($hours === 1 ? 'hour' : 'hours') . ' ago';
        }

        $days = intdiv($hours, 24);
        if ($days < 7) {
            return $days === 1 ? 'Yesterday' : $days . ' days ago';
        }

        return $when->year === Carbon::now()->year
            ? $when->format('M j')
            : $when->format('M j, Y');
    }

    public static function duration(int $seconds, ?CarbonInterface $since = null): string
    {
        $seconds = max(0, $seconds);

        if ($seconds < 60) {
            return $seconds . 's';
        }

        $minutes = intdiv($seconds, 60);
        if ($minutes < 60) {
            $remainingSeconds = $seconds % 60;
            return $remainingSeconds > 0 ? "{$minutes}m {$remainingSeconds}s" : "{$minutes}m";
        }

        $hours = intdiv($minutes, 60);
        if ($hours < 24) {
            $remainingMinutes = $minutes % 60;
            return $remainingMinutes > 0 ? "{$hours}h {$remainingMinutes}m" : "{$hours}h";
        }

        $days = intdiv($hours, 24);
        if ($days < 7) {
            $remainingHours = $hours % 24;
            return $remainingHours > 0 ? "{$days}d {$remainingHours}h" : "{$days}d";
        }

        if ($days < 8) {
            return '1 week';
        }

        if ($since) {
            return $since->year === Carbon::now()->year
                ? $since->format('M j')
                : $since->format('M j, Y');
        }

        return intdiv($days, 7) . ' weeks';
    }
}
