<div>
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}
    <!-- Header da Página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-gear-fill text-primary me-2"></i>
                        Configurações do Sistema
                    </h1>
                    <p class="text-muted mb-0">
                        Configure os parâmetros do sistema de irrigação inteligente
                    </p>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Voltar ao Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form wire:submit="saveSettings">
        <div class="row">
            <!-- Configurações de Irrigação -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-droplet-fill me-2"></i>
                            Configurações de Irrigação
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Limiar de Humidade -->
                        <div class="mb-3">
                            <label for="humidityThreshold" class="form-label">
                                <i class="bi bi-sliders me-1"></i>
                                Limiar de Humidade do Solo
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       wire:model="humidityThreshold" 
                                       class="form-control @error('humidityThreshold') is-invalid @enderror" 
                                       id="humidityThreshold"
                                       min="10" 
                                       max="90" 
                                       placeholder="30">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('humidityThreshold')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                A bomba será acionada quando a humidade estiver abaixo deste valor (10% - 90%)
                            </small>
                        </div>

                        <!-- Intervalo de Atualização -->
                        <div class="mb-3">
                            <label for="pollingInterval" class="form-label">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Intervalo de Atualização
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       wire:model="pollingInterval" 
                                       class="form-control @error('pollingInterval') is-invalid @enderror" 
                                       id="pollingInterval"
                                       min="1" 
                                       max="60" 
                                       placeholder="5">
                                <span class="input-group-text">segundos</span>
                            </div>
                            @error('pollingInterval')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Frequência de atualização dos dados dos sensores (1 - 60 segundos)
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configurações do Blynk -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-cloud-fill me-2"></i>
                            Configurações do Blynk Cloud
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Token do Blynk -->
                        <div class="mb-3">
                            <label for="blynkToken" class="form-label">
                                <i class="bi bi-key-fill me-1"></i>
                                Token de Autenticação
                            </label>
                            <input type="password" 
                                   wire:model="blynkToken" 
                                   class="form-control @error('blynkToken') is-invalid @enderror" 
                                   id="blynkToken"
                                   placeholder="XDMd-ylA4lL9DcNZeNhZjBgdNPZmzOSh">
                            @error('blynkToken')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Token de autenticação do seu dispositivo no Blynk Cloud
                            </small>
                        </div>

                        <!-- URL Base do Blynk -->
                        <div class="mb-3">
                            <label for="blynkBaseUrl" class="form-label">
                                <i class="bi bi-link-45deg me-1"></i>
                                URL Base da API
                            </label>
                            <input type="url" 
                                   wire:model="blynkBaseUrl" 
                                   class="form-control @error('blynkBaseUrl') is-invalid @enderror" 
                                   id="blynkBaseUrl"
                                   placeholder="https://blynk.cloud/external/api">
                            @error('blynkBaseUrl')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                URL base da API do Blynk Cloud
                            </small>
                        </div>

                        <!-- Botão de Teste -->
                        <div class="d-grid">
                            <button type="button" 
                                    wire:click="testConnection" 
                                    class="btn btn-outline-info"
                                    {{ $isLoading ? 'disabled' : '' }}>
                                @if($isLoading)
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                @else
                                    <i class="bi bi-wifi me-2"></i>
                                @endif
                                Testar Conexão
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Salvar Configurações</h6>
                                <small class="text-muted">As alterações serão aplicadas imediatamente</small>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" 
                                        wire:click="resetToDefaults" 
                                        class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-2"></i>
                                    Restaurar Padrões
                                </button>
                                <button type="submit" 
                                        class="btn btn-primary"
                                        {{ $isLoading ? 'disabled' : '' }}>
                                    @if($isLoading)
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Salvando...
                                    @else
                                        <i class="bi bi-check-lg me-2"></i>
                                        Salvar Configurações
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

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
                        <div class="col-md-4">
                            <h6>Versão do Sistema</h6>
                            <ul class="list-unstyled mb-0">
                                <li><strong>Laravel:</strong> {{ app()->version() }}</li>
                                <li><strong>Livewire:</strong> 3.x</li>
                                <li><strong>Bootstrap:</strong> 5.3</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6>Configurações Atuais</h6>
                            <ul class="list-unstyled mb-0">
                                <li><strong>Limiar:</strong> {{ $humidityThreshold }}%</li>
                                <li><strong>Intervalo:</strong> {{ $pollingInterval }}s</li>
                                <li><strong>Ambiente:</strong> {{ app()->environment() }}</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6>Endpoints da API</h6>
                            <ul class="list-unstyled mb-0">
                                <li><a href="{{ route('api.sensors') }}" target="_blank" class="text-decoration-none">📊 Dados dos Sensores</a></li>
                                <li><a href="https://blynk.cloud" target="_blank" class="text-decoration-none">🌐 Blynk Cloud</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
