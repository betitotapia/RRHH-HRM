<?php

namespace App\Jobs;

use App\Models\PushNotificationTicket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class CheckExpoPushReceipts implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public int $timeout = 45;

    public function __construct(public array $ticketDatabaseIds) {}

    public function backoff(): array { return [60, 300, 900, 1800]; }

    public function handle(): void
    {
        $tickets = PushNotificationTicket::with('pushToken')->whereIn('id', $this->ticketDatabaseIds)->where('status', 'pending')->get();
        if ($tickets->isEmpty()) return;

        foreach ($tickets->chunk(100) as $chunk) {
            $request = Http::acceptJson()->asJson()->timeout(20);
            if ($accessToken = config('services.expo.access_token')) $request = $request->withToken($accessToken);
            $response = $request->post(config('services.expo.receipts_url'), ['ids' => $chunk->pluck('ticket_id')->values()->all()]);
            if ($response->status() === 429 || $response->serverError()) throw new \RuntimeException('Los receipts de Expo no están disponibles temporalmente.');
            if (! $response->successful()) return;

            $receipts = $response->json('data', []);
            foreach ($chunk as $ticket) {
                $receipt = $receipts[$ticket->ticket_id] ?? null;
                if (! $receipt) continue;
                $error = $receipt['details']['error'] ?? null;
                $ticket->update(['status' => ($receipt['status'] ?? null) === 'ok' ? 'delivered' : 'error', 'error_code' => $error, 'checked_at' => now()]);
                if ($error === 'DeviceNotRegistered') $ticket->pushToken?->update(['disabled_at' => now()]);
            }
        }
    }
}
