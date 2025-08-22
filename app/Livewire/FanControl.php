<?php

namespace App\Livewire;

use App\Services\BlynkService;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

/**
 * Componente para controlar o ventilador
 * Permite ligar/desligar manualmente o ventilador e envia notificações por email
 */
class FanControl extends Component
{
    public bool $fanStatus = false;
    public bool $isLoading = false;
    public ?string $lastAction = null;

    public function mount(bool $fanStatus = false)
    {
        $this->fanStatus = $fanStatus;
    }

    /**
     * Alterna o estado do ventilador (liga/desliga)
     */
    public function toggleFan(): void
    {
        $this->isLoading = true;
        
        try {
            $blynkService = app(BlynkService::class);
            
            if ($this->fanStatus) {
                $success = $blynkService->turnFanOff();
                $action = 'desligado';
            } else {
                $success = $blynkService->turnFanOn();
                $action = 'ligado';
            }
            
            if ($success) {
                $this->fanStatus = !$this->fanStatus;
                $this->lastAction = "Ventilador {$action} às " . now()->format('H:i:s');
                
                // Envia notificação por email
                $this->sendEmailNotification($action);
                
                // Dispara evento para atualizar dashboard
                $this->dispatch('refresh-sensors');
                
                session()->flash('success', "Ventilador {$action} com sucesso!");
            } else {
                session()->flash('error', 'Falha ao controlar o ventilador. Tente novamente.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Erro de conexão: ' . $e->getMessage());
        }
        
        $this->isLoading = false;
    }

    /**
     * Envia notificação por email sobre mudança de estado do ventilador
     */
    private function sendEmailNotification(string $action): void
    {
        try {
            $emailTo = config('irrigation.notification_email', env('NOTIFICATION_EMAIL'));
            
            if ($emailTo) {
                Mail::raw(
                    "O ventilador foi {$action} às " . now()->format('d/m/Y H:i:s') . "\n\n" .
                    "Sistema de Irrigação Inteligente IoT",
                    function ($message) use ($emailTo, $action) {
                        $message->to($emailTo)
                                ->subject("Ventilador {$action} - Sistema IoT");
                    }
                );
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de notificação do ventilador: ' . $e->getMessage());
        }
    }

    /**
     * Retorna a classe CSS do botão baseada no status
     */
    public function getButtonClass(): string
    {
        return $this->fanStatus ? 'btn-warning' : 'btn-primary';
    }

    /**
     * Retorna o texto do botão baseado no status
     */
    public function getButtonText(): string
    {
        return $this->fanStatus ? 'Desligar Ventilador' : 'Ligar Ventilador';
    }

    /**
     * Retorna o ícone do botão baseado no status
     */
    public function getButtonIcon(): string
    {
        return $this->fanStatus ? 'bi-stop-fill' : 'bi-play-fill';
    }

    public function render()
    {
        return view('livewire.fan-control');
    }
}
