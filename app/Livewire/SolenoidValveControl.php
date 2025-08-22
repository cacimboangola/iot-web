<?php

namespace App\Livewire;

use App\Services\BlynkService;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

/**
 * Componente para controlar a válvula solenoide
 * Permite abrir/fechar manualmente a válvula e envia notificações por email
 */
class SolenoidValveControl extends Component
{
    public bool $valveStatus = false;
    public bool $isLoading = false;
    public ?string $lastAction = null;

    public function mount(bool $valveStatus = false)
    {
        $this->valveStatus = $valveStatus;
    }

    /**
     * Alterna o estado da válvula solenoide (abre/fecha)
     */
    public function toggleValve(): void
    {
        $this->isLoading = true;
        
        try {
            $blynkService = app(BlynkService::class);
            
            if ($this->valveStatus) {
                $success = $blynkService->closeSolenoidValve();
                $action = 'fechada';
            } else {
                $success = $blynkService->openSolenoidValve();
                $action = 'aberta';
            }
            
            if ($success) {
                $this->valveStatus = !$this->valveStatus;
                $this->lastAction = "Válvula {$action} às " . now()->format('H:i:s');
                
                // Envia notificação por email
                $this->sendEmailNotification($action);
                
                // Dispara evento para atualizar dashboard
                $this->dispatch('refresh-sensors');
                
                session()->flash('success', "Válvula solenoide {$action} com sucesso!");
            } else {
                session()->flash('error', 'Falha ao controlar a válvula. Tente novamente.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Erro de conexão: ' . $e->getMessage());
        }
        
        $this->isLoading = false;
    }

    /**
     * Envia notificação por email sobre mudança de estado da válvula
     */
    private function sendEmailNotification(string $action): void
    {
        try {
            $emailTo = config('irrigation.notification_email', env('NOTIFICATION_EMAIL'));
            
            if ($emailTo) {
                Mail::raw(
                    "A válvula solenoide foi {$action} às " . now()->format('d/m/Y H:i:s') . "\n\n" .
                    "Sistema de Irrigação Inteligente IoT",
                    function ($message) use ($emailTo, $action) {
                        $message->to($emailTo)
                                ->subject("Válvula Solenoide {$action} - Sistema IoT");
                    }
                );
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de notificação da válvula: ' . $e->getMessage());
        }
    }

    /**
     * Retorna a classe CSS do botão baseada no status
     */
    public function getButtonClass(): string
    {
        return $this->valveStatus ? 'btn-info' : 'btn-secondary';
    }

    /**
     * Retorna o texto do botão baseado no status
     */
    public function getButtonText(): string
    {
        return $this->valveStatus ? 'Fechar Válvula' : 'Abrir Válvula';
    }

    /**
     * Retorna o ícone do botão baseado no status
     */
    public function getButtonIcon(): string
    {
        return $this->valveStatus ? 'bi-unlock-fill' : 'bi-lock-fill';
    }

    public function render()
    {
        return view('livewire.solenoid-valve-control');
    }
}
