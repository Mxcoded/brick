<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Frontdeskcrm\Exports\GuestImportGuide;
use Modules\Frontdeskcrm\Models\Guest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class GuestImportRoundTripTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'guests.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['guests.create', 'access_frontdesk_dashboard']);
    }

    public function test_downloaded_guide_can_be_reimported(): void
    {
        // Use real storage (local disk root is storage/app/private)
        Excel::store(new GuestImportGuide, 'guest-import-guide.xlsx', 'local');
        $path = storage_path('app/private/guest-import-guide.xlsx');
        $this->assertFileExists($path);
        $bytes = file_get_contents($path);

        $file = UploadedFile::fake()->createWithContent('guest-import-guide.xlsx', $bytes);

        $response = $this->actingAs($this->user)->post(route('frontdesk.guests.import.process'), [
            'file' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');
        $this->assertNotNull(Guest::where('full_name', 'John Doe')->first());
        
        // Cleanup
        @unlink($path);
    }
}
