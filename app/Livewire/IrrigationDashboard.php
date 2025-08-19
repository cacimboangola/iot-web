<?php

namespace App\Livewire;

use App\Services\BlynkService;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Dashboard principal do sistema de irrigação inteligente
 * Centraliza a exibição de todos os sensores e controles
 */
class IrrigationDashboard extends Component
{
    public array $sensorData = [];
    public bool $isLoading = true;
    public ?string $lastUpdate = null;

    public function mount()
    {
        $this->loadSensorData();
    }

    /**
     * Carrega todos os dados dos sensores via BlynkService
     */
    public function loadSensorData(): void
    {
        $this->isLoading = true;
        
        try {
            $blynkService = app(BlynkService::class);
            $this->sensorData = $blynkService->getAllSensorData();
            $this->lastUpdate = now()->format('H:i:s');
        } catch (\Exception $e) {
            $this->sensorData = $this->getSimulatedData();
            $this->lastUpdate = now()->format('H:i:s') . ' (simulado)';
        }
        
        $this->isLoading = false;
    }

    /**
     * Dados simulados para quando a API não responde
     */
    private function getSimulatedData(): array
    {
        return [
            'soil_humidity' => rand(20, 80),
            'air_temperature' => rand(18, 35),
            'air_humidity' => rand(40, 90),
            'luminosity' => rand(0, 100),
            'pump_status' => (bool) rand(0, 1),
            'auto_mode_status' => (bool) rand(0, 1),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Listener para atualização automática dos dados
     */
    #[On('refresh-sensors')]
    public function refreshSensors(): void
    {
        $this->loadSensorData();
    }

    public function render()
    {
        return view('livewire.irrigation-dashboard')
            ->layout('layouts.app', ['title' => 'Sistema de Irrigação Inteligente']);
    }
}
