<div>
    {{-- The whole world belongs to you. --}}
    <!-- Status do Modo Automático -->
    <div class="row align-items-center mb-3">
        <div class="col-auto">
            <div class="status-indicator {{ $autoModeStatus ? 'status-online' : 'status-offline' }}"></div>
        </div>
        <div class="col">
            <h6 class="mb-0">Modo Automático</h6>
            <p class="text-muted mb-0">{{ $autoModeStatus ? 'Ativo' : 'Inativo' }}</p>
        </div>
        <div class="col-auto">
            <i class="bi bi-{{ $autoModeStatus ? 'robot' : 'gear' }} text-{{ $autoModeStatus ? 'info' : 'secondary' }}" style="font-size: 1.5rem;"></i>
        </div>
    </div>

    <!-- Botão de Controle -->
    <div class="d-grid mb-3">
        <button wire:click="toggleAutoMode" 
                class="btn {{ $this->getButtonClass() }} btn-lg"
                {{ $isLoading ? 'disabled' : '' }}>
            @if($isLoading)
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Processando...
            @else
                <i class="{{ $this->getButtonIcon() }} me-2"></i>
                {{ $this->getButtonText() }}
            @endif
        </button>
    </div>

    <!-- Configuração de Limiar -->
    <div class="card bg-light mb-3">
        <div class="card-body">
            <h6 class="card-title mb-3">
                <i class="bi bi-sliders me-2"></i>
                Limiar de Humidade
            </h6>
            
            <form wire:submit="updateThreshold">
                <div class="input-group">
                    <input type="number" 
                           wire:model="humidityThreshold" 
                           class="form-control" 
                           min="10" 
                           max="90" 
                           placeholder="30">
                    <span class="input-group-text">%</span>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-check-lg"></i>
                    </button>
                </div>
                @error('humidityThreshold')
                    <div class="text-danger mt-1">
                        <small>{{ $message }}</small>
                    </div>
                @enderror
            </form>
            
            <small class="text-muted mt-2 d-block">
                A bomba será acionada quando a humidade do solo estiver abaixo deste valor.
            </small>
        </div>
    </div>

    <!-- Última Ação -->
    @if($lastAction)
        <div class="alert alert-info py-2">
            <i class="bi bi-info-circle me-2"></i>
            <small>{{ $lastAction }}</small>
        </div>
    @endif

    <!-- Informações do Modo Automático -->
    <div class="row text-center">
        <div class="col-4">
            <div class="border-end">
                <div class="h5 mb-0 text-{{ $autoModeStatus ? 'info' : 'muted' }}">
                    <i class="bi bi-{{ $autoModeStatus ? 'play-circle-fill' : 'pause-circle' }}"></i>
                </div>
                <small class="text-muted">Estado</small>
            </div>
        </div>
        <div class="col-4">
            <div class="border-end">
                <div class="h5 mb-0 text-primary">
                    {{ $humidityThreshold }}<small>%</small>
                </div>
                <small class="text-muted">Limiar</small>
            </div>
        </div>
        <div class="col-4">
            <div class="h5 mb-0 text-success">
                <i class="bi bi-droplet-fill"></i>
            </div>
            <small class="text-muted">Auto</small>
        </div>
    </div>
</div>
