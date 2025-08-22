<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações do Sistema de Irrigação
    |--------------------------------------------------------------------------
    |
    | Configurações específicas para o sistema de irrigação inteligente
    | incluindo limiares, intervalos de polling e outras configurações.
    |
    */

    'humidity_threshold' => env('IRRIGATION_HUMIDITY_THRESHOLD', 30),
    'polling_interval' => env('IRRIGATION_POLLING_INTERVAL', 5000),
    
    /*
    |--------------------------------------------------------------------------
    | Configurações dos Sensores
    |--------------------------------------------------------------------------
    */
    'sensors' => [
        'soil_humidity' => [
            'pin' => 'V0',
            'name' => 'Humidade do Solo',
            'unit' => '%',
            'icon' => 'bi-droplet-fill',
            'color' => 'info',
            'min_value' => 0,
            'max_value' => 100,
        ],
        'air_temperature' => [
            'pin' => 'V1',
            'name' => 'Temperatura do Ar',
            'unit' => '°C',
            'icon' => 'bi-thermometer-half',
            'color' => 'danger',
            'min_value' => -10,
            'max_value' => 50,
        ],
        'air_humidity' => [
            'pin' => 'V2',
            'name' => 'Humidade do Ar',
            'unit' => '%',
            'icon' => 'bi-moisture',
            'color' => 'primary',
            'min_value' => 0,
            'max_value' => 100,
        ],
        'luminosity' => [
            'pin' => 'V6',
            'name' => 'Luminosidade',
            'unit' => '%',
            'icon' => 'bi-brightness-high-fill',
            'color' => 'warning',
            'min_value' => 0,
            'max_value' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Controle
    |--------------------------------------------------------------------------
    */
    'controls' => [
        'pump' => [
            'control_pin' => 'V3',
            'status_pin' => 'V4',
            'name' => 'Bomba de Irrigação',
        ],
        'auto_mode' => [
            'control_pin' => 'V5',
            'status_pin' => 'V5',
            'name' => 'Modo Automático',
        ],
        'fan' => [
            'control_pin' => 'V7',
            'status_pin' => 'V9',
            'name' => 'Ventilador',
        ],
        'solenoid_valve' => [
            'control_pin' => 'V8',
            'status_pin' => 'V10',
            'name' => 'Válvula Solenoide',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Notificações por Email
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'email' => env('NOTIFICATION_EMAIL', null),
        'enabled' => env('NOTIFICATIONS_ENABLED', true),
        'events' => [
            'pump_on' => env('NOTIFY_PUMP_ON', true),
            'pump_off' => env('NOTIFY_PUMP_OFF', true),
            'fan_on' => env('NOTIFY_FAN_ON', true),
            'fan_off' => env('NOTIFY_FAN_OFF', true),
            'valve_open' => env('NOTIFY_VALVE_OPEN', true),
            'valve_close' => env('NOTIFY_VALVE_CLOSE', true),
        ],
    ],
];
