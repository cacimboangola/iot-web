# 🌱 Sistema de Irrigação Inteligente IoT

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-red?style=for-the-badge&logo=laravel" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Livewire-3-blue?style=for-the-badge&logo=livewire" alt="Livewire 3">
  <img src="https://img.shields.io/badge/Bootstrap-5-purple?style=for-the-badge&logo=bootstrap" alt="Bootstrap 5">
  <img src="https://img.shields.io/badge/ESP32-IoT-green?style=for-the-badge&logo=espressif" alt="ESP32">
  <img src="https://img.shields.io/badge/Blynk-Cloud-orange?style=for-the-badge&logo=blynk" alt="Blynk Cloud">
</p>

## 📋 Sobre o Projeto

Sistema web completo para monitoramento e controle de irrigação inteligente baseado em **ESP32**, desenvolvido com **Laravel 11**, **Livewire 3** e **Bootstrap 5**. O sistema integra-se ao **Blynk Cloud** para comunicação em tempo real com sensores de humidade, temperatura, luminosidade e controle de bomba de irrigação.

### 🎯 Funcionalidades Principais

- **Dashboard em Tempo Real**: Monitoramento de todos os sensores com atualização automática
- **Controle Remoto**: Liga/desliga bomba de irrigação via interface web
- **Modo Automático**: Irrigação baseada em limiares configuráveis de humidade
- **Interface Responsiva**: Design moderno com Bootstrap 5 e ícones
- **Configurações Avançadas**: Página dedicada para ajustar parâmetros do sistema
- **API RESTful**: Endpoints para integração com outros sistemas
- **Dados Simulados**: Seeder para desenvolvimento sem hardware físico

### 📊 Sensores Monitorados

| Sensor | Pin Virtual | Descrição | Unidade |
|--------|-------------|-----------|----------|
| 🌱 Humidade do Solo | V0 | Sensor de humidade do solo | % |
| 🌡️ Temperatura do Ar | V1 | Sensor de temperatura ambiente | °C |
| 💧 Humidade do Ar | V2 | Sensor de humidade relativa do ar | % |
| ☀️ Luminosidade | V6 | Sensor LDR de luminosidade | % |
| ⚡ Bomba de Irrigação | V3/V4 | Controle e status da bomba | ON/OFF |
| 🤖 Modo Automático | V5 | Status do modo automático | ON/OFF |

## 🚀 Instalação e Configuração

### Pré-requisitos

- **PHP 8.2+**
- **Composer**
- **Node.js & NPM** (opcional, para assets)
- **Servidor Web** (Apache/Nginx ou built-in do PHP)
- **Conta no Blynk Cloud** com dispositivo ESP32 configurado

### 1️⃣ Clone o Repositório

```bash
git clone <url-do-repositorio>
cd project-iot-mei
```

### 2️⃣ Instale as Dependências

```bash
# Dependências do PHP
composer install

# Dependências do Node.js (opcional)
npm install
```

### 3️⃣ Configure o Ambiente

```bash
# Copie o arquivo de configuração
cp .env.example .env

# Gere a chave da aplicação
php artisan key:generate
```

### 4️⃣ Configure as Variáveis de Ambiente

Edite o arquivo `.env` com suas configurações:

```env
# Configurações da Aplicação
APP_NAME="Sistema de Irrigação IoT"
APP_URL=http://localhost:8000

# Configurações do Blynk Cloud
BLYNK_BASE_URL=https://blynk.cloud/external/api
BLYNK_AUTH_TOKEN=SEU_TOKEN_AQUI

# Configurações do Sistema de Irrigação
IRRIGATION_HUMIDITY_THRESHOLD=30
IRRIGATION_POLLING_INTERVAL=5000

# Banco de Dados (SQLite por padrão)
DB_CONNECTION=sqlite
DB_DATABASE=/caminho/absoluto/para/database.sqlite
```

### 5️⃣ Configure o Banco de Dados

```bash
# Crie o arquivo de banco SQLite
touch database/database.sqlite

# Execute as migrações
php artisan migrate

# (Opcional) Gere dados simulados para teste
php artisan db:seed --class=IrrigationDataSeeder
```

### 6️⃣ Inicie o Servidor

```bash
# Servidor de desenvolvimento
php artisan serve

# Acesse: http://localhost:8000
```

## 🔧 Configuração do Hardware ESP32

### Esquema de Ligação

```
ESP32 Pinout:
├── GPIO 34 → Sensor de Humidade do Solo (Analógico)
├── GPIO 22 → DHT22 (Temperatura e Humidade do Ar)
├── GPIO 35 → LDR (Sensor de Luminosidade)
├── GPIO 2  → Relé da Bomba de Irrigação
└── 3.3V/GND → Alimentação dos sensores
```

### Código Arduino (Exemplo)

