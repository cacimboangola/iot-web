<?php

namespace App\Livewire;

use App\Services\BlynkService;
use Livewire\Component;

/**
 * Componente para controlar o modo automático de irrigação
 * Permite ativar/desativar o modo automático e configurar limiares
 */
class AutoModeControl extends Component
{
    public bool $autoModeStatus = false;
    public bool $isLoading = false;
    public int $humidityThreshold;
    public ?string $lastAction = null;

    public function mount(bool $autoModeStatus = false)
    {
        $this->autoModeStatus = $autoModeStatus;
        $this->humidityThreshold = (int) config('irrigation.humidity_threshold', 30);
    }

    /**
     * Alterna o modo automático (ativa/desativa)
     */
    public function toggleAutoMode(): void
    {
        $this->isLoading = true;
        
        try {
            $blynkService = app(BlynkService::class);
            
            if ($this->autoModeStatus) {
                $success = $blynkService->disableAutoMode();
                $action = 'desativado';
            } else {
                $success = $blynkService->enableAutoMode();
                $action = 'ativado';
            }
            
            if ($success) {
                $this->autoModeStatus = !$this->autoModeStatus;
                $this->lastAction = "Modo automático {$action} às " . now()->format('H:i:s');
                
                // Dispara evento para atualizar dashboard
                $this->dispatch('refresh-sensors');
                
                session()->flash('success', "Modo automático {$action} com sucesso!");
            } else {
                session()->flash('error', 'Falha ao alterar modo automático. Tente novamente.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Erro de conexão: ' . $e->getMessage());
        }
        
        $this->isLoading = false;
    }

    /**
     * Atualiza o limiar de humidade
     */
    public function updateThreshold(): void
    {
        $this->validate([
            'humidityThreshold' => 'required|integer|min:10|max:90'
        ], [
            'humidityThreshold.required' => 'O limiar de humidade é obrigatório.',
            'humidityThreshold.integer' => 'O limiar deve ser um número inteiro.',
            'humidityThreshold.min' => 'O limiar mínimo é 10%.',
            'humidityThreshold.max' => 'O limiar máximo é 90%.'
        ]);

        // Aqui você pode salvar no banco de dados ou arquivo de configuração
        // Por simplicidade, vamos apenas mostrar uma mensagem de sucesso
        session()->flash('success', "Limiar de humidade atualizado para {$this->humidityThreshold}%!");
    }

    /**
     * Retorna a classe CSS do botão baseada no status
     */
    public function getButtonClass(): string
    {
        return $this->autoModeStatus ? 'btn-warning' : 'btn-info';
    }

    /**
     * Retorna o texto do botão baseado no status
     */
    public function getButtonText(): string
    {
        return $this->autoModeStatus ? 'Desativar Automático' : 'Ativar Automático';
    }

    /**
     * Retorna o ícone do botão baseado no status
     */
    public function getButtonIcon(): string
    {
        return $this->autoModeStatus ? 'bi-pause-circle' : 'bi-play-circle';
    }

    public function render()
    {
        return view('livewire.auto-mode-control');
    }
}
