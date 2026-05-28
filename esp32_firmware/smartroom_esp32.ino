/*
 * SmartRoom IoT ESP32 Firmware
 * 
 * Hardware Requirements:
 * - ESP32 Development Board
 * - DHT11 Temperature & Humidity Sensor
 * - LDR Light Sensor Module
 * - LED Module (for room lighting)
 * - Active Buzzer Module (for alerts)
 * 
 * Pin Configuration:
 * - DHT11: GPIO 4
 * - LDR: GPIO 34 (ADC1_CH6)
 * - LED: GPIO 2
 * - Buzzer: GPIO 15
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <DHT.h>
#include <ArduinoJson.h>

// WiFi Configuration
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// Server Configuration
const char* serverUrl = "http://192.168.1.13:8000/api";

// Sensor Pin Definitions
#define DHT_PIN 4
#define LDR_PIN 34

// Actuator Pin Definitions
#define LED_PIN 2
#define BUZZER_PIN 15

// Sensor Configuration
#define DHT_TYPE DHT11
DHT dht(DHT_PIN, DHT_TYPE);

// Timing Variables
unsigned long lastSensorRead = 0;
unsigned long lastActuatorPoll = 0;
const unsigned long sensorInterval = 5000;  // Send sensor data every 5 seconds
const unsigned long actuatorInterval = 1500; // Poll actuators every 1.5 seconds

// Previous actuator states for change detection
bool previousLedState = false;
bool previousBuzzerState = false;

void setup() {
  Serial.begin(115200);
  
  // Initialize pins
  pinMode(DHT_PIN, INPUT);
  pinMode(LDR_PIN, INPUT);
  pinMode(LED_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  
  // Initialize actuators to OFF
  digitalWrite(LED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);
  
  // Initialize DHT sensor
  dht.begin();
  
  // Connect to WiFi
  connectToWiFi();
  
  Serial.println("SmartRoom IoT System Initialized");
}

void loop() {
  unsigned long currentTime = millis();
  
  // Check WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi disconnected. Reconnecting...");
    connectToWiFi();
  }
  
  // Send sensor data every 5 seconds
  if (currentTime - lastSensorRead >= sensorInterval) {
    lastSensorRead = currentTime;
    sendSensorData();
  }
  
  // Poll actuator states every 1.5 seconds
  if (currentTime - lastActuatorPoll >= actuatorInterval) {
    lastActuatorPoll = currentTime;
    pollActuatorStates();
  }
  
  delay(100);
}

void connectToWiFi() {
  Serial.print("Connecting to WiFi");
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println();
  Serial.println("WiFi connected!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
}

void sendSensorData() {
  // Read DHT11 (Temperature & Humidity)
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();
  
  // Read LDR (Light Level)
  int ldrValue = analogRead(LDR_PIN);
  int lightLevel = map(ldrValue, 0, 4095, 0, 100); // Convert to percentage (integer)
  
  // Check for sensor read errors
  if (isnan(temperature) || isnan(humidity)) {
    Serial.println("Failed to read from DHT sensor!");
    return;
  }
  
  // Create JSON payload
  StaticJsonDocument<256> doc;
  doc["temperature"] = temperature;
  doc["humidity"] = humidity;
  doc["light_level"] = lightLevel;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  // Send to server
  HTTPClient http;
  String url = String(serverUrl) + "/sensor-data";
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  
  int httpResponseCode = http.POST(jsonString);
  
  if (httpResponseCode > 0) {
    Serial.print("Sensor data sent. Response code: ");
    Serial.println(httpResponseCode);
    Serial.print("Payload: ");
    Serial.println(jsonString);
  } else {
    Serial.print("Error sending sensor data: ");
    Serial.println(httpResponseCode);
  }
  
  http.end();
}

void pollActuatorStates() {
  HTTPClient http;
  String url = String(serverUrl) + "/actuator-status";
  http.begin(url);
  
  int httpResponseCode = http.GET();
  
  if (httpResponseCode == 200) {
    String response = http.getString();
    
    // Parse JSON response
    StaticJsonDocument<256> doc;
    DeserializationError error = deserializeJson(doc, response);
    
    if (!error) {
      bool ledState = doc["led_state"];
      bool buzzerState = doc["buzzer_state"];
      
      // Update LED if state changed
      if (ledState != previousLedState) {
        digitalWrite(LED_PIN, ledState ? HIGH : LOW);
        previousLedState = ledState;
        Serial.print("LED state changed to: ");
        Serial.println(ledState ? "ON" : "OFF");
      }
      
      // Update buzzer if state changed
      if (buzzerState != previousBuzzerState) {
        digitalWrite(BUZZER_PIN, buzzerState ? HIGH : LOW);
        previousBuzzerState = buzzerState;
        Serial.print("Buzzer state changed to: ");
        Serial.println(buzzerState ? "ON" : "OFF");
      }
      
      // Log current states
      Serial.println("Actuator states updated");
      Serial.print("LED: ");
      Serial.println(ledState ? "ON" : "OFF");
      Serial.print("Buzzer: ");
      Serial.println(buzzerState ? "ON" : "OFF");
      
    } else {
      Serial.print("JSON parsing error: ");
      Serial.println(error.c_str());
    }
  } else {
    Serial.print("Error polling actuator states: ");
    Serial.println(httpResponseCode);
  }
  
  http.end();
}
