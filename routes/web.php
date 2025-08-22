<?php

use App\Livewire\IrrigationDashboard;
use App\Livewire\SettingsPage;
use Illuminate\Support\Facades\Route;

/** Rota principal - Dashboard do sistema de irrigação */
Route::get('/', IrrigationDashboard::class)->name('dashboard');

/** Página de configurações */
Route::get('/settings', SettingsPage::class)->name('settings');

/** API Routes para integração externa (opcional) */
Route::prefix('api')->group(function () {
    Route::get('/sensors', function () {
        $blynkService = app(\App\Services\BlynkService::class);
        dd($blynkService->getAllSensorData());
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

    Route::post('/fan/toggle', function () {
        $blynkService = app(\App\Services\BlynkService::class);
        $currentStatus = $blynkService->getFanStatus();

        if ($currentStatus) {
            $success = $blynkService->turnFanOff();
        } else {
            $success = $blynkService->turnFanOn();
        }

        return response()->json([
            'success' => $success,
            'new_status' => !$currentStatus
        ]);
    })->name('api.fan.toggle');

    Route::post('/valve/toggle', function () {
        $blynkService = app(\App\Services\BlynkService::class);
        $currentStatus = $blynkService->getSolenoidValveStatus();

        if ($currentStatus) {
            $success = $blynkService->closeSolenoidValve();
        } else {
            $success = $blynkService->openSolenoidValve();
        }

        return response()->json([
            'success' => $success,
            'new_status' => !$currentStatus
        ]);
    })->name('api.valve.toggle');
});
