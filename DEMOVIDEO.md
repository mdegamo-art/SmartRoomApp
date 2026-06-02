# Smart Room IoT — Demo Video Script (Max 5 Minutes)

**Purpose:** Fast, clear defense/demo script aligned with instructor requirements.  
**Target length:** 4:30 to 5:00 minutes  
**Project:** ESP32 + Laravel 12 + MySQL + React Native (Expo)

---

## 1) Pre-recording checklist (30 seconds before recording)

- Laravel server is running (`php artisan serve --host=0.0.0.0 --port=8000`)
- Database migrated (including `system_events`) (`php artisan migrate`)
- ESP32 is powered and connected to Wi-Fi
- Phone and laptop are on the same network
- Mobile app `Profile -> System -> Server URL` is set correctly
- Same `device_id` is used across firmware, backend, and mobile link (ex: `SMARTROOM-001`)

---

## 2) Demo flow with narration (max 5 minutes)

### 0:00 - 0:30 — Project intro

**Say:**

> Good day. This is my Semestral Full-Stack IoT project, Smart Room IoT.  
> Hardware is ESP32 with DHT11 and LDR sensors, plus LED and buzzer actuators.  
> Backend is Laravel 12 with MySQL and REST JSON APIs.  
> Client interfaces are web admin dashboard and React Native mobile app.  
> The architecture is centralized: mobile and web send commands to Laravel, Laravel updates database, and ESP32 polls actuator states from server.

---

### 0:30 - 1:15 — Web authentication and session protection

**Action:** Open web login and sign in.  
**Say:**

> Here is the protected web admin login using username and password.  
> After login, access is granted to dashboard features only for authenticated sessions.  
> At the top, this inactivity countdown enforces automatic logout when idle to protect active hardware control panels.

---

### 1:15 - 2:05 — Real-time dashboard status and telemetry

**Action:** Show dashboard cards and online/offline indicator.  
**Say:**

> This dashboard shows the latest telemetry values: temperature, humidity, and light intensity.  
> It also shows the real-time device status indicator.  
> If ESP32 stops posting within the stale threshold, the dashboard reads offline.  
> This satisfies real-time ecosystem monitoring with connection health visibility.

---

### 2:05 - 2:55 — Web manual override to physical hardware

**Action:** Toggle LED and buzzer from web actuator controls; show hardware reaction on camera.  
**Say:**

> Now I am using manual web overrides.  
> I toggle LED from dashboard, and the physical LED changes state.  
> I toggle buzzer from dashboard, and the physical buzzer reacts.  
> This writes actuator states to the database instantly, then ESP32 executes through server polling.

---

### 2:55 - 3:40 — Mobile runtime server config + live monitoring

**Action:** Open app, go to `Profile -> System`, show Server URL field, then dashboard.  
**Say:**

> On mobile, server target is configurable at runtime in Profile System.  
> The server URL is saved locally and reused after restart.  
> On dashboard tab, sensor values are mirrored from backend with automated refresh cycles every 3 seconds.

---

### 3:40 - 4:35 — Mobile controls, touch-lock, and sync

**Action:** Open Controls tab, toggle actuator, attempt rapid repeated taps while request is in flight, then change state from web and show mobile re-align.  
**Say:**

> Here are mobile actuator switches for remote control.  
> While a network update is in-flight, touch-lock buffering disables overlapping presses, preventing duplicate API traffic.  
> If I change actuator state from web admin, mobile toggles automatically re-align on next polling cycle.  
> This demonstrates bi-directional state synchronization.

---

### 4:35 - 5:00 — Logs and closing

**Action:** Open `/dashboard/logs`, show telemetry history table and system events audit table.  
**Say:**

> Finally, this page shows chronological telemetry history and system modification audit trail.  
> So the project meets hardware, backend, dashboard, and mobile requirements with centralized control and full end-to-end operation.  
> Thank you.

---

## 3) What to keep visible on camera

- At least one shot with **phone + hardware + laptop** in frame during actuator toggles
- Device ID shown at least once (ex: `SMARTROOM-001`)
- Logs page showing both:
  - telemetry rows
  - system modification audit rows

---

## 4) Fast fallback plan (if one part fails during recording)

- If hardware momentarily disconnects:
  - Show offline indicator behavior as proof of monitoring logic
  - Reconnect and continue with actuator demo
- If mobile cannot reach server:
  - Reopen `Profile -> System`, re-enter server URL, save, and continue

---

*Use this as your speaking script while recording a single clean demo take.*
