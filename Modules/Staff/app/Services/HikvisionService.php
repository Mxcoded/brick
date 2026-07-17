<?php

namespace Modules\Staff\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Staff\Models\AttendanceLog;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\HikvisionAttendanceRecord;
use Modules\Staff\Models\StaffSetting;

class HikvisionService
{
    protected ?string $ip;

    protected ?string $username;

    protected ?string $password;

    protected int $port;

    protected int $timeout;

    protected string $deviceType;

    public function __construct()
    {
        $this->ip = StaffSetting::get('hikvision_ip');
        $this->username = StaffSetting::get('hikvision_username', 'admin');
        $this->password = StaffSetting::get('hikvision_password');
        $this->port = (int) StaffSetting::get('hikvision_port', 80);
        $this->timeout = (int) StaffSetting::get('hikvision_timeout', 30);
        $this->deviceType = StaffSetting::get('hikvision_device_type', 'attendance');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->ip) && ! empty($this->password);
    }

    protected function baseUrl(): string
    {
        return "http://{$this->ip}:{$this->port}";
    }

    protected function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($this->timeout)
            ->withOptions([
                'verify' => false,
                'curl' => [
                    CURLOPT_SSL_CIPHER_LIST => 'ALL:@SECLEVEL=0',
                ],
            ])
            ->withDigestAuth($this->username, $this->password);
    }

    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'Hikvision machine not configured.'];
        }

        try {
            $response = $this->http()->get($this->baseUrl().'/ISAPI/System/deviceInfo');

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Connection successful.'];
            }

            if ($response->status() === 401) {
                return ['success' => false, 'message' => 'Authentication failed. Check username/password.'];
            }

            return ['success' => false, 'message' => "HTTP {$response->status()}: ".Str::limit($response->body(), 100)];
        } catch (\Exception $e) {
            Log::error('Hikvision connection test failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function fetchAttendanceRecords(?Carbon $from = null, ?Carbon $to = null): Collection
    {
        if (! $this->isConfigured()) {
            Log::warning('HikvisionService: machine not configured.');

            return collect();
        }

        return $this->deviceType === 'access_control'
            ? $this->fetchAcsEvents($from, $to)
            : $this->fetchFromAttendanceEndpoint($from, $to);
    }

    protected function fetchFromAttendanceEndpoint(?Carbon $from, ?Carbon $to): Collection
    {
        $from = $from ?? now()->startOfDay();
        $to = $to ?? now()->endOfDay();

        $allRecords = collect();
        $position = 0;
        $maxResults = 100;
        $maxIterations = 50;

        for ($i = 0; $i < $maxIterations; $i++) {
            $xml = $this->buildSearchXml($from, $to, $position, $maxResults);

            try {
                $response = $this->http()
                    ->withHeaders(['Content-Type' => 'application/xml'])
                    ->send('POST', $this->baseUrl().'/ISAPI/AttendanceRecord/Search', ['body' => $xml]);

                if (! $response->successful()) {
                    Log::warning('Hikvision API request failed', [
                        'status' => $response->status(),
                        'body' => Str::limit($response->body(), 500),
                    ]);
                    break;
                }

                $records = $this->parseAttendanceResponse($response->body());
                $allRecords = $allRecords->concat($records);

                if ($records->count() < $maxResults) {
                    break;
                }

                $position += $maxResults;
            } catch (\Exception $e) {
                Log::error('Hikvision fetch error', ['error' => $e->getMessage()]);
                break;
            }
        }

        return $allRecords;
    }

    protected function fetchAcsEvents(?Carbon $from, ?Carbon $to): Collection
    {
        $from = $from ?? now()->startOfDay();
        $to = $to ?? now()->endOfDay();

        $allRecords = collect();
        $position = 0;
        $maxResults = 100;
        $maxIterations = 50;

        for ($i = 0; $i < $maxIterations; $i++) {
            $xml = $this->buildAcsSearchXml($from, $to, $position, $maxResults);

            try {
                $response = $this->http()
                    ->withHeaders(['Content-Type' => 'application/xml'])
                    ->send('POST', $this->baseUrl().'/ISAPI/AccessControl/AcsEvent/Search', ['body' => $xml]);

                if (! $response->successful()) {
                    Log::warning('Hikvision ACS API request failed', [
                        'status' => $response->status(),
                        'body' => Str::limit($response->body(), 500),
                    ]);
                    break;
                }

                $records = $this->parseAcsEventResponse($response->body());
                $allRecords = $allRecords->concat($records);

                if ($records->count() < $maxResults) {
                    break;
                }

                $position += $maxResults;
            } catch (\Exception $e) {
                Log::error('Hikvision ACS fetch error', ['error' => $e->getMessage()]);
                break;
            }
        }

        return $allRecords;
    }

    public function importFetchedRecords(Collection $records): array
    {
        $imported = 0;
        $skipped = 0;
        $matched = 0;

        $employees = Employee::whereNotNull('biometric_pin')
            ->pluck('id', 'biometric_pin');

        $existingUids = HikvisionAttendanceRecord::whereIn(
            'original_id',
            $records->pluck('uid')->filter()
        )->pluck('id', 'original_id');

        $now = now();

        foreach ($records as $record) {
            $uid = $record['uid'] ?? null;
            if (! $uid) {
                $skipped++;

                continue;
            }

            if (isset($existingUids[$uid])) {
                $skipped++;

                continue;
            }

            $pin = $record['pin'] ?? '';
            $employeeId = $employees[$pin] ?? null;
            $punchTime = $record['time'] ?? null;
            $punchType = $this->resolvePunchType($record['status'] ?? null);

            if (! $punchTime) {
                $skipped++;

                continue;
            }

            HikvisionAttendanceRecord::create([
                'original_id' => (string) $uid,
                'employee_id' => $employeeId,
                'pin' => (string) $pin,
                'punch_time' => $punchTime,
                'punch_type' => $punchType,
                'raw_data' => $record,
                'imported_at' => $now,
            ]);

            if ($employeeId) {
                $this->syncToAttendanceLog($employeeId, $punchTime, $punchType);
                $matched++;
            }

            $imported++;
            $existingUids[$uid] = true;
        }

        return [
            'total' => $records->count(),
            'imported' => $imported,
            'skipped' => $skipped,
            'matched_employees' => $matched,
        ];
    }

    protected function syncToAttendanceLog(int $employeeId, Carbon $punchTime, ?string $punchType): void
    {
        $today = $punchTime->copy()->startOfDay();
        $log = AttendanceLog::firstOrNew([
            'employee_id' => $employeeId,
            'date' => $today,
        ]);

        if ($punchType === 'in' && ! $log->clock_in) {
            $log->clock_in = $punchTime;
            $log->status = 'present';
            $log->save();
        } elseif ($punchType === 'out' && $log->clock_in && ! $log->clock_out) {
            $log->clock_out = $punchTime;
            $log->save();
        } elseif (! $log->exists) {
            $log->clock_in = $punchTime;
            $log->punch_type = $punchType;
            $log->status = $punchType === 'out' ? 'present' : 'present';
            $log->save();
        }
    }

    protected function buildSearchXml(Carbon $from, Carbon $to, int $position, int $maxResults): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<AttendanceRecordSearch>
<searchResultPosition>'.$position.'</searchResultPosition>
<maxResults>'.$maxResults.'</maxResults>
<AttendanceRecordCondition>
<startTime>'.$from->format('Y-m-d\TH:i:s').'</startTime>
<endTime>'.$to->format('Y-m-d\TH:i:s').'</endTime>
</AttendanceRecordCondition>
</AttendanceRecordSearch>';
    }

    protected function parseAttendanceResponse(string $body): Collection
    {
        $records = collect();
        $xml = simplexml_load_string($body);

        if (! $xml) {
            Log::warning('HikvisionService: failed to parse XML response', ['body' => Str::limit($body, 500)]);

            return $records;
        }

        $xml->registerXPathNamespace('h', 'http://www.hikvision.com/ver10/XMLSchema');

        $nodes = $xml->xpath('//AttendanceRecord') ?? $xml->AttendanceRecord ?? [];

        foreach ($nodes as $node) {
            $uid = null;
            if (isset($node['uid'])) {
                $uid = (string) $node['uid'];
            } elseif (isset($node->uid)) {
                $uid = (string) $node->uid;
            }

            $timeRaw = (string) ($node->time ?? '');
            $time = $timeRaw ? Carbon::parse($timeRaw) : null;

            $records->push([
                'uid' => $uid ?: uniqid('hik_', true),
                'pin' => (string) ($node->employeeId ?? ''),
                'time' => $time,
                'status' => (string) ($node->status ?? ''),
            ]);
        }

        return $records;
    }

    protected function buildAcsSearchXml(Carbon $from, Carbon $to, int $position, int $maxResults): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<AcsEventSearch xmlns="http://www.isapi.org/ver20/XMLSchema">
<searchID>'.(string) Str::uuid().'</searchID>
<searchResultPosition>'.$position.'</searchResultPosition>
<maxResults>'.$maxResults.'</maxResults>
<AcsEventCondition>
<startTime>'.$from->format('Y-m-d\TH:i:s').'</startTime>
<endTime>'.$to->format('Y-m-d\TH:i:s').'</endTime>
</AcsEventCondition>
</AcsEventSearch>';
    }

    protected function parseAcsEventResponse(string $body): Collection
    {
        $records = collect();
        $xml = simplexml_load_string($body);

        if (! $xml) {
            Log::warning('HikvisionService: failed to parse ACS event XML', ['body' => Str::limit($body, 500)]);

            return $records;
        }

        $nodes = $xml->xpath('//AcsEvent') ?? [];

        foreach ($nodes as $node) {
            $timeRaw = (string) ($node->time ?? '');
            $time = $timeRaw ? Carbon::parse($timeRaw) : null;

            if (! $time) {
                continue;
            }

            $minorType = (int) ($node->minorEventType ?? 0);

            $records->push([
                'uid' => (string) ($node->serialNumber ?? uniqid('acs_', true)),
                'pin' => (string) ($node->employeeNoString ?? $node->cardNo ?? ''),
                'time' => $time,
                'status' => $this->resolveMinorEventType($minorType),
            ]);
        }

        return $records;
    }

    protected function resolveMinorEventType(int $minorType): string
    {
        return match ($minorType) {
            75, 77, 79, 81, 83 => 'in',
            76, 78, 80, 82, 84 => 'out',
            default => 'unknown',
        };
    }

    protected function resolvePunchType(?string $status): string
    {
        return match ($status) {
            '1', 'in', 'check_in' => 'in',
            '2', 'out', 'check_out' => 'out',
            default => 'unknown',
        };
    }
}
