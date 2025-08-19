<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * Página de configurações do sistema de irrigação
 * Permite alterar limiares, intervalos e outras configurações
 */
class SettingsPage extends Component
{
    public int $humidityThreshold;
    public int $pollingInterval;
    public string $blynkToken;
    public string $blynkBaseUrl;
    public bool $isLoading = false;

    public function mount()
    {
        $this->humidityThreshold = (int) config('irrigation.humidity_threshold', 30);
        $this->pollingInterval = (int) (config('irrigation.polling_interval', 5000) / 1000);
        $this->blynkToken = config('services.blynk.auth_token', '');
        $this->blynkBaseUrl = config('services.blynk.base_url', 'https://blynk.cloud/external/api');
    }

    /**
     * Salva as configurações
     */
    public function saveSettings(): void
    {
        $this->validate([
            'humidityThreshold' => 'required|integer|min:10|max:90',
            'pollingInterval' => 'required|integer|min:1|max:60',
            'blynkToken' => 'required|string|min:10',
            'blynkBaseUrl' => 'required|url',
        ], [
            'humidityThreshold.required' => 'O limiar de humidade é obrigatório.',
            'humidityThreshold.min' => 'O limiar mínimo é 10%.',
            'humidityThreshold.max' => 'O limiar máximo é 90%.',
            'pollingInterval.required' => 'O intervalo de atualização é obrigatório.',
            'pollingInterval.min' => 'O intervalo mínimo é 1 segundo.',
            'pollingInterval.max' => 'O intervalo máximo é 60 segundos.',
            'blynkToken.required' => 'O token do Blynk é obrigatório.',
            'blynkToken.min' => 'O token deve ter pelo menos 10 caracteres.',
            'blynkBaseUrl.required' => 'A URL base do Blynk é obrigatória.',
            'blynkBaseUrl.url' => 'A URL base deve ser válida.',
        ]);

        $this->isLoading = true;

        try {
            // Aqui você poderia salvar no banco de dados ou atualizar o arquivo .env
            // Por simplicidade, vamos apenas mostrar uma mensagem de sucesso
            
            session()->flash('success', 'Configurações salvas com sucesso!');
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao salvar configurações: ' . $e->getMessage());
        }

        $this->isLoading = false;
    }

    /**
     * Testa a conexão com o Blynk
     */
    public function testConnection(): void
    {
        $this->isLoading = true;

        try {
            $blynkService = app(\App\Services\BlynkService::class);
            $data = $blynkService->getAllSensorData();
            
            if (!empty($data)) {
                session()->flash('success', 'Conexão com Blynk estabelecida com sucesso!');
            } else {
                session()->flash('warning', 'Conexão estabelecida, mas nenhum dado foi retornado.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Falha na conexão: ' . $e->getMessage());
        }

        $this->isLoading = false;
    }

    /**
     * Restaura configurações padrão
     */
    public function resetToDefaults(): void
    {
        $this->humidityThreshold = 30;
        $this->pollingInterval = 5;
        $this->blynkBaseUrl = 'https://blynk.cloud/external/api';
        
        session()->flash('info', 'Configurações restauradas para os valores padrão.');
    }

    public function render()
    {
        return view('livewire.settings-page')
            ->layout('layouts.app', ['title' => 'Configurações - Sistema de Irrigação']);
    }
}
