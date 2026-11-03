<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PushToken;
use Illuminate\Http\Request;

class PushTokenController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255', 'regex:/^(ExponentPushToken|ExpoPushToken)\[[^\]]+\]$/'],
            'platform' => 'nullable|in:android,ios',
            'device_uid' => 'nullable|string|max:255',
        ]);

        PushToken::updateOrCreate(['token' => $data['token']], [
            'user_id' => $request->user()->id,
            'platform' => $data['platform'] ?? null,
            'device_uid' => $data['device_uid'] ?? null,
            'last_seen_at' => now(),
            'disabled_at' => null,
        ]);

        return response()->json(['registered' => true]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'token' => 'nullable|required_without:device_uid|string|max:255',
            'device_uid' => 'nullable|required_without:token|string|max:255',
        ]);

        $request->user()->pushTokens()
            ->when($data['token'] ?? null, fn ($query, $token) => $query->where('token', $token))
            ->when(! isset($data['token']) && ($data['device_uid'] ?? null), fn ($query) => $query->where('device_uid', $data['device_uid']))
            ->update(['disabled_at' => now()]);

        return response()->noContent();
    }
}
