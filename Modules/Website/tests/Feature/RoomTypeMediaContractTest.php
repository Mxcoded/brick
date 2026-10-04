<?php

namespace Modules\Website\Tests\Feature;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Website\Filament\Resources\RoomTypeResource;
use Modules\Website\Filament\Resources\RoomTypeResource\Pages\EditRoomType;
use Modules\Website\Models\RoomType;
use Modules\Website\Support\MediaPath;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Pins the storage contract for room_types.image_url.
 *
 * The column stores a path on the public disk. It used to store whatever URL the
 * upload happened to produce, and four readers disagreed about what that meant: the
 * Filament table read it as a disk path, the Filament form demanded https://, the
 * Blade templates echoed it raw, and the front desk prefixed "storage/" onto it and
 * rendered storage/https://images.unsplash.com/... for every seeded room type.
 *
 * These tests exist so the next person who turns image_url back into a URL field
 * meets a failing test rather than a broken check-in screen.
 */
class RoomTypeMediaContractTest extends TestCase
{
    use DatabaseTransactions;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->editor = User::factory()->create();
        $this->editor->givePermissionTo(Permission::findOrCreate('website.room-types.update', 'web'));
    }

    public function test_a_disk_path_resolves_to_a_public_disk_url(): void
    {
        Storage::disk('public')->put('room-types/deluxe.jpg', 'binary');

        $roomType = $this->makeRoomType('Deluxe', ['image_url' => 'room-types/deluxe.jpg']);

        $this->assertSame(Storage::disk('public')->url('room-types/deluxe.jpg'), $roomType->imageUrl());
    }

    public function test_a_legacy_absolute_url_is_passed_through_untouched(): void
    {
        $url = 'https://images.unsplash.com/photo-1505691938895-1758d7eaa511?q=80';

        $this->assertSame($url, $this->makeRoomType('Legacy', ['image_url' => $url])->imageUrl());
    }

    public function test_a_blank_image_resolves_to_null_rather_than_a_broken_src(): void
    {
        $this->assertNull($this->makeRoomType('No Image')->imageUrl());
    }

    public function test_an_absolute_storage_url_from_our_own_disk_reduces_to_a_path(): void
    {
        // Host-independent on purpose: a row uploaded under a different APP_URL still
        // normalises, which is the whole reason the column stores a path.
        $this->assertSame(
            'room-types/legacy-upload.jpg',
            MediaPath::toStoragePath('http://staging.example.test/storage/room-types/legacy-upload.jpg')
        );
    }

    public function test_a_remote_url_we_do_not_host_is_not_rewritten_to_a_path(): void
    {
        $remote = 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80';

        // Null means "leave the column alone" - there is no local file to point at.
        $this->assertNull(MediaPath::toStoragePath($remote));
        $this->assertTrue(MediaPath::isRemote($remote));
    }

    public function test_the_room_type_migration_rewrites_our_urls_and_spares_remote_ones(): void
    {
        $storedPath = 'room-types/migrated.jpg';
        $remoteUrl = 'https://images.unsplash.com/photo-1554995207-c18c203602cb?q=80';

        DB::table('room_types')->insert([
            'name' => 'Migrated',
            'slug' => 'migrated',
            'price' => 100000,
            'capacity' => 2,
            'image_url' => 'http://localhost/storage/'.$storedPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $remoteId = DB::table('room_types')->insertGetId([
            'name' => 'Remote',
            'slug' => 'remote',
            'price' => 100000,
            'capacity' => 2,
            'image_url' => $remoteUrl,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runMediaMigration();

        $this->assertSame($storedPath, RoomType::where('slug', 'migrated')->firstOrFail()->image_url);
        $this->assertSame(
            $remoteUrl,
            RoomType::where('id', $remoteId)->firstOrFail()->image_url,
            'A remote photograph has no local copy, so the migration must not touch it.'
        );
    }

    public function test_the_editor_saves_an_upload_as_a_path_not_a_url(): void
    {
        $roomType = $this->makeRoomType('Uploadable');

        $this->actingAs($this->editor);

        Livewire::test(EditRoomType::class, ['record' => $roomType->getKey()])
            // FileUpload state is a list of disk paths, which is precisely why this
            // field cannot produce the absolute URL the column used to hold.
            ->fillForm(['image_url' => ['room-types/uploaded.jpg']])
            ->call('save')
            ->assertHasNoFormErrors();

        $stored = $roomType->fresh()->image_url;

        $this->assertStringContainsString('room-types/', $stored);
        $this->assertStringNotContainsString('://', $stored);
    }

    public function test_the_editor_form_offers_an_upload_widget_for_the_image(): void
    {
        $roomType = $this->makeRoomType('Widgeted', ['image_url' => 'room-types/widgeted.jpg']);

        $this->actingAs($this->editor);

        // Assert on the schema, not on a rendered DOM string: the regression was that
        // this field was a URL TextInput, which cannot produce a path at all.
        $component = Livewire::test(EditRoomType::class, ['record' => $roomType->getKey()])
            ->instance();

        $upload = collect($component->getSchema('form')->getComponents())
            ->flatMap(fn ($component) => method_exists($component, 'getChildComponents')
                ? $component->getChildComponents()
                : [$component])
            ->first(fn ($component) => $component->getName() === 'image_url');

        $this->assertInstanceOf(
            FileUpload::class,
            $upload,
            'image_url must be a FileUpload so the column receives a disk path.'
        );
        $this->assertSame('public', $upload->getDiskName());
    }

    public function test_the_room_type_index_renders_for_a_room_type_stored_as_a_path(): void
    {
        $this->makeRoomType('Listed', ['image_url' => 'room-types/listed.jpg']);

        $this->actingAs($this->editor)
            ->get(RoomTypeResource::getUrl())
            ->assertSuccessful();
    }

    private function makeRoomType(string $name, array $attributes = []): RoomType
    {
        return RoomType::create(array_merge([
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'price' => 100000,
            'capacity' => 2,
            'description' => 'A test room type.',
        ], $attributes));
    }

    private function runMediaMigration(): void
    {
        $migration = require __DIR__.'/../../database/migrations/2026_10_04_000001_normalize_room_type_image_url_to_disk_path.php';

        $migration->up();
    }
}
