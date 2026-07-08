# Hikvision DS-K1A802AMF-B Attendance Middleware

Runs on the Windows PC (iVMS-4200 machine) to forward face recognition/access
control events in real-time to the staff portal webhook.

## How It Works

Connects to the device's **ISAPI event stream** (`/ISAPI/Event/notification/alertStream`)
and keeps a persistent HTTP connection open. Every face scan or card swipe at
the door generates an `EventNotificationAlert` XML event, which is parsed and
forwarded as JSON to `https://brickspoint.com/staff/attendance/hikvision-webhook`.

No Hikvision SDK required — uses only built-in .NET Framework HTTP APIs.

## Setup

### 1. Compile the Event Stream Listener

```powershell
& "$env:windir\Microsoft.NET\Framework\v4.0.30319\csc.exe" `
    -target:exe -platform:x86 -out:EventStreamListener.exe EventStreamListener.cs
```

### 2. Configure

Edit `HikvisionConfig.json`:

```json
{
  "DeviceIp":   "192.168.2.3",
  "DevicePort": 80,
  "Username":   "admin",
  "Password":   "your_password",
  "WebhookUrl": "https://brickspoint.com/staff/attendance/hikvision-webhook",
  "LogFile":    "hikvision_middleware.log"
}
```

### 3. Test

```powershell
.\HikvisionMiddleware.ps1 -Mode once
```

### 4. Run Listener (continuous)

```powershell
.\HikvisionMiddleware.ps1 -Mode listen
```

Walk to the device, scan your face or card. Events should appear in the
console and be forwarded to the webhook.

### 5. Schedule as a Windows Service

Use `nssm` or Task Scheduler to keep it running:

- **Task:** Run at startup
- **Action:** `powershell.exe`
- **Arguments:** `-NoProfile -File "C:\path\to\scripts\HikvisionMiddleware.ps1" -Mode listen`
- Check **"Run whether user is logged on or not"**
- Check **"Run with highest privileges"**
- Set **"If the task fails, restart every minute"**

## Files

| File | Purpose |
|------|---------|
| `HikvisionMiddleware.ps1` | Main orchestrator script |
| `EventStreamListener.cs` | C# ISAPI event stream client (no SDK needed) |
| `EventStreamListener.exe` | Compiled binary (from the .cs above) |
| `HikvisionConfig.json` | Device & webhook configuration |
| `hikvision_middleware.log` | Log file |
| `HikvisionSDKHelper.cs` | Legacy SDK-based helper (not recommended) |
