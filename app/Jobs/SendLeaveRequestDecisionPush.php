<?php

namespace App\Jobs;

use App\Models\LeaveRequest;
use App\Models\PushNotificationTicket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendLeaveRequestDecisionPush implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public int $timeout = 45;

    public function __construct(public int $leaveRequestId) {}

    public function backoff(): array { return [10, 30, 120, 300]; }

    public function handle(): void
    {
        $leaveRequest = LeaveRequest::with('employee.user.pushTokens')->find($this->leaveRequestId);
        if (! $leaveRequest || ! in_array($leaveRequest->status, ['approved', 'rejected'], true)) return;

        $tokens = $leaveRequest->employee?->user?->pushTokens->whereNull('disabled_at')->values() ?? collect();
        if ($tokens->isEmpty()) return;

        $requestLabel = $leaveRequest->request_type === 'vacation' ? 'solicitud de vacaciones' : 'permiso administrativo';
        $approved = $leaveRequest->status === 'approved';
        $resolution = $leaveRequest->request_type === 'vacation'
            ? ($approved ? 'aprobada' : 'rechazada')
            : ($approved ? 'aprobado' : 'rechazado');

        foreach ($tokens->chunk(100) as $chunk) {
            $messages = $chunk->map(fn ($pushToken) => [
                'to' => $pushToken->token,
                'title' => $approved ? 'Solicitud aprobada' : 'Solicitud rechazada',
                'body' => 'Tu '.$requestLabel.' fue '.$resolution.'.',
                'sound' => 'default',
                'channelId' => 'leave-requests',
                'data' => [
                    'type' => 'leave_request_decision',
                    'request_id' => $leaveRequest->id,
                    'request_type' => $leaveRequest->request_type,
                    'status' => $leaveRequest->status,
                    'url' => '/leave-requests',
                ],
            ])->values()->all();

            $request = Http::acceptJson()->asJson()->timeout(20);
            if ($accessToken = config('services.expo.access_token')) $request = $request->withToken($accessToken);
            $response = $request->post(config('services.expo.send_url'), $messages);
            if ($response->status() === 429 || $response->serverError()) throw new \RuntimeException('Expo Push Service no está disponible temporalmente.');
            if (! $response->successful()) {
                Log::warning('Expo rechazó un lote de notificaciones.', ['leave_request_id' => $leaveRequest->id, 'status' => $response->status()]);
                return;
            }

            $results = $response->json('data', []);
            $ticketIds = [];
            foreach ($chunk->values() as $index => $pushToken) {
                $result = $results[$index] ?? [];
                if (($result['status'] ?? null) === 'ok' && ! empty($result['id'])) {
                    $ticket = PushNotificationTicket::updateOrCreate(['ticket_id' => $result['id']], [
                        'push_token_id' => $pushToken->id,
                        'leave_request_id' => $leaveRequest->id,
                        'status' => 'pending',
                    ]);
                    $ticketIds[] = $ticket->id;
                } elseif (($result['details']['error'] ?? null) === 'DeviceNotRegistered') {
                    $pushToken->update(['disabled_at' => now()]);
                }
            }
            if ($ticketIds) CheckExpoPushReceipts::dispatch($ticketIds)->delay(now()->addMinutes(15));
        }
    }
}
