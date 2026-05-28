# SmartRoom IoT - ESP32 Setup Guide

## Final Equipment List

### Main Controller
- **ESP32 DevKit V1** (1 unit)
  - Main microcontroller
  - Connects to WiFi
  - Reads sensors
  - Controls actuators

### Sensors (Input Devices)
- **DHT11 Temperature and Humidity Sensor** (1 unit)
  - Detects room temperature
  - Detects humidity level

- **LDR Light Sensor Module** (1 unit)
  - Detects room brightness/light intensity

### Actuators (Output Devices)
- **LED Module** (1 unit)
  - Simulates room lighting
  - Visual status indicator
  - Controlled through: Mobile app, Admin dashboard, Optional automation

- **Active Buzzer Module** (1 unit)
  - Alarm/warning notification
  - Temperature alert sound
  - Controlled through: Mobile app, Admin dashboard, Automatic alert system

### Supporting Components
- **Breadboard** (1 unit)
  - Organizes circuit connections
  - Allows solderless prototyping

- **Jumper Wires** (1 set)
  - Connect components to ESP32
  - Types: Male-to-male, Male-to-female

- **USB Cable for ESP32** (1 unit)
  - Programming
  - Power supply

- **WiFi Router or Mobile Hotspot** (1 unit)
  - Internet/network communication

---

## System Flow

### STEP 1 — Sensor Data Collection
The sensors collect room environmental data.

**DHT11 Sensor** reads:
- Temperature
- Humidity

**LDR Sensor** reads:
- Light intensity

### STEP 2 — ESP32 Processes Sensor Data
ESP32 receives sensor readings and prepares the data.

Example JSON payload:
```json
{
  "temperature": 30,
  "humidity": 70,
  "light_level": 75
}
```

### STEP 3 — ESP32 Sends Data to Laravel API
ESP32 connects to WiFi and sends data using REST API.

**API Endpoint:** `POST /api/sensor-data`

### STEP 4 — Laravel Stores Data in Database
Laravel receives sensor data and stores it in MySQL.

**Database Table:** `telemetry_logs`

Stored values:
- Temperature
- Humidity
- Light level
- Timestamp

### STEP 5 — Dashboard and Mobile App Display Live Data
The system displays:
- Temperature
- Humidity
- Brightness
- Device online status

Users can monitor the room remotely.

### STEP 6 — User Sends Actuator Commands
User controls devices using:
- Mobile application
- Admin dashboard

Commands:
- LED ON/OFF
- Buzzer ON/OFF

### STEP 7 — Laravel Updates Actuator States
Laravel stores actuator states inside database.

**Database Table:** `actuator_states`

Example:
| actuator_name | state |
| ------------- | ----- |
| led           | 1     |
| buzzer        | 0     |

### STEP 8 — ESP32 Polls Server for Commands
ESP32 checks the server every 1–2 seconds.

**API Endpoint:** `GET /api/actuator-status`

### STEP 9 — ESP32 Controls Physical Devices
ESP32 executes the commands:

**LED:** Turns ON/OFF

**Buzzer:** Activates/deactivates sound alarm

This becomes the actual IoT interaction.

---

## Optional Automation Flow

### Automatic Alert System

**Condition:** If temperature > 35°C

**Action:** Automatically activate buzzer

**Purpose:** High temperature warning

---

## Complete System Architecture Flow

```
[DHT11 Sensor] + [LDR Sensor]
                │
                ▼
          [ESP32 DevKit]
                │
         WiFi Communication
                │
                ▼
           [Laravel API]
                │
          [MySQL Database]
                │
      ┌─────────┴─────────┐
      ▼                   ▼
[Admin Dashboard]   [Mobile App]
      │                   │
      └── Device Commands ┘
                │
                ▼
          [Laravel API]
                │
                ▼
             [ESP32]
                │
       ┌────────┴────────┐
       ▼                 ▼
   [LED Module]    [Buzzer Module]
```

---

## Final Project Output

The finished system will be able to:

✅ Monitor room temperature
✅ Monitor humidity
✅ Detect room brightness
✅ Store sensor history
✅ Remotely control LED
✅ Remotely control buzzer
✅ Display live data on mobile app
✅ Display live data on admin dashboard
✅ Trigger warning alarms
✅ Demonstrate complete IoT architecture

---

## Final Project Summary

The project is an IoT-based room monitoring and control system that uses ESP32 sensors and actuators connected to a Laravel REST API backend with React Native mobile integration. The system enables real-time environmental monitoring and remote device control through WiFi communication.

---

## Quick Start

### Prerequisites
- Arduino IDE 2.x installed
- ESP32 Board Support Package installed
- Required Arduino Libraries:
  - WiFi (built-in)
  - HTTPClient (built-in)
  - ArduinoJson (install via Library Manager)
  - DHT sensor library (install via Library Manager)

### Installation Steps

1. **Install Arduino IDE**
   - Download from: https://www.arduino.cc/en/software
   - Install and launch Arduino IDE

2. **Install ESP32 Board Support**
   - Open Arduino IDE
   - Go to File → Preferences
   - Add this URL to "Additional Board Manager URLs":
     ```
     https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
     ```
   - Go to Tools → Board → Boards Manager
   - Search for "ESP32" and install "esp32 by Espressif Systems"

3. **Install Required Libraries**
   - Go to Tools → Manage Libraries
   - Search and install:
     - "ArduinoJson" by Benoit Blanchon
     - "DHT sensor library" by Adafruit

