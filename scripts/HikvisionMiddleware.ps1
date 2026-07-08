<#
.SYNOPSIS
    Hikvision Face Terminal (DS-K1A802AMF-B) Attendance Middleware for Windows
.DESCRIPTION
    Connects to the device's ISAPI event stream for real-time face/access events.
    Forwards events to the staff portal webhook.

    Schedule this script via Windows Task Scheduler as a startup task.
.PARAMETER Mode
    'listen' - Connect to ISAPI event stream (continuous, recommended)
    'once'   - Single test cycle (ISAPI poll, for debugging)
.PARAMETER ConfigFile
    Path to JSON config file (default: HikvisionConfig.json in same directory)
#>

param(
    [ValidateSet('listen', 'once')]
    [string]$Mode = 'listen',
    [string]$ConfigFile = ''
)

# Ensure curl.exe is available
if (-not (Get-Command "curl.exe" -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: curl.exe not found. This script requires curl.exe (Windows 10/Server 2016+)." -ForegroundColor Red
    exit 1
}

# --- Configuration ---
if (-not $ConfigFile) {
    $ConfigFile = Join-Path -Path $PSScriptRoot -ChildPath 'HikvisionConfig.json'
}

if (Test-Path -LiteralPath $ConfigFile) {
    $config = Get-Content -LiteralPath $ConfigFile -Raw | ConvertFrom-Json
} else {
    $config = @{
        DeviceIp       = ''
        DevicePort     = 80
        Username       = 'admin'
        Password       = ''
        WebhookUrl     = 'https://brickspoint.com/staff/attendance/hikvision-webhook'
        StreamExe      = Join-Path -Path $PSScriptRoot -ChildPath 'EventStreamListener.exe'
        LogFile        = Join-Path -Path $PSScriptRoot -ChildPath 'hikvision_middleware.log'
    }
    # Save default config
    $config | ConvertTo-Json | Set-Content -LiteralPath $ConfigFile -Encoding UTF8
    Write-Host "Default config saved to $ConfigFile. Please edit it with your device details, then re-run."
    exit 0
}

function Write-Log {
    param([string]$Message, [string]$Level = 'INFO')
    $timestamp = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    $line = "[$timestamp] [$Level] $Message"
    Add-Content -LiteralPath $config.LogFile -Value $line -Encoding UTF8
    if ($Level -in 'ERROR', 'WARN') { Write-Warning $line }
    else { Write-Host $line }
}

function Invoke-ISAPI {
    param([string]$Endpoint, [string]$Method = 'GET', [string]$Body = '', [string]$ContentType = 'application/xml')

    $url = "http://$($config.DeviceIp):$($config.DevicePort)$Endpoint"
    $tmpFile = [System.IO.Path]::GetTempFileName()

    $curlArgs = @('-s', '-S', '-w', '%{http_code}', '-o', $tmpFile, '--max-time', '20')
    $curlArgs += '--digest', '-u', "$($config.Username):$($config.Password)"

    $bodyFile = $null
    if ($Method -eq 'POST') {
        $curlArgs += '-X', 'POST'
        if ($Body) {
            $bodyFile = [System.IO.Path]::GetTempFileName()
            [System.IO.File]::WriteAllText($bodyFile, $Body, [System.Text.Encoding]::UTF8)
            $curlArgs += '--data-binary', "@$bodyFile"
            $curlArgs += '-H', "Content-Type: $ContentType"
        }
    }

    $curlArgs += $url

    try {
        $statusCode = & "curl.exe" @curlArgs 2>$null
        $content = [System.IO.File]::ReadAllText($tmpFile)
        if ($statusCode -match '^\d+$') { $statusCode = [int]$statusCode }
        return @{ StatusCode = $statusCode; Content = $content }
    } catch {
        return @{ StatusCode = $null; Content = $_.Exception.Message }
    } finally {
        if (Test-Path -LiteralPath $tmpFile -PathType Leaf) { Remove-Item -LiteralPath $tmpFile -Force }
        if ($bodyFile -and (Test-Path -LiteralPath $bodyFile -PathType Leaf)) { Remove-Item -LiteralPath $bodyFile -Force }
    }
}

function Invoke-TestConnection {
    $result = Invoke-ISAPI -Endpoint '/ISAPI/System/deviceInfo'
    if ($result.StatusCode -eq 200) {
        Write-Log "Device connection OK (HTTP 200)"
        return $true
    } else {
        Write-Log "Device connection FAILED (HTTP $($result.StatusCode)): $($result.Content)" -Level 'ERROR'
        return $false
    }
}

function Invoke-AcsSearch {
    # Attempt ACS event search with most promising XML format
    $searchId = [Guid]::NewGuid().ToString()
    $fromDate = (Get-Date).AddDays(-7).ToString('yyyy-MM-ddTHH:mm:ss')
    $toDate = (Get-Date).ToString('yyyy-MM-ddTHH:mm:ss')

    $xml = @"
<?xml version="1.0" encoding="UTF-8"?>
<AcsEventSearch xmlns="http://www.isapi.org/ver20/XMLSchema">
<searchID>$searchId</searchID>
<searchResultPosition>0</searchResultPosition>
<maxResults>100</maxResults>
<AcsEventCondition>
<startTime>$fromDate</startTime>
<endTime>$toDate</endTime>
</AcsEventCondition>
</AcsEventSearch>
"@

    Write-Log "Searching ACS events from $fromDate to $toDate"
    $result = Invoke-ISAPI -Endpoint '/ISAPI/AccessControl/AcsEvent/Search' -Method 'POST' -Body $xml
    Write-Log "ACS Search response: HTTP $($result.StatusCode) - $($result.Content.Substring(0, [Math]::Min(300, $result.Content.Length)))"

    if ($result.StatusCode -eq 200) {
        return $result.Content
    }
    return $null
}

function Invoke-Webhook {
    param([string]$Payload)

    if (-not $Payload -or $Payload.Trim() -eq '') { return }

    try {
        $body = @{ records = $Payload } | ConvertTo-Json
        $response = Invoke-WebRequest -Uri $config.WebhookUrl -Method 'POST' `
            -Headers @{ 'Content-Type' = 'application/json' } `
            -Body $body `
            -UseBasicParsing -TimeoutSec 30
        Write-Log "Webhook forwarded: HTTP $($response.StatusCode)"
    } catch {
        Write-Log "Webhook failed: $_" -Level 'ERROR'
    }
}

function Invoke-PollCycle {
    Write-Log "=== Poll cycle started ==="

    if (-not (Invoke-TestConnection)) {
        Write-Log "Device unreachable, skipping poll" -Level 'WARN'
        return
    }

    # Try ACS event search
    $events = Invoke-AcsSearch
    if ($events) {
        Invoke-Webhook -Payload $events
    } else {
        Write-Log "No events returned from ACS search (expected for this device model)"
    }

    # Also try attendance search (in case device supports it via different path)
    $altXml = @"
<?xml version="1.0" encoding="UTF-8"?>
<AttendanceRecordSearch version="2.0" xmlns="http://www.isapi.org/ver20/XMLSchema">
<searchResultPosition>0</searchResultPosition>
<maxResults>100</maxResults>
<AttendanceRecordCondition>
<startTime>$((Get-Date).AddDays(-1).ToString('yyyy-MM-ddTHH:mm:ss'))</startTime>
<endTime>$((Get-Date).ToString('yyyy-MM-ddTHH:mm:ss'))</endTime>
</AttendanceRecordCondition>
</AttendanceRecordSearch>
"@
    $attResult = Invoke-ISAPI -Endpoint '/ISAPI/AttendanceRecord/Search' -Method 'POST' -Body $altXml
    if ($attResult.StatusCode -eq 200) {
        Write-Log "Attendance search returned data! HTTP 200"
        Invoke-Webhook -Payload $attResult.Content
    }

    Write-Log "=== Poll cycle finished ==="
}

function Start-EventStreamListener {
    $exePath = $config.StreamExe
    if (-not (Test-Path -LiteralPath $exePath)) {
        Write-Log "EventStreamListener.exe not found at $exePath" -Level 'ERROR'
        Write-Log "Compile it: csc.exe -target:exe -platform:x86 -out:EventStreamListener.exe EventStreamListener.cs" -Level 'ERROR'
        return
    }

    Write-Log "Starting EventStreamListener (continuous mode)..."
    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = $exePath
    $psi.Arguments = "$($config.DeviceIp) $($config.DevicePort) $($config.Username) $($config.Password) $($config.WebhookUrl)"
    $psi.UseShellExecute = $false
    $psi.RedirectStandardOutput = $true
    $psi.RedirectStandardError = $true
    $psi.CreateNoWindow = $true

    $proc = [System.Diagnostics.Process]::Start($psi)

    while (-not $proc.HasExited) {
        $line = $proc.StandardOutput.ReadLine()
        if ($line) {
            Write-Log "Event: $line"
        }
        $err = $proc.StandardError.ReadLine()
        if ($err) { Write-Host "STREAM: $err" }
    }

    Write-Log "EventStreamListener exited (will auto-restart via scheduled task)" -Level 'WARN'
}

# --- Main ---
Write-Log "Hikvision Middleware started (Mode: $Mode)"

switch ($Mode) {
    'listen' {
        Start-EventStreamListener
    }
    'once' {
        Invoke-PollCycle
    }
}

Write-Log "Hikvision Middleware finished"
