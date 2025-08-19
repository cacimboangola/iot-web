<div>
    {{-- Nothing in the world is as soft and yielding as water. --}}
    <!-- Status da Bomba -->
    <div class="row align-items-center mb-3">
        <div class="col-auto">
            <div class="status-indicator {{ $pumpStatus ? 'status-online' : 'status-offline' }}"></div>
        </div>
        <div class="col">
            <h6 class="mb-0">Status da Bomba</h6>
            <p class="text-muted mb-0">{{ $pumpStatus ? 'Ligada' : 'Desligada' }}</p>
        </div>
        <div class="col-auto">
            <i class="bi bi-{{ $pumpStatus ? 'droplet-fill' : 'droplet' }} text-{{ $pumpStatus ? 'success' : 'secondary' }}" style="font-size: 1.5rem;"></i>
        </div>
    </div>

    <!-- Botão de Controle -->
    <div class="d-grid mb-3">
        <button wire:click="togglePump" 
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

    <!-- Última Ação -->
    @if($lastAction)
        <div class="alert alert-info py-2">
            <i class="bi bi-info-circle me-2"></i>
            <small>{{ $lastAction }}</small>
        </div>
    @endif

    <!-- Informações Adicionais -->
    <div class="row text-center">
        <div class="col-6">
            <div class="border-end">
                <div class="h5 mb-0 text-{{ $pumpStatus ? 'success' : 'muted' }}">
                    <i class="bi bi-{{ $pumpStatus ? 'play-fill' : 'stop-fill' }}"></i>
                </div>
                <small class="text-muted">Estado</small>
            </div>
        </div>
        <div class="col-6">
            <div class="h5 mb-0 text-primary">
                <i class="bi bi-gear-fill"></i>
            </div>
            <small class="text-muted">Manual</small>
        </div>
    </div>
</div>
