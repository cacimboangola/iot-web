<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

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
        $this->baseUrl = env('BLYNK_API_URL', 'https://blynk.cloud/external/api');
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
     * Lê o estado do ventilador (V9)
     * @return bool|null Estado do ventilador (true = ligado, false = desligado)
     */
    public function getFanStatus(): ?bool
    {
        $value = $this->getSensorValue('V9');
        return $value !== null ? (bool) $value : null;
    }

    /**
     * Lê o estado da válvula solenoide (V10)
     * @return bool|null Estado da válvula (true = aberta, false = fechada)
     */
    public function getSolenoidValveStatus(): ?bool
    {
        $value = $this->getSensorValue('V10');
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
     * Liga o ventilador
     * @return bool Sucesso da operação
     */
    public function turnFanOn(): bool
    {
        return $this->updatePin('V7', 1);
    }

    /**
     * Desliga o ventilador
     * @return bool Sucesso da operação
     */
    public function turnFanOff(): bool
    {
        return $this->updatePin('V7', 0);
    }

    /**
     * Abre a válvula solenoide
     * @return bool Sucesso da operação
     */
    public function openSolenoidValve(): bool
    {
        return $this->updatePin('V8', 1);
    }

    /**
     * Fecha a válvula solenoide
     * @return bool Sucesso da operação
     */
    public function closeSolenoidValve(): bool
    {
        return $this->updatePin('V8', 0);
    }

    /**
     * Obtém todos os dados dos sensores de uma vez
     * @return array Array com todos os valores dos sensores
     */
    public function getAllSensorData(): array
    {
        $data = [
            'soil_humidity' => $this->getSoilHumidity(),
            'air_temperature' => $this->getAirTemperature(),
            'air_humidity' => $this->getAirHumidity(),
            'luminosity' => $this->getLuminosity(),
            'pump_status' => $this->getPumpStatus(),
            'auto_mode_status' => $this->getAutoModeStatus(),
            'fan_status' => $this->getFanStatus(),
            'solenoid_valve_status' => $this->getSolenoidValveStatus(),
            'timestamp' => now()->toISOString(),
        ];

        // Se todos os valores são null, usa dados simulados
        $hasValidData = collect($data)->except('timestamp')->filter()->isNotEmpty();

        /*if (!$hasValidData) {
            return $this->getSimulatedData();
        }*/

        return $data;
    }

    /**
     * Gera dados simulados quando a API não responde
     * @return array Array com dados simulados realistas
     */
    private function getSimulatedData(): array
    {
        $airTemp = rand(18, 35);
        $soilHumidity = rand(20, 80);

        return [
            'soil_humidity' => $soilHumidity,
            'air_temperature' => $airTemp,
            'air_humidity' => rand(40, 90),
            'luminosity' => rand(0, 100),
            'pump_status' => $soilHumidity < 30,
            'auto_mode_status' => (bool) rand(0, 1),
            'fan_status' => $airTemp > 28 || rand(0, 1),
            'solenoid_valve_status' => $soilHumidity < 40 || rand(0, 1),
            'timestamp' => now()->toISOString(),
            'simulated' => true,
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
