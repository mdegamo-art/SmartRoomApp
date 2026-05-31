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

// WiFi Configuration
const char* ssid = "Redmi A3";
const char* password = "12345678";

// Server Configuration
const char* serverUrl = "http://10.182.231.239:8000/api";

// Sensor Pin Definitions
#define DHT_PIN 4
#define DHT_TYPE DHT11
#define LDR_PIN 34

// DHT Sensor Object
DHT dht(DHT_PIN, DHT_TYPE);

// Actuator Pin Definitions
#define LED_PIN 2
#define BUZZER_PIN 15

// Timing Variables
unsigned long lastSensorRead = 0;
unsigned long lastActuatorPoll = 0;
const unsigned long sensorInterval = 5000;  // Send sensor data every 5 seconds
const unsigned long actuatorInterval = 1500; // Poll actuators every 1.5 seconds

// Previous actuator states for change detection
bool previousLedState = false;
bool previousBuzzerState = false;

// DHT11 Variables
float temperature = 0;
float humidity = 0;

void setup() {
  Serial.begin(115200);
  delay(2000); // Wait for serial to stabilize

  Serial.println("Initializing DHT11...");

  // Initialize DHT sensor
  dht.begin();
  delay(2000); // Give DHT11 time to stabilize after power-up

  // Initialize pins
  pinMode(LDR_PIN, INPUT);
  pinMode(LED_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  
  // Initialize actuators to OFF
  digitalWrite(LED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);
  
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
    readDHT11();
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

// Read DHT11 using library
void readDHT11() {
  // DHT11 needs at least 1 second between readings
  delay(1000);

  float h = dht.readHumidity();
  float t = dht.readTemperature();

  // Debug raw values
  Serial.print("Raw T: "); Serial.println(t);
  Serial.print("Raw H: "); Serial.println(h);

  if (isnan(h) || isnan(t)) {
    Serial.println("DHT11 read failed! Check wiring.");
    Serial.println("→ Make sure VCC is on VIN (5V), not 3V3");
    Serial.println("→ Make sure DATA is on GPIO 4");
    return;
  }

  humidity = h;
  temperature = t;

  Serial.print("Temperature: ");
  Serial.print(temperature);
  Serial.print(" °C | Humidity: ");
  Serial.print(humidity);
  Serial.println(" %");
}

void sendSensorData() {
  // Read LDR (Light Level)
  int ldrValue = analogRead(LDR_PIN);
  int lightLevel = map(ldrValue, 0, 4095, 0, 100); // Convert to percentage (integer)
  
  // Create JSON payload manually
  String jsonString = "{\"temperature\":" + String(temperature) + 
                      ",\"humidity\":" + String(humidity) + 
                      ",\"light_level\":" + String(lightLevel) + "}";
  
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
    
    // Parse JSON manually
    bool ledState = false;
    bool buzzerState = false;
    
    // Parse led
    int ledIndex = response.indexOf("\"led\":");
    if (ledIndex != -1) {
      int valueStart = ledIndex + 6;
      int valueEnd = response.indexOf(",", valueStart);
      if (valueEnd == -1) valueEnd = response.indexOf("}", valueStart);
      String ledValue = response.substring(valueStart, valueEnd);
      ledState = (ledValue == "true" || ledValue == "1");
    }
    
    // Parse buzzer
    int buzzerIndex = response.indexOf("\"buzzer\":");
    if (buzzerIndex != -1) {
      int valueStart = buzzerIndex + 9;
      int valueEnd = response.indexOf(",", valueStart);
      if (valueEnd == -1) valueEnd = response.indexOf("}", valueStart);
      String buzzerValue = response.substring(valueStart, valueEnd);
      buzzerState = (buzzerValue == "true" || buzzerValue == "1");
    }
    
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
    Serial.print("Error polling actuator states: ");
    Serial.println(httpResponseCode);
  }
  
  http.end();
}
