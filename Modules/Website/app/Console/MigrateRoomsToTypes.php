<?php

namespace Modules\Website\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\Booking;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomTypeImage;
use Modules\Website\Models\RoomUnit;

class MigrateRoomsToTypes extends Command
{
    protected $signature = 'rooms:migrate-to-types 
                            {--dry-run : Preview changes without making them}
                            {--force : Skip confirmation}';

    protected $description = 'Migrate existing rooms to room types and units structure';

    // Roman numeral patterns to strip from room names
    private $romanNumerals = [
        ' I' => '', ' II' => '', ' III' => '', ' IV' => '', ' V' => '',
        ' VI' => '', ' VII' => '', ' VIII' => '', ' IX' => '', ' X' => '',
        ' XI' => '', ' XII' => '', ' XIII' => '', ' XIV' => '', ' XV' => '',
        ' 1' => '', ' 2' => '', ' 3' => '', ' 4' => '', ' 5' => '',
        ' 6' => '', ' 7' => '', ' 8' => '', ' 9' => '', ' 10' => '',
    ];

    public function handle()
    {
        $this->info('🏨 Room Migration Tool');
        $this->info('======================');

        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // 1. Get all existing rooms
        $rooms = Room::with(['amenities', 'images'])->get();

        if ($rooms->isEmpty()) {
            $this->info('No rooms found to migrate.');

            return 0;
        }

        $this->info("Found {$rooms->count()} rooms to process.");
        $this->newLine();

        // 2. Group rooms by type name (stripping numbers)
        $groupedRooms = $this->groupRoomsByType($rooms);

        // 3. Display migration plan
        $this->displayMigrationPlan($groupedRooms);

        if ($isDryRun) {
            $this->newLine();
            $this->info('Dry run complete. Run without --dry-run to apply changes.');

            return 0;
        }

        // 4. Confirm and execute
        if (! $this->option('force') && ! $this->confirm('Proceed with migration?')) {
            $this->info('Migration cancelled.');

            return 0;
        }

        $this->executeMigration($groupedRooms);

        $this->newLine();
        $this->info('✅ Migration complete!');

        return 0;
    }

    /**
     * Group rooms by their base type name (stripping Roman numerals).
     */
    private function groupRoomsByType($rooms)
    {
        $grouped = [];

        foreach ($rooms as $room) {
            // Extract base name by removing Roman numerals and numbers at end
            $baseName = $this->extractBaseName($room->name);

            if (! isset($grouped[$baseName])) {
                $grouped[$baseName] = [
                    'type_data' => null,
                    'rooms' => [],
                ];
            }

            // Use first room's data as the type template
            if ($grouped[$baseName]['type_data'] === null) {
                $grouped[$baseName]['type_data'] = [
                    'name' => $baseName,
                    'slug' => Str::slug($baseName),
                    'price' => $room->price,
                    'capacity' => $room->capacity,
                    'size' => $room->size,
                    'bed_type' => $room->bed_type,
                    'description' => $room->description,
                    'image_url' => $room->image_url,
                    'video_url' => $room->video_url,
                    'is_featured' => $room->is_featured,
                    'amenities' => $room->amenities->pluck('id')->toArray(),
                    'images' => $room->images->toArray(),
                ];
            }

            $grouped[$baseName]['rooms'][] = $room;
        }

        return $grouped;
    }

    /**
     * Extract base name from room name (remove Roman numerals and trailing numbers).
     */
    private function extractBaseName($name)
    {
        $baseName = $name;

        // Remove Roman numerals (case-insensitive at end of string)
        $baseName = preg_replace('/\s+(I{1,3}|IV|V|VI{0,3}|IX|X{0,3}|XI{0,3}|XII|XIII|XIV|XV)$/i', '', $baseName);

        // Remove trailing numbers
        $baseName = preg_replace('/\s+\d+$/', '', $baseName);

        return trim($baseName);
    }

    /**
     * Display the migration plan.
     */
    private function displayMigrationPlan($groupedRooms)
    {
        $this->info('📋 Migration Plan:');
        $this->newLine();

        foreach ($groupedRooms as $typeName => $data) {
            $unitCount = count($data['rooms']);
            $this->line("  <fg=cyan>{$typeName}</> → {$unitCount} unit(s)");

            foreach ($data['rooms'] as $room) {
                $unitNumber = $this->generateUnitNumber($room);
                $this->line("      └─ Unit: {$unitNumber} (from \"{$room->name}\")");
            }
            $this->newLine();
        }

        $typeCount = count($groupedRooms);
        $totalUnits = array_sum(array_map(fn ($g) => count($g['rooms']), $groupedRooms));

        $this->table(
            ['Summary', 'Count'],
            [
                ['Room Types to Create', $typeCount],
                ['Room Units to Create', $totalUnits],
            ]
        );
    }

