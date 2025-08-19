<?php

namespace App\Livewire;

use App\Services\BlynkService;
use Livewire\Component;

/**
 * Componente para controlar a bomba de irrigação
 * Permite ligar/desligar manualmente a bomba
 */
class PumpControl extends Component
{
    public bool $pumpStatus = false;
    public bool $isLoading = false;
    public ?string $lastAction = null;

    public function mount(bool $pumpStatus = false)
    {
        $this->pumpStatus = $pumpStatus;
    }

    /**
     * Alterna o estado da bomba (liga/desliga)
     */
    public function togglePump(): void
    {
        $this->isLoading = true;
        
        try {
            $blynkService = app(BlynkService::class);
            
            if ($this->pumpStatus) {
                $success = $blynkService->turnPumpOff();
                $action = 'desligada';
            } else {
                $success = $blynkService->turnPumpOn();
                $action = 'ligada';
            }
            
            if ($success) {
                $this->pumpStatus = !$this->pumpStatus;
                $this->lastAction = "Bomba {$action} às " . now()->format('H:i:s');
                
                // Dispara evento para atualizar dashboard
                $this->dispatch('refresh-sensors');
                
                session()->flash('success', "Bomba {$action} com sucesso!");
            } else {
                session()->flash('error', 'Falha ao controlar a bomba. Tente novamente.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Erro de conexão: ' . $e->getMessage());
        }
        
        $this->isLoading = false;
    }

    /**
     * Retorna a classe CSS do botão baseada no status
     */
    public function getButtonClass(): string
    {
        return $this->pumpStatus ? 'btn-danger' : 'btn-success';
    }

    /**
     * Retorna o texto do botão baseado no status
     */
    public function getButtonText(): string
    {
        return $this->pumpStatus ? 'Desligar Bomba' : 'Ligar Bomba';
    }

    /**
     * Retorna o ícone do botão baseado no status
     */
    public function getButtonIcon(): string
    {
        return $this->pumpStatus ? 'bi-power' : 'bi-play-fill';
    }

    public function render()
    {
        return view('livewire.pump-control');
    }
}
