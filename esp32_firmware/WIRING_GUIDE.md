# SmartRoom IoT - ESP32 Wiring Guide

## Hardware Components Required

### Microcontroller
- **ESP32 Development Board** (ESP32-WROOM-32 or similar)

### Sensors (Minimum 2)
1. **DHT11 Temperature & Humidity Sensor**
   - Measures: Temperature (°C) and Humidity (%)
   - Interface: Digital single-wire protocol
   
2. **LDR Light Sensor Module**
   - Measures: Light intensity (0-100%)
   - Interface: Analog input

### Actuators (Minimum 2)
1. **LED Module**
   - Controls: Room lighting / visual status indicator
   - Interface: Digital output
   - Voltage: 3.3V-5V DC

2. **Active Buzzer**
   - Controls: Audio alerts
   - Interface: Digital output
   - Voltage: 5V DC

---

## Pin Configuration

### ESP32 Pinout

| Component | ESP32 Pin | Type | Description |
|-----------|-----------|------|-------------|
| DHT11 Data | GPIO 4 | Input | Temperature & Humidity sensor |
| LDR Signal | GPIO 34 | Analog Input | Light sensor (ADC1_CH6) |
| LED + | GPIO 2 | Output | LED control signal |
| Buzzer + | GPIO 15 | Output | Buzzer control signal |

---

## Wiring Diagram

### Power Connections
```
ESP32 Power:
- VIN/3V3 → 3.3V (for ESP32)
- GND → GND (common ground)

External 5V Power (for sensors & actuators):
- 5V → VCC of DHT11, LED, Buzzer
- GND → GND of all components
```

### Sensor Wiring

#### DHT11 Temperature & Humidity Sensor
```
DHT11 Pin 1 (VCC) → 5V
DHT11 Pin 2 (DATA) → GPIO 4 (with 10K pull-up resistor to 5V)
DHT11 Pin 3 (NC) → Not Connected
DHT11 Pin 4 (GND) → GND
```

#### LDR Light Sensor Module
```
LDR Module VCC → 5V
LDR Module GND → GND
LDR Module OUT → GPIO 34 (ADC)
```

### Actuator Wiring

#### LED Module
```
LED VCC → 5V
LED GND → GND
LED IN → GPIO 2
```

#### Active Buzzer
```
Buzzer + → GPIO 15
Buzzer - → GND
```

---

## Complete Circuit Diagram

```
                    ESP32
                  ┌─────────┐
                  │         │
         5V ─────┤ VIN     │
                  │         │
         GND ─────┤ GND     │
                  │         │
DHT11 DATA ──────┤ GPIO 4  │
                  │         │
LDR SIGNAL ──────┤ GPIO 34 │
                  │         │
LED IN ───────────┤ GPIO 2  │
                  │         │
BUZZER + ─────────┤ GPIO 15 │
                  │         │
                  └─────────┘

Sensors:
┌─────────┐     ┌─────────┐
│  DHT11  │     │   LDR   │
└─────────┘     └─────────┘
   │   │            │   │
   │   └────────────┴───┴────────────┐
   │                                      │
   └─────────── To ESP32 Pins ───────────┘

Actuators:
┌─────────┐     ┌─────────┐
│   LED   │     │ BUZZER  │
└─────────┘     └─────────┘
   │   │            │   │
   │   └────────────┴───┴────────────┐
   │                                      │
   └─────────── To ESP32 Pins ───────────┘
```

---

## Component Placement Recommendations

### Breadboard Layout (if using breadboard)
1. **Left side**: Power rails (5V and GND)
2. **Center-left**: ESP32 board
3. **Center-right**: Sensors (DHT11, LDR)
4. **Right side**: Actuators (LED, Buzzer)

### PCB Layout (if designing custom PCB)
1. Place ESP32 in center
2. Place sensors on left side
3. Place actuators on right side
4. Keep power traces short and thick
5. Add decoupling capacitors near each IC
6. Add LED indicators for power and status

---

## Safety Precautions

### General Safety
- Double-check all connections before powering on
- Use appropriate wire gauges for current ratings
- Ensure proper grounding
- Test with low voltage first before connecting to mains
- Use a multimeter to verify connections

---

## Testing Procedure

### 1. Power-Up Test
1. Apply 5V to the circuit
2. Check ESP32 power LED (if available)
3. Monitor Serial Monitor (115200 baud)

### 2. Sensor Test
1. Observe DHT11 readings in Serial Monitor
2. Cover LDR to test light sensor
3. Verify temperature and humidity changes

### 3. Actuator Test
1. Use Laravel admin dashboard to toggle LED
2. Listen for buzzer when activated
3. Verify LED lights up and buzzer sounds

### 4. Integration Test
1. Verify sensor data appears in Laravel database
2. Verify mobile app shows real-time data
3. Test control from mobile app to ESP32 actuators

---

## Troubleshooting

### Common Issues

**DHT11 not reading:**
- Check pull-up resistor (10K between DATA and VCC)
- Verify GPIO 4 connection
- Replace DHT11 if damaged

**LDR always 0 or 4095:**
- Check LDR module connection
- Verify GPIO 34 is ADC-capable
- Test LDR with multimeter

**LED not lighting:**
- Check 5V power to LED
- Verify GPIO 2 connection
- Test LED with direct 5V to IN pin

**Buzzer not sounding:**
- Check GPIO 15 connection
- Verify 5V power to buzzer
- Test buzzer with direct 5V

**WiFi not connecting:**
- Verify SSID and password in code
- Check ESP32 antenna
- Move closer to WiFi router
- Check 2.4GHz vs 5GHz (ESP32 only supports 2.4GHz)

---

## Bill of Materials

| Component | Quantity | Approx. Cost |
|-----------|----------|--------------|
| ESP32 Development Board | 1 | $5-10 |
| DHT11 Sensor | 1 | $2-3 |
| LDR Light Sensor Module | 1 | $1-2 |
| LED Module | 1 | $1-2 |
| Active Buzzer | 1 | $1-2 |
| 10K Resistor | 1 | $0.10 |
| Breadboard | 1 | $3-5 |
| Jumper Wires | 20 | $2-3 |
| USB Cable | 1 | $1-2 |
| **Total** | | **$17-30** |

---

## Next Steps

1. **Upload Firmware**: Use Arduino IDE to upload `smartroom_esp32.ino`
2. **Configure WiFi**: Edit SSID and password in the code
3. **Configure Server**: Update `serverUrl` to your Laravel server IP
4. **Test Integration**: Verify all components work together
5. **Deploy**: Mount components in enclosure for final project