    /**
     * Generate unit number from room name.
     */
    private function generateUnitNumber($room)
    {
        // Try to extract number/Roman numeral from name
        if (preg_match('/\s+(I{1,3}|IV|V|VI{0,3}|IX|X|XI{0,3}|XII|XIII|XIV|XV)$/i', $room->name, $matches)) {
            return 'Unit '.strtoupper($matches[1]);
        }

        if (preg_match('/\s+(\d+)$/', $room->name, $matches)) {
            return 'Unit '.$matches[1];
        }

        // Use room ID as fallback
        return 'Unit '.$room->id;
    }

    /**
     * Execute the migration.
     */
    private function executeMigration($groupedRooms)
    {
        DB::beginTransaction();

        try {
            $this->info('Starting migration...');

            $roomToUnitMap = []; // Maps old room_id to new room_unit_id
            $roomToTypeMap = []; // Maps old room_id to new room_type_id

            foreach ($groupedRooms as $typeName => $data) {
                $this->line("  Creating type: {$typeName}");

                // Create RoomType
                $roomType = RoomType::create([
                    'name' => $data['type_data']['name'],
                    'slug' => $data['type_data']['slug'],
                    'price' => $data['type_data']['price'],
                    'capacity' => $data['type_data']['capacity'],
                    'size' => $data['type_data']['size'],
                    'bed_type' => $data['type_data']['bed_type'],
                    'description' => $data['type_data']['description'],
                    'image_url' => $data['type_data']['image_url'],
                    'video_url' => $data['type_data']['video_url'],
                    'is_featured' => $data['type_data']['is_featured'],
                    'is_active' => true,
                ]);

                // Attach amenities
                if (! empty($data['type_data']['amenities'])) {
                    $roomType->amenities()->attach($data['type_data']['amenities']);
                }

                // Copy images
                foreach ($data['type_data']['images'] as $imageData) {
                    RoomTypeImage::create([
                        'room_type_id' => $roomType->id,
                        'image_url' => $imageData['image_url'],
                        'path' => $imageData['path'] ?? null,
                    ]);
                }

                // Create RoomUnits
                foreach ($data['rooms'] as $room) {
                    $unitNumber = $this->generateUnitNumber($room);

                    $unit = RoomUnit::create([
                        'room_type_id' => $roomType->id,
                        'room_number' => $unitNumber,
                        'floor' => null,
                        'status' => $this->mapStatus($room->status),
                        'notes' => "Migrated from room: {$room->name}",
                    ]);

                    $roomToUnitMap[$room->id] = $unit->id;
                    $roomToTypeMap[$room->id] = $roomType->id;

                    $this->line("    Created unit: {$unitNumber}");
                }
            }

            // Update existing bookings
            $this->info('Updating bookings...');
            $bookingsUpdated = 0;

            foreach (Booking::whereNotNull('room_id')->cursor() as $booking) {
                if (isset($roomToTypeMap[$booking->room_id])) {
                    $booking->update([
                        'room_type_id' => $roomToTypeMap[$booking->room_id],
                        'room_unit_id' => $roomToUnitMap[$booking->room_id] ?? null,
                    ]);
                    $bookingsUpdated++;
                }
            }
            $this->line("  Updated {$bookingsUpdated} bookings");

            // Update existing registrations
            if (class_exists(Registration::class)) {
                $this->info('Updating registrations...');
                $registrationsUpdated = 0;

                foreach (Registration::whereNotNull('room_id')->cursor() as $registration) {
                    if (isset($roomToTypeMap[$registration->room_id])) {
                        $registration->update([
                            'room_type_id' => $roomToTypeMap[$registration->room_id],
                            'room_unit_id' => $roomToUnitMap[$registration->room_id] ?? null,
                        ]);
                        $registrationsUpdated++;
                    }
                }
                $this->line("  Updated {$registrationsUpdated} registrations");
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Migration failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Map old room status to new unit status.
     */
    private function mapStatus($oldStatus)
    {
        return match ($oldStatus) {
            'available' => 'available',
            'booked' => 'available', // 'booked' is now determined by bookings, not unit status
            'maintenance' => 'maintenance',
            default => 'available',
        };
    }
}
