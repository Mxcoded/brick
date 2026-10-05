<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Modules\Frontdeskcrm\Filament\Resources\RegistrationResource;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Tests\Concerns\GrantsFrontdeskRoles;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The Registration form has to actually render.
 *
 * `Select::relationship('roomUnit', ...)` takes the column it shows as its
 * second argument, and Filament turns that straight into SQL. `room_units` has
 * no `name` column - the column is `room_number` - so naming `name` here did not
 * fail an authorization test, it returned a 500 on the create and edit screens
 * the moment anyone with Frontdesk access opened them. The role matrix stayed
 * green throughout because the user was allowed; only the page was broken.
 */
class RegistrationFormRenderTest extends TestCase
{
    use DatabaseTransactions, GrantsFrontdeskRoles;

    private User $receptionist;

    private Guest $guest;

    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->grantFrontdeskRole(RoleEnum::RECEPTIONIST->value);

        $this->receptionist = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $this->receptionist->assignRole(RoleEnum::RECEPTIONIST->value);

        $this->roomType = RoomType::create([
            'name' => 'Render Suite',
            'slug' => 'render-suite-'.uniqid(),
            'price' => 45000,
            'capacity' => 2,
            'is_active' => true,
        ]);

        RoomUnit::create([
            'room_type_id' => $this->roomType->id,
            'room_number' => 'RENDER-101',
            'floor' => 1,
            'status' => 'available',
        ]);

        $this->guest = Guest::create([
            'full_name' => 'Render Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'render'.uniqid().'@example.com',
        ]);
    }

    #[Test]
    public function the_create_form_renders(): void
    {
        $this->actingAs($this->receptionist)
            ->get(RegistrationResource::getUrl('create'))
            ->assertOk();
    }

    #[Test]
    public function the_edit_form_renders(): void
    {
        $registration = $this->guest->registrations()->create([
            'full_name' => $this->guest->full_name,
            'contact_number' => $this->guest->contact_number,
            'email' => $this->guest->email,
            'room_type_id' => $this->roomType->id,
            'room_rate' => 45000,
            'stay_status' => 'reserved',
        ]);

        $this->actingAs($this->receptionist)
            ->get(RegistrationResource::getUrl('edit', ['record' => $registration]))
            ->assertOk();
    }

    #[Test]
    public function room_units_really_has_no_name_column(): void
    {
        // Documents why the select must be pointed at room_number: if a `name`
        // column is ever added to room_units, this test is the signal to
        // reconsider, and until then `name` stays a bug.
        $columns = Schema::getColumnListing('room_units');

        $this->assertContains('room_number', $columns);
        $this->assertNotContains('name', $columns);
    }
}