```cpp
#define BLYNK_TEMPLATE_ID "SEU_TEMPLATE_ID"
#define BLYNK_DEVICE_NAME "Irrigacao Inteligente"
#define BLYNK_AUTH_TOKEN "SEU_TOKEN_AQUI"

#include <WiFi.h>
#include <BlynkSimpleEsp32.h>
#include <DHT.h>

// Configurações
const char* ssid = "SEU_WIFI";
const char* password = "SUA_SENHA";

// Pinos
#define DHT_PIN 22
#define SOIL_PIN 34
#define LDR_PIN 35
#define PUMP_PIN 2

DHT dht(DHT_PIN, DHT22);

void setup() {
  Serial.begin(115200);
  
  // Configuração dos pinos
  pinMode(PUMP_PIN, OUTPUT);
  
  // Inicialização
  dht.begin();
  Blynk.begin(BLYNK_AUTH_TOKEN, ssid, password);
}

void loop() {
  Blynk.run();
  
  // Leitura dos sensores a cada 2 segundos
  static unsigned long lastTime = 0;
  if (millis() - lastTime > 2000) {
    sendSensorData();
    lastTime = millis();
  }
}

void sendSensorData() {
  // Humidade do solo
  int soilValue = analogRead(SOIL_PIN);
  float soilHumidity = map(soilValue, 0, 4095, 0, 100);
  
  // Temperatura e humidade do ar
  float airTemp = dht.readTemperature();
  float airHumidity = dht.readHumidity();
  
  // Luminosidade
  int ldrValue = analogRead(LDR_PIN);
  float luminosity = map(ldrValue, 0, 4095, 0, 100);
  
  // Status da bomba
  bool pumpStatus = digitalRead(PUMP_PIN);
  
  // Envio para Blynk
  Blynk.virtualWrite(V0, soilHumidity);
  Blynk.virtualWrite(V1, airTemp);
  Blynk.virtualWrite(V2, airHumidity);
  Blynk.virtualWrite(V4, pumpStatus);
  Blynk.virtualWrite(V6, luminosity);
}

// Controle da bomba via Blynk
BLYNK_WRITE(V3) {
  int value = param.asInt();
  digitalWrite(PUMP_PIN, value);
}
```

## 🎮 Como Usar

### Dashboard Principal

1. **Acesse** `http://localhost:8000`
2. **Visualize** os dados dos sensores em tempo real
3. **Controle** a bomba manualmente com os botões
4. **Configure** o modo automático e limiares

### Página de Configurações

1. **Acesse** `http://localhost:8000/settings`
2. **Ajuste** o limiar de humidade (10-90%)
3. **Configure** o intervalo de atualização (1-60s)
4. **Teste** a conexão com o Blynk Cloud
5. **Salve** as configurações

### API Endpoints

```bash
# Obter dados dos sensores
GET /api/sensors

# Alternar bomba
POST /api/pump/toggle

# Alternar modo automático
POST /api/auto-mode/toggle
```

## 🛠️ Desenvolvimento

### Estrutura do Projeto

```
project-iot-mei/
├── app/
│   ├── Livewire/           # Componentes Livewire
│   │   ├── IrrigationDashboard.php
│   │   ├── SensorCard.php
│   │   ├── PumpControl.php
│   │   ├── AutoModeControl.php
│   │   └── SettingsPage.php
│   └── Services/
│       └── BlynkService.php # Serviço para API Blynk
├── config/
│   ├── irrigation.php      # Configurações do sistema
│   └── services.php        # Configurações de serviços
├── database/
│   └── seeders/
│       └── IrrigationDataSeeder.php
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php   # Layout principal
│   └── livewire/           # Views dos componentes
└── routes/
    └── web.php             # Rotas da aplicação
```

### Comandos Úteis

```bash
# Gerar dados simulados
php artisan db:seed --class=IrrigationDataSeeder

# Limpar cache
php artisan config:clear
php artisan cache:clear

# Executar testes
php artisan test

# Verificar código (PSR-12)
./vendor/bin/pint
```

## 🔍 Troubleshooting

### Problemas Comuns

**1. Erro de conexão com Blynk**
- Verifique o token de autenticação no `.env`
- Confirme se o dispositivo ESP32 está online
- Teste a conexão na página de configurações

**2. Dados não atualizando**
- Verifique se o polling automático está funcionando
- Confirme o intervalo de atualização nas configurações
- Verifique o console do navegador para erros JavaScript

**3. Interface não carregando**
- Execute `composer install` e `php artisan key:generate`
- Verifique se todas as dependências estão instaladas
- Confirme as permissões de arquivo

### Logs e Debug

```bash
# Visualizar logs
tail -f storage/logs/laravel.log

# Modo debug
# No .env: APP_DEBUG=true
```

## 🤝 Contribuição

Contribuições são bem-vindas! Para contribuir:

1. **Fork** o projeto
2. **Crie** uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. **Commit** suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. **Push** para a branch (`git push origin feature/AmazingFeature`)
5. **Abra** um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👨‍💻 Autor

Desenvolvido com ❤️ para sistemas de irrigação inteligente.

---

**🌱 Cultive o futuro com tecnologia! 🌱**
