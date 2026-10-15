<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Models\BookingSource;
use Modules\Website\Models\RoomInventoryBlock;
use Modules\Website\Models\RoomType;
use Modules\Website\Services\RoomCalendarService;

class InventoryCalendarController extends Controller
{
    protected RoomCalendarService $calendarService;

    public function __construct(RoomCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Display the unified inventory calendar view.
     */
    public function index()
    {
        $roomTypes = RoomType::with('units')->active()->ordered()->get();

        try {
            $bookingSources = class_exists(BookingSource::class)
                ? BookingSource::orderBy('name')->pluck('name')
                : collect(RoomCalendarService::SOURCE_KEYS);
        } catch (\Throwable $e) {
            $bookingSources = collect(RoomCalendarService::SOURCE_KEYS);
        }

        return view('website::admin.inventory.calendar', [
            'roomTypes' => $roomTypes,
            'bookingSources' => $bookingSources,
        ]);
    }

    /**
     * API: Get inventory data for the calendar grid.
     */
    public function getInventoryData(Request $request): JsonResponse
    {
        try {
            [$start, $end] = $this->resolveRange($request);

            $data = $this->calendarService->getUnifiedMatrix($start, $end);

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Inventory API Error: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Export the visible availability grid as a CSV file (opens in Excel).
     */
    public function export(Request $request)
    {
        [$start, $end] = $this->resolveRange($request);

        $matrix = $this->calendarService->getUnifiedMatrix($start, $end);
        $matrix = $this->calendarService->filterMatrixBySource($matrix, $request->input('source'));
        $days = $matrix['days'];
        $filename = 'room-calendar-'.$start->format('Y-m-d').'_'.$end->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($matrix, $days) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel reads the currency/accents correctly.
            fwrite($out, "\xEF\xBB\xBF");

            $row = fn (array $cells) => fputcsv($out, $cells);

            $row(['Brickspoint Room Calendar', $matrix['summary']['period'] ?? '']);

            // ── Availability by room type ─────────────────────────────
            $row([]);
            $row(['AVAILABILITY  (available / booked / blocked / sell limit)']);
            $header = ['Room Type', 'Rooms', 'Sell Limit', 'Price'];
            foreach ($days as $day) {
                $header[] = $day['date'];
            }
            $row($header);

            foreach ($matrix['room_types'] as $type) {
                $line = [$type['name'], $type['total_units'], $type['sell_limit'], number_format($type['price'], 2, '.', '')];
                foreach ($days as $day) {
                    $cell = $type['dates'][$day['date']] ?? null;
                    $line[] = $cell
                        ? "{$cell['available']} / {$cell['booked']} / {$cell['blocked']} / {$cell['sell_limit']}"
                        : '';
                }
                $row($line);
            }

            // ── Unit status ────────────────────────────────────────────
            $row([]);
            $row(['UNIT STATUS  (I=In-house, R=Reserved, O=Online, P=Pending, M=Maintenance, B=Blocked, =Available)']);
            $unitHeader = ['Room Type', 'Room'];
            foreach ($days as $day) {
                $unitHeader[] = $day['date'];
            }
            $row($unitHeader);

            $legend = [
                'checked_in' => 'I',
                'checked_out' => 'I',
                'reserved' => 'R',
                'online_booking' => 'O',
                'draft_by_guest' => 'P',
                'maintenance' => 'M',
                'blocked' => 'B',
            ];

            foreach ($matrix['room_types'] as $type) {
                foreach ($type['unit_rows'] as $unit) {
                    $line = [$type['name'], $unit['room_number']];
                    foreach ($days as $day) {
                        $marker = '';
                        foreach ($unit['events'] as $event) {
                            $from = substr($event['start'], 0, 10);
                            $to = substr($event['end'], 0, 10);
                            if ($day['date'] >= $from && $day['date'] < $to) {
                                $marker = $legend[$event['status']] ?? 'X';
                                break;
                            }
                        }
                        $line[] = $marker;
                    }
                    $row($line);
                }
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Resolve and clamp the requested date range.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolveRange(Request $request): array
    {
        $start = $request->input('start')
            ? Carbon::parse($request->input('start'))->startOfDay()
            : now()->startOfMonth();

        $end = $request->input('end')
            ? Carbon::parse($request->input('end'))->startOfDay()
            : now()->endOfMonth();

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        // Limit to max 90 days to prevent performance issues
        if ($start->diffInDays($end) > 90) {
            $end = $start->copy()->addDays(90);
        }

        return [$start, $end];
    }

    /**
     * API: Apply a block to rooms.
     */
    public function applyBlock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'blocked_count' => 'required|integer|min:0',
            'block_type' => 'required|in:maintenance,stop_sell,manual,overbooking_protection',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $block = $this->calendarService->blockRooms(
                $validated['room_type_id'],
                Carbon::parse($validated['start_date']),
                Carbon::parse($validated['end_date']),
                $validated['blocked_count'],
                $validated['block_type'],
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Rooms blocked successfully.',
                'block' => $block,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to block rooms: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Remove an inventory block.
     */
    public function removeBlock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'block_id' => 'required|exists:room_inventory_blocks,id',
        ]);

        try {
            $deleted = $this->calendarService->removeBlock($validated['block_id']);

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'Block removed successfully.' : 'Block not found.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove block: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Apply restrictions to a room type.
     */
    public function applyRestriction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'min_stay' => 'nullable|integer|min:1|max:30',
            'max_stay' => 'nullable|integer|min:1|max:365',
            'stop_sell' => 'boolean',
            'closed_to_arrival' => 'boolean',
            'closed_to_departure' => 'boolean',
            'blocked_count' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $block = $this->calendarService->applyRestrictions(
                $validated['room_type_id'],
                Carbon::parse($validated['start_date']),
                Carbon::parse($validated['end_date']),
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Restrictions applied successfully.',
                'block' => $block,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply restrictions: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Bulk update inventory (multiple selections).
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'updates' => 'required|array|min:1',
            'updates.*.room_type_id' => 'required|exists:room_types,id',
            'updates.*.start_date' => 'required|date',
            'updates.*.end_date' => 'required|date|after_or_equal:updates.*.start_date',
            'updates.*.blocked_count' => 'nullable|integer|min:0',
            'updates.*.min_stay' => 'nullable|integer|min:1',
            'updates.*.max_stay' => 'nullable|integer|min:1',
            'updates.*.stop_sell' => 'boolean',
            'updates.*.closed_to_arrival' => 'boolean',
            'updates.*.closed_to_departure' => 'boolean',
            'updates.*.replace_existing' => 'boolean',
        ]);

        try {
            $results = $this->calendarService->bulkUpdateInventory($validated['updates']);

            return response()->json([
                'success' => true,
                'message' => count($results).' update(s) applied successfully.',
                'blocks' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply updates: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get active blocks for a room type.
     */
    public function getBlocks(Request $request): JsonResponse
    {
        $roomTypeId = $request->input('room_type_id');

        if ($roomTypeId) {
            $blocks = $this->calendarService->getActiveBlocks($roomTypeId);
        } else {
            $blocks = RoomInventoryBlock::with('roomType')
                ->active()
                ->orderBy('start_date')
                ->get();
        }

        return response()->json([
            'blocks' => $blocks,
        ]);
    }

    /**
     * API: Quick actions - Open all rooms for a date range.
     */
    public function openRooms(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'open_count' => 'nullable|integer|min:0',
        ]);

        try {
            // Open the requested range by splitting any overlapping blocks,
            // so the rest of a blocked range stays blocked. A given open_count
            // frees only that many rooms (reduces blocked_count) instead of all.
            $affected = $this->calendarService->openRooms(
                $validated['room_type_id'],
                Carbon::parse($validated['start_date']),
                Carbon::parse($validated['end_date']),
                $validated['open_count'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => "Opened {$affected} block(s). Selected rooms are now available.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to open rooms: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Quick actions - Stop sell for a date range.
     */
    public function stopSell(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            // Remove existing blocks first
            RoomInventoryBlock::forRoomType($validated['room_type_id'])
                ->overlapping($validated['start_date'], $validated['end_date'])
                ->delete();

            // Create stop sell block
            $block = $this->calendarService->applyRestrictions(
                $validated['room_type_id'],
                Carbon::parse($validated['start_date']),
                Carbon::parse($validated['end_date']),
                [
                    'stop_sell' => true,
                    'block_type' => 'stop_sell',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Stop sell applied successfully.',
                'block' => $block,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply stop sell: '.$e->getMessage(),
            ], 500);
        }
    }
}
