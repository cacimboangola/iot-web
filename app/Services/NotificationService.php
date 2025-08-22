<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Serviço para gerenciar notificações por email do sistema de irrigação
 */
class NotificationService
{
    /**
     * Envia notificação por email sobre mudança de estado de atuador
     */
    public function sendActuatorNotification(string $actuator, string $action): void
    {
        if (!$this->isNotificationEnabled()) {
            return;
        }

        $emailTo = config('irrigation.notifications.email');
        
        if (!$emailTo) {
            Log::warning('Email de notificação não configurado');
            return;
        }

        // Verifica se a notificação específica está habilitada
        if (!$this->isEventNotificationEnabled($actuator, $action)) {
            return;
        }

        try {
            $subject = $this->getEmailSubject($actuator, $action);
            $body = $this->getEmailBody($actuator, $action);

            Mail::raw($body, function ($message) use ($emailTo, $subject) {
                $message->to($emailTo)
                        ->subject($subject);
            });

            Log::info("Notificação enviada: {$actuator} {$action}", [
                'email' => $emailTo,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação por email: {$e->getMessage()}", [
                'actuator' => $actuator,
                'action' => $action,
                'email' => $emailTo
            ]);
        }
    }

    /**
     * Verifica se as notificações estão habilitadas globalmente
     */
    private function isNotificationEnabled(): bool
    {
        return config('irrigation.notifications.enabled', true);
    }

    /**
     * Verifica se a notificação específica do evento está habilitada
     */
    private function isEventNotificationEnabled(string $actuator, string $action): bool
    {
        $eventKey = $this->getEventKey($actuator, $action);
        return config("irrigation.notifications.events.{$eventKey}", true);
    }

    /**
     * Gera a chave do evento para configuração
     */
    private function getEventKey(string $actuator, string $action): string
    {
        $actionMap = [
            'ligada' => 'on',
            'ligado' => 'on',
            'desligada' => 'off',
            'desligado' => 'off',
            'aberta' => 'open',
            'fechada' => 'close'
        ];

        $actuatorMap = [
            'bomba' => 'pump',
            'ventilador' => 'fan',
            'válvula' => 'valve'
        ];

        $actionKey = $actionMap[$action] ?? $action;
        $actuatorKey = $actuatorMap[$actuator] ?? $actuator;

        return "{$actuatorKey}_{$actionKey}";
    }

    /**
     * Gera o assunto do email
     */
    private function getEmailSubject(string $actuator, string $action): string
    {
        $actuatorNames = [
            'bomba' => 'Bomba de Irrigação',
            'ventilador' => 'Ventilador',
            'válvula' => 'Válvula Solenoide'
        ];

        $actuatorName = $actuatorNames[$actuator] ?? ucfirst($actuator);
        
        return "{$actuatorName} {$action} - Sistema IoT";
    }

    /**
     * Gera o corpo do email
     */
    private function getEmailBody(string $actuator, string $action): string
    {
        $timestamp = now()->format('d/m/Y H:i:s');
        
        $actuatorNames = [
            'bomba' => 'A bomba de irrigação',
            'ventilador' => 'O ventilador',
            'válvula' => 'A válvula solenoide'
        ];

        $actuatorText = $actuatorNames[$actuator] ?? "O(A) {$actuator}";

        return "{$actuatorText} foi {$action} às {$timestamp}\n\n" .
               "Esta é uma notificação automática do Sistema de Irrigação Inteligente IoT.\n\n" .
               "Para mais detalhes, acesse o dashboard: " . config('app.url') . "\n\n" .
               "---\n" .
               "Sistema de Irrigação IoT\n" .
               "Desenvolvido com Laravel + Livewire + ESP32";
    }

    /**
     * Testa o envio de email
     */
    public function testEmailConfiguration(): bool
    {
        try {
            $emailTo = config('irrigation.notifications.email');
            
            if (!$emailTo) {
                return false;
            }

            Mail::raw(
                "Este é um email de teste do Sistema de Irrigação IoT.\n\n" .
                "Se você recebeu este email, a configuração está funcionando corretamente!\n\n" .
                "Timestamp: " . now()->format('d/m/Y H:i:s'),
                function ($message) use ($emailTo) {
                    $message->to($emailTo)
                            ->subject('Teste de Configuração - Sistema IoT');
                }
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Erro no teste de email: ' . $e->getMessage());
            return false;
        }
    }
}
