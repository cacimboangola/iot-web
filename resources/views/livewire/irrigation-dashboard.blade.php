<div wire:poll.5s="loadSensorData">
    {{-- Dashboard com atualização automática a cada 5 segundos --}}
    <!-- Header do Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-droplet-fill text-primary me-2"></i>
                        Dashboard de Irrigação
                    </h1>
                    <p class="text-muted mb-0">
                        <span class="status-indicator {{ $isLoading ? 'status-offline' : 'status-online' }}"></span>
                        @if($lastUpdate)
                            Última atualização: {{ $lastUpdate }}
                            <small class="badge bg-success ms-2">
                                <i class="bi bi-arrow-clockwise"></i> Auto 5s
                            </small>
                        @else
                            Carregando dados...
                        @endif
                    </p>
                </div>
                <div>
                    <button wire:click="loadSensorData" class="btn btn-outline-primary" {{ $isLoading ? 'disabled' : '' }}>
                        @if($isLoading)
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        @else
                            <i class="bi bi-arrow-clockwise me-2"></i>
                        @endif
                        Atualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards dos Sensores -->
    <div class="row g-4 mb-4">
        <!-- Humidade do Solo -->
        <div class="col-lg-3 col-md-6">
            <livewire:sensor-card 
                title="Humidade do Solo"
                :value="$sensorData['soil_humidity'] ?? null"
                unit="%"
                icon="bi-droplet-fill"
                color="info"
                :threshold="30"
            />
        </div>

        <!-- Temperatura do Ar -->
        <div class="col-lg-3 col-md-6">
            <livewire:sensor-card 
                title="Temperatura do Ar"
                :value="$sensorData['air_temperature'] ?? null"
                unit="°C"
                icon="bi-thermometer-half"
                color="danger"
            />
        </div>

        <!-- Humidade do Ar -->
        <div class="col-lg-3 col-md-6">
            <livewire:sensor-card 
                title="Humidade do Ar"
                :value="$sensorData['air_humidity'] ?? null"
                unit="%"
                icon="bi-moisture"
                color="primary"
            />
        </div>

        <!-- Luminosidade -->
        <div class="col-lg-3 col-md-6">
            <livewire:sensor-card 
                title="Luminosidade"
                :value="$sensorData['luminosity'] ?? null"
                unit="%"
                icon="bi-brightness-high-fill"
                color="warning"
            />
        </div>
    </div>

    <!-- Controles -->
    <div class="row g-4 mb-4">
        <!-- Controle da Bomba -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear-fill me-2"></i>
                        Controle da Bomba
                    </h5>
                </div>
                <div class="card-body">
                    <livewire:pump-control 
                        :pumpStatus="$sensorData['pump_status'] ?? false"
                    />
                </div>
            </div>
        </div>

        <!-- Controle do Modo Automático -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-robot me-2"></i>
                        Modo Automático
                    </h5>
                </div>
                <div class="card-body">
                    <livewire:auto-mode-control 
                        :autoModeStatus="$sensorData['auto_mode_status'] ?? false"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Novos Controles de Atuadores -->
    <div class="row g-4">
        <!-- Controle do Ventilador -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-fan me-2"></i>
                        Controle do Ventilador
                    </h5>
                </div>
                <div class="card-body">
                    <livewire:fan-control 
                        :fanStatus="$sensorData['fan_status'] ?? false"
                    />
                </div>
            </div>
        </div>

        <!-- Controle da Válvula Solenoide -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-unlock-fill me-2"></i>
                        Controle da Válvula
                    </h5>
                </div>
                <div class="card-body">
                    <livewire:solenoid-valve-control 
                        :valveStatus="$sensorData['solenoid_valve_status'] ?? false"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Informações do Sistema -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Informações do Sistema
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Status da Conexão</h6>
                            <p class="mb-2">
                                <span class="status-indicator {{ $isLoading ? 'status-offline' : 'status-online' }}"></span>
                                {{ $isLoading ? 'Conectando...' : 'Online' }}
                            </p>
                            
                            <h6>Configurações Atuais</h6>
                            <ul class="list-unstyled mb-0">
                                <li><strong>Limiar de Humidade:</strong> {{ config('irrigation.humidity_threshold') }}%</li>
                                <li><strong>Intervalo de Atualização:</strong> {{ config('irrigation.polling_interval') / 1000 }}s</li>
                                <li><strong>Servidor Blynk:</strong> {{ config('services.blynk.base_url') }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Dados Técnicos</h6>
                            @if(!empty($sensorData))
                                <p class="mb-2"><strong>Timestamp:</strong> {{ $sensorData['timestamp'] ?? 'N/A' }}</p>
                                <p class="mb-2"><strong>Última Leitura:</strong> {{ $lastUpdate }}</p>
                            @endif
                            
                            <h6>Links Úteis</h6>
                            <ul class="list-unstyled mb-0">
                                <li><a href="{{ route('settings') }}" class="text-decoration-none">⚙️ Configurações</a></li>
                                <li><a href="https://blynk.cloud" target="_blank" class="text-decoration-none">🌐 Blynk Cloud</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
