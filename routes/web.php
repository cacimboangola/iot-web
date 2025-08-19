<?php

use App\Livewire\IrrigationDashboard;
use App\Livewire\SettingsPage;
use Illuminate\Support\Facades\Route;

/**
 * Rota principal - Dashboard do sistema de irrigação
 */
Route::get('/', IrrigationDashboard::class)->name('dashboard');

/**
 * Página de configurações
 */
Route::get('/settings', SettingsPage::class)->name('settings');

/**
 * API Routes para integração externa (opcional)
 */
Route::prefix('api')->group(function () {
    Route::get('/sensors', function () {
        $blynkService = app(\App\Services\BlynkService::class);
        return response()->json($blynkService->getAllSensorData());
    })->name('api.sensors');
    
    Route::post('/pump/toggle', function () {
        $blynkService = app(\App\Services\BlynkService::class);
        $currentStatus = $blynkService->getPumpStatus();
        
        if ($currentStatus) {
            $success = $blynkService->turnPumpOff();
        } else {
            $success = $blynkService->turnPumpOn();
        }
        
        return response()->json([
            'success' => $success,
            'new_status' => !$currentStatus
        ]);
    })->name('api.pump.toggle');
    
    Route::post('/auto-mode/toggle', function () {
        $blynkService = app(\App\Services\BlynkService::class);
        $currentStatus = $blynkService->getAutoModeStatus();
        
        if ($currentStatus) {
            $success = $blynkService->disableAutoMode();
        } else {
            $success = $blynkService->enableAutoMode();
        }
        
        return response()->json([
            'success' => $success,
            'new_status' => !$currentStatus
        ]);
    })->name('api.auto-mode.toggle');
});
