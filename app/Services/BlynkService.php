<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service class para centralizar todas as chamadas à API do Blynk Cloud
 * Responsável por ler sensores e controlar dispositivos do sistema de irrigação
 */
class BlynkService
{
    private string $baseUrl;
    private string $authToken;

    public function __construct()
    {
        $this->baseUrl = env('BLYNK_API_URL', 'https://blynk.cloud');
        $this->authToken = env('BLYNK_AUTH_TOKEN', 'XDMd-ylA4lL9DcNZeNhZjBgdNPZmzOSh');
    }

    /**
     * Lê o valor de humidade do solo (V0)
     * @return float|null Percentual de humidade do solo
     */
    public function getSoilHumidity(): ?float
    {
        return $this->getSensorValue('V0');
    }

    /**
     * Lê o valor de temperatura do ar (V1)
     * @return float|null Temperatura em graus Celsius
     */
    public function getAirTemperature(): ?float
    {
        return $this->getSensorValue('V1');
    }

    /**
     * Lê o valor de humidade do ar (V2)
     * @return float|null Percentual de humidade do ar
     */
    public function getAirHumidity(): ?float
    {
        return $this->getSensorValue('V2');
    }

    /**
     * Lê o valor de luminosidade (V6)
     * @return float|null Percentual de luminosidade
     */
    public function getLuminosity(): ?float
    {
        return $this->getSensorValue('V6');
    }

    /**
     * Lê o estado da bomba (V4)
     * @return bool|null Estado da bomba (true = ligada, false = desligada)
     */
    public function getPumpStatus(): ?bool
    {
        $value = $this->getSensorValue('V4');
        return $value !== null ? (bool) $value : null;
    }

    /**
     * Lê o estado do modo automático (V5)
     * @return bool|null Estado do modo automático (true = ativo, false = inativo)
     */
    public function getAutoModeStatus(): ?bool
    {
        $value = $this->getSensorValue('V5');
        return $value !== null ? (bool) $value : null;
    }

    /**
     * Liga a bomba de irrigação
     * @return bool Sucesso da operação
     */
    public function turnPumpOn(): bool
    {
        return $this->updatePin('V3', 1);
    }

    /**
     * Desliga a bomba de irrigação
     * @return bool Sucesso da operação
     */
    public function turnPumpOff(): bool
    {
        return $this->updatePin('V3', 0);
    }

    /**
     * Ativa o modo automático
     * @return bool Sucesso da operação
     */
    public function enableAutoMode(): bool
    {
        return $this->updatePin('V5', 1);
    }

    /**
     * Desativa o modo automático
     * @return bool Sucesso da operação
     */
    public function disableAutoMode(): bool
    {
        return $this->updatePin('V5', 0);
    }

    /**
     * Obtém todos os dados dos sensores de uma vez
     * @return array Array com todos os valores dos sensores
     */
    public function getAllSensorData(): array
    {
        return [
            'soil_humidity' => $this->getSoilHumidity(),
            'air_temperature' => $this->getAirTemperature(),
            'air_humidity' => $this->getAirHumidity(),
            'luminosity' => $this->getLuminosity(),
            'pump_status' => $this->getPumpStatus(),
            'auto_mode_status' => $this->getAutoModeStatus(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Método privado para ler valores de sensores
     * @param string $pin Pin virtual do Blynk (ex: V0, V1, etc.)
     * @return float|null Valor do sensor ou null em caso de erro
     */
    private function getSensorValue(string $pin): ?float
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/get', [
                'token' => $this->authToken,
                $pin => ''
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return is_numeric($data) ? (float) $data : null;
            }

            Log::warning("Falha ao ler sensor {$pin}", [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (Exception $e) {
            Log::error("Erro ao conectar com Blynk para sensor {$pin}", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Método privado para atualizar pins de controle
     * @param string $pin Pin virtual do Blynk (ex: V3, V5)
     * @param int $value Valor a ser enviado (0 ou 1)
     * @return bool Sucesso da operação
     */
    private function updatePin(string $pin, int $value): bool
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/update', [
                'token' => $this->authToken,
                $pin => $value
            ]);

            if ($response->successful()) {
                Log::info("Pin {$pin} atualizado com sucesso", ['value' => $value]);
                return true;
            }

            Log::warning("Falha ao atualizar pin {$pin}", [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return false;
        } catch (Exception $e) {
            Log::error("Erro ao conectar com Blynk para atualizar pin {$pin}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
