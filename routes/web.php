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
    
    Route::get('/test-email', function () {
        $notificationService = app(\App\Services\NotificationService::class);
        $success = $notificationService->testEmailConfiguration();
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Email de teste enviado com sucesso!' : 'Erro ao enviar email de teste. Verifique as configurações.',
            'email_config' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from' => config('mail.from.address'),
                'notification_email' => config('irrigation.notifications.email'),
                'notifications_enabled' => config('irrigation.notifications.enabled'),
            ]
        ]);
    })->name('api.test-email');
});
