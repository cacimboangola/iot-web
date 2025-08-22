<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Seeder para simular dados de sensores quando a API do Blynk não estiver disponível
 * Útil para desenvolvimento e testes do sistema
 */
class IrrigationDataSeeder extends Seeder
{
    /**
     * Executa o seeding dos dados simulados
     */
    public function run(): void
    {
        $this->command->info('Gerando dados simulados para o sistema de irrigação...');
        
        // Gera dados simulados realistas
        $simulatedData = $this->generateRealisticSensorData();
        
        // Armazena os dados no cache para uso pela aplicação
        Cache::put('irrigation_simulated_data', $simulatedData, now()->addHours(24));
        
        // Log da operação
        Log::info('Dados simulados de irrigação gerados', $simulatedData);
        
        $this->command->info('Dados simulados gerados com sucesso!');
        $this->displaySimulatedData($simulatedData);
    }
    
    /**
     * Gera dados realistas dos sensores
     */
    private function generateRealisticSensorData(): array
    {
        // Simula condições baseadas na hora do dia
        $hour = now()->hour;
        $isDay = $hour >= 6 && $hour <= 18;
        $isMorning = $hour >= 6 && $hour <= 10;
        $isEvening = $hour >= 18 && $hour <= 22;
        
        // Humidade do solo - varia entre 20-80%
        $soilHumidity = $this->generateRealisticValue(25, 75, 'soil_humidity');
        
        // Temperatura do ar - varia com a hora do dia
        if ($isDay) {
            $airTemperature = $this->generateRealisticValue(22, 35, 'air_temperature');
        } else {
            $airTemperature = $this->generateRealisticValue(15, 25, 'air_temperature');
        }
        
        // Humidade do ar - inversamente relacionada à temperatura
        $baseAirHumidity = $isDay ? 45 : 70;
        $airHumidity = $this->generateRealisticValue($baseAirHumidity - 15, $baseAirHumidity + 15, 'air_humidity');
        
        // Luminosidade - baseada na hora do dia
        if ($isMorning || $isEvening) {
            $luminosity = $this->generateRealisticValue(30, 70, 'luminosity');
        } elseif ($isDay) {
            $luminosity = $this->generateRealisticValue(70, 100, 'luminosity');
        } else {
            $luminosity = $this->generateRealisticValue(0, 20, 'luminosity');
        }
        
        // Status da bomba - baseado na humidade do solo
        $pumpStatus = $soilHumidity < config('irrigation.humidity_threshold', 30);
        
        // Modo automático - aleatório mas tendendo a estar ativo
        $autoModeStatus = rand(1, 10) <= 7; // 70% de chance de estar ativo
        
        // Status do ventilador - baseado na temperatura (liga quando quente)
        $fanStatus = $airTemperature > 28 || rand(1, 10) <= 3; // Liga se temp > 28°C ou 30% chance aleatória
        
        // Status da válvula solenoide - baseado na humidade do solo (abre quando seco)
        $solenoidValveStatus = $soilHumidity < 40 || rand(1, 10) <= 2; // Abre se humidade < 40% ou 20% chance aleatória
        
        return [
            'soil_humidity' => round($soilHumidity, 1),
            'air_temperature' => round($airTemperature, 1),
            'air_humidity' => round($airHumidity, 1),
            'luminosity' => round($luminosity, 1),
            'pump_status' => $pumpStatus,
            'auto_mode_status' => $autoModeStatus,
            'fan_status' => $fanStatus,
            'solenoid_valve_status' => $solenoidValveStatus,
            'timestamp' => now()->toISOString(),
            'simulated' => true,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
    
    /**
     * Gera valores realistas com base em tendências
     */
    private function generateRealisticValue(float $min, float $max, string $type): float
    {
        // Obtém valor anterior do cache para criar tendência
        $previousData = Cache::get('irrigation_simulated_data', []);
        $previousValue = $previousData[$type] ?? null;
        
        if ($previousValue !== null) {
            // Cria variação suave baseada no valor anterior
            $variation = ($max - $min) * 0.1; // 10% de variação máxima
            $newMin = max($min, $previousValue - $variation);
            $newMax = min($max, $previousValue + $variation);
            
            return $newMin + (mt_rand() / mt_getrandmax()) * ($newMax - $newMin);
        }
        
        // Primeira geração - valor aleatório no range
        return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
    }
    
    /**
     * Exibe os dados simulados no console
     */
    private function displaySimulatedData(array $data): void
    {
        $this->command->table(
            ['Sensor', 'Valor', 'Unidade', 'Status'],
            [
                ['Humidade do Solo', $data['soil_humidity'], '%', $data['soil_humidity'] < 30 ? '⚠️ Baixa' : '✅ OK'],
                ['Temperatura do Ar', $data['air_temperature'], '°C', '📊 Normal'],
                ['Humidade do Ar', $data['air_humidity'], '%', '📊 Normal'],
                ['Luminosidade', $data['luminosity'], '%', '📊 Normal'],
                ['Bomba', $data['pump_status'] ? 'Ligada' : 'Desligada', '-', $data['pump_status'] ? '🟢 ON' : '🔴 OFF'],
                ['Modo Automático', $data['auto_mode_status'] ? 'Ativo' : 'Inativo', '-', $data['auto_mode_status'] ? '🤖 AUTO' : '👤 MANUAL'],
                ['Ventilador', $data['fan_status'] ? 'Ligado' : 'Desligado', '-', $data['fan_status'] ? '🌀 ON' : '⭕ OFF'],
                ['Válvula Solenoide', $data['solenoid_valve_status'] ? 'Aberta' : 'Fechada', '-', $data['solenoid_valve_status'] ? '🔓 OPEN' : '🔒 CLOSED'],
            ]
        );
        
        $this->command->info("\n💡 Dica: Use 'php artisan db:seed --class=IrrigationDataSeeder' para gerar novos dados simulados.");
    }
}
