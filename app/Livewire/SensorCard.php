<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * Componente reutilizável para exibir dados de sensores
 * Usado para mostrar humidade, temperatura, luminosidade, etc.
 */
class SensorCard extends Component
{
    public string $title;
    public ?float $value;
    public string $unit;
    public string $icon;
    public string $color;
    public ?float $threshold = null;
    public bool $showThreshold = false;

    public function mount(
        string $title,
        ?float $value,
        string $unit,
        string $icon,
        string $color = 'primary',
        ?float $threshold = null
    ) {
        $this->title = $title;
        $this->value = $value;
        $this->unit = $unit;
        $this->icon = $icon;
        $this->color = $color;
        $this->threshold = $threshold;
        $this->showThreshold = $threshold !== null;
    }

    /**
     * Determina se o valor está abaixo do limiar (para alertas)
     */
    public function isBelowThreshold(): bool
    {
        return $this->threshold !== null && 
               $this->value !== null && 
               $this->value < $this->threshold;
    }

    /**
     * Retorna a classe CSS baseada no status do sensor
     */
    public function getCardClass(): string
    {
        if ($this->isBelowThreshold()) {
            return 'border-warning bg-warning-subtle';
        }
        
        return 'border-' . $this->color;
    }

    public function render()
    {
        return view('livewire.sensor-card');
    }
}
