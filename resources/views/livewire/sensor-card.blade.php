<div class="card sensor-card h-100 {{ $this->getCardClass() }}">
    <div class="card-body text-center">
        <!-- Ícone do Sensor -->
        <div class="mb-3">
            <i class="{{ $icon }} text-{{ $color }}" style="font-size: 2.5rem;"></i>
        </div>
        
        <!-- Título -->
        <h6 class="card-title text-muted mb-2">{{ $title }}</h6>
        
        <!-- Valor -->
        <div class="sensor-value text-{{ $color }} mb-2">
            @if($value !== null)
                {{ number_format($value, 1) }}<small class="text-muted">{{ $unit }}</small>
            @else
                <span class="text-muted">--</span>
            @endif
        </div>
        
        <!-- Alerta de Limiar -->
        @if($showThreshold && $this->isBelowThreshold())
            <div class="alert alert-warning py-2 mb-0">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <small>Abaixo do limiar ({{ $threshold }}{{ $unit }})</small>
            </div>
        @elseif($showThreshold)
            <div class="text-muted">
                <small>Limiar: {{ $threshold }}{{ $unit }}</small>
            </div>
        @endif
        
        <!-- Barra de Progresso (para sensores com % ou valores conhecidos) -->
        @if($value !== null && in_array($unit, ['%']))
            <div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar bg-{{ $color }}" 
                     role="progressbar" 
                     style="width: {{ min(100, max(0, $value)) }}%"
                     aria-valuenow="{{ $value }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
            </div>
        @endif
    </div>
    
    <!-- Footer com timestamp (opcional) -->
    <div class="card-footer bg-transparent border-0 text-center">
        <small class="text-muted last-update">
            <i class="bi bi-clock me-1"></i>
            <span class="last-update-time">--:--:--</span>
        </small>
    </div>
</div>
