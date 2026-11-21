<?php

namespace App\Support;

use App\Models\NotificationRead;
use App\Models\User;
use Carbon\Carbon;

class NotificationReadState
{
    public static function readUntil(User $user): Carbon
    {
        $row = NotificationRead::where('user_id', $user->id)->first();

        return $row ? $row->read_until : $user->created_at;
    }

    public static function markReadUntil(User $user, Carbon $until): Carbon
    {
        $current = self::readUntil($user);
        $next = $until->greaterThan($current) ? $until : $current;

        NotificationRead::updateOrCreate(
            ['user_id' => $user->id],
            ['read_until' => $next]
        );

        return $next;
    }
}