4. **Upload Firmware**
   - Open `smartroom_esp32.ino` in Arduino IDE
   - Select your ESP32 board: Tools → Board → ESP32 Dev Module
   - Select COM port: Tools → Port → (your ESP32 port)
   - Edit WiFi credentials in the code:
     ```cpp
     const char* ssid = "YOUR_WIFI_SSID";
     const char* password = "YOUR_WIFI_PASSWORD";
     ```
   - Edit server URL if needed:
     ```cpp
     const char* serverUrl = "http://192.168.1.13:8000/api";
     ```
   - Click Upload button (→)

5. **Monitor Serial Output**
   - Open Serial Monitor (magnifying glass icon)
   - Set baud rate to 115200
   - You should see connection messages and sensor data

---

## Configuration

### WiFi Settings
Edit these lines in `smartroom_esp32.ino`:
```cpp
const char* ssid = "YOUR_WIFI_SSID";        // Your WiFi network name
const char* password = "YOUR_WIFI_PASSWORD"; // Your WiFi password
```

### Server Settings
Edit the server URL to match your Laravel server:
```cpp
const char* serverUrl = "http://192.168.1.13:8000/api";
```

Replace `192.168.1.13` with your actual Laravel server IP address.

### Timing Settings
Adjust these intervals as needed:
```cpp
const unsigned long sensorInterval = 5000;  // Send sensor data every 5 seconds
const unsigned long actuatorInterval = 1500; // Poll actuators every 1.5 seconds
```

---

## API Integration

### Endpoints Used by ESP32

#### 1. Send Sensor Data
- **Endpoint**: `POST /api/sensor-data`
- **Payload**:
  ```json
  {
    "temperature": 25.5,
    "humidity": 60.0,
    "light_level": 75.0,
    "motion_detected": 1
  }
  ```
- **Response**: HTTP 200 OK

#### 2. Get Actuator States
- **Endpoint**: `GET /api/actuator-status`
- **Response**:
  ```json
  {
    "relay_state": true,
    "buzzer_state": false
  }
  ```

---

## Troubleshooting

### ESP32 Won't Connect to WiFi
- Verify SSID and password are correct
- Check if router is 2.4GHz (ESP32 doesn't support 5GHz)
- Move ESP32 closer to router
- Restart ESP32

### Sensor Data Not Reaching Server
- Check server is running: `php artisan serve`
- Verify server IP is correct
- Check firewall settings
- Test endpoint with curl: `curl -X POST http://192.168.1.13:8000/api/sensor-data`

### Actuators Not Responding
- Check wiring connections
- Verify actuator endpoint returns correct data
- Test with Laravel admin dashboard
- Check GPIO pin assignments

### DHT11 Reading NaN
- Check pull-up resistor (10K between DATA and VCC)
- Verify GPIO 4 connection
- Replace DHT11 if damaged

### LDR Always 0 or 4095
- Check voltage divider circuit
- Verify GPIO 34 connection
- Test LDR with multimeter

---

## Testing

### Manual Testing

1. **Test WiFi Connection**
   - Open Serial Monitor
   - Should see "WiFi connected!" message
   - Note the IP address

2. **Test Sensors**
   - Cover LDR with hand → light_level should decrease
   - Warm DHT11 with breath → temperature should increase
   - Wave hand in front of PIR → motion_detected should be 1

3. **Test Actuators**
   - Use Laravel admin dashboard to toggle relay
   - ESP32 should receive new state and update GPIO
   - Relay should click and LED should light up

4. **Test Full Integration**
   - Open React Native mobile app
   - Toggle controls from app
   - Verify ESP32 receives updates
   - Verify physical actuators respond

---

## Advanced Configuration

### Adding More Sensors

To add additional sensors:

1. **Define new pin**:
   ```cpp
   #define NEW_SENSOR_PIN 12
   pinMode(NEW_SENSOR_PIN, INPUT);
   ```

2. **Read sensor in loop**:
   ```cpp
   int newValue = analogRead(NEW_SENSOR_PIN);
   ```

3. **Add to JSON payload**:
   ```cpp
   doc["new_sensor"] = newValue;
   ```

4. **Update Laravel database schema** to store new data

### Adding More Actuators

To add additional actuators:

1. **Define new pin**:
   ```cpp
   #define NEW_ACTUATOR_PIN 13
   pinMode(NEW_ACTUATOR_PIN, OUTPUT);
   ```

2. **Update actuator polling**:
   ```cpp
   bool newActuatorState = doc["new_actuator_state"];
   digitalWrite(NEW_ACTUATOR_PIN, newActuatorState ? HIGH : LOW);
   ```

3. **Update Laravel actuator controller** to include new actuator

---

## Security Considerations

### Production Deployment

1. **Use HTTPS** (if available):
   ```cpp
   const char* serverUrl = "https://your-server.com/api";
   ```

2. **Add API Key** (implement in Laravel):
   ```cpp
   http.addHeader("X-API-Key", "your-secret-key");
   ```

3. **Use Static IP** for ESP32 (optional):
   ```cpp
   IPAddress local_IP(192, 168, 1, 100);
   IPAddress gateway(192, 168, 1, 1);
   IPAddress subnet(255, 255, 255, 0);
   WiFi.config(local_IP, gateway, subnet);
   ```

---

## Performance Optimization

### Reduce Power Consumption
- Add deep sleep mode between readings
- Use lower clock speed if not needed
- Disable unused peripherals

### Improve Reliability
- Add watchdog timer
- Implement retry logic for failed requests
- Add error recovery mechanisms

---

## Support

For issues or questions:
1. Check the wiring guide: `WIRING_GUIDE.md`
2. Review Laravel API documentation
3. Check Serial Monitor for error messages
4. Verify all connections with multimeter

---

## License

This firmware is part of the SmartRoom IoT project for educational purposes.
