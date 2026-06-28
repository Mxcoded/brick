<?php

namespace Modules\Website\Console\Commands;

use App\Models\RoomType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Website\Models\Booking;

class CleanupOrphanedBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cleanup-orphaned 
                            {--dry-run : Show what would be fixed without making changes}
                            {--room-type= : Only check bookings for a specific room type ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix bookings where room_type_id doesn\'t match their assigned unit\'s room type (after unit moves)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $specificRoomType = $this->option('room-type');

        $this->info('');
        $this->info('==============================================');
        $this->info('  Orphaned Bookings Cleanup Tool');
        $this->info('==============================================');
        $this->info('');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->info('');
        }

        // Find bookings where room_unit_id exists but room_type_id doesn't match the unit's current room_type_id
        $query = Booking::query()
            ->whereNotNull('room_unit_id')
            ->whereHas('roomUnit', function ($q) {
                $q->whereColumn('bookings.room_type_id', '!=', 'room_units.room_type_id');
            });

        if ($specificRoomType) {
            $query->where('room_type_id', $specificRoomType);
        }

        $orphanedBookings = $query->with(['roomUnit.roomType', 'roomType'])->get();

        if ($orphanedBookings->isEmpty()) {
            $this->info('✅ No orphaned bookings found. All bookings are correctly linked to their unit\'s room type.');

            return Command::SUCCESS;
        }

        $this->warn("Found {$orphanedBookings->count()} booking(s) with mismatched room types:");
        $this->info('');

        $tableData = [];
        foreach ($orphanedBookings as $booking) {
            $tableData[] = [
                'ID' => $booking->id,
                'Reference' => $booking->booking_reference,
                'Unit' => $booking->roomUnit->room_number ?? 'N/A',
                'Current Room Type' => $booking->roomType->name ?? "ID: {$booking->room_type_id}",
                'Unit\'s Room Type' => $booking->roomUnit->roomType->name ?? 'N/A',
                'Status' => $booking->status,
            ];
        }

        $this->table(
            ['ID', 'Reference', 'Unit', 'Current Room Type', 'Unit\'s Room Type', 'Status'],
            $tableData
        );

        $this->info('');

        if ($dryRun) {
            $this->info('To fix these bookings, run the command without --dry-run');

            return Command::SUCCESS;
        }

        if (! $this->confirm('Do you want to update these bookings to match their unit\'s room type?')) {
            $this->info('Operation cancelled.');

            return Command::SUCCESS;
        }

        // Perform the cleanup
        $this->info('');
        $this->info('Fixing orphaned bookings...');

        $fixed = 0;
        $errors = 0;

        DB::beginTransaction();
        try {
            foreach ($orphanedBookings as $booking) {
                $oldRoomTypeId = $booking->room_type_id;
                $newRoomTypeId = $booking->roomUnit->room_type_id;

                $booking->room_type_id = $newRoomTypeId;
                $booking->save();

                $fixed++;

                Log::info('Orphaned booking fixed', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'room_unit_id' => $booking->room_unit_id,
                    'old_room_type_id' => $oldRoomTypeId,
                    'new_room_type_id' => $newRoomTypeId,
                    'fixed_by' => 'bookings:cleanup-orphaned command',
                ]);

                $this->line("  ✓ Fixed booking #{$booking->id} ({$booking->booking_reference})");
            }

            DB::commit();

            $this->info('');
            $this->info("✅ Successfully fixed {$fixed} booking(s).");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error fixing bookings: {$e->getMessage()}");
            Log::error('Failed to fix orphaned bookings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }

        // Also check for bookings linked to non-existent room types
        $this->info('');
        $this->info('Checking for bookings linked to deleted room types...');

        $deletedRoomTypeBookings = Booking::whereNotNull('room_type_id')
            ->whereDoesntHave('roomType')
            ->get();

        if ($deletedRoomTypeBookings->isNotEmpty()) {
            $this->warn("Found {$deletedRoomTypeBookings->count()} booking(s) linked to deleted room types:");

            foreach ($deletedRoomTypeBookings as $booking) {
                $this->line("  - Booking #{$booking->id} ({$booking->booking_reference}) - Room Type ID: {$booking->room_type_id}");
            }

            $this->info('');
            $this->warn('These bookings need manual attention. Options:');
            $this->line('  1. Restore the deleted room type');
            $this->line('  2. Assign these bookings to a different room type');
            $this->line('  3. Cancel these bookings if no longer valid');
        } else {
            $this->info('✅ No bookings linked to deleted room types.');
        }

        // Summary of room types that can now be safely deleted
        $this->info('');
        $this->info('Checking room types that can be safely deleted...');

        $emptyRoomTypes = RoomType::withCount(['units', 'bookings'])
            ->having('units_count', '=', 0)
            ->get();

        if ($emptyRoomTypes->isNotEmpty()) {
            $this->info('');
            $this->info('Room types with no units:');

            foreach ($emptyRoomTypes as $rt) {
                $canDelete = $rt->bookings_count === 0;
                $status = $canDelete ? '✅ Can delete' : "⚠️ Has {$rt->bookings_count} booking(s)";
                $this->line("  - {$rt->name} (ID: {$rt->id}) - {$status}");
            }
        }

        return Command::SUCCESS;
    }
}
