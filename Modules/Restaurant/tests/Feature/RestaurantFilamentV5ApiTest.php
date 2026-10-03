<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Restaurant\Filament\Resources\StockItems\Pages\CreateStockItem;
use Modules\Restaurant\Filament\Resources\StockItems\Pages\ListStockItems;
use Modules\Restaurant\Models\StockItem;
use Modules\Restaurant\Tests\Concerns\GrantsRestaurantRoles;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Renders the screens whose schemas are built on page load.
 *
 * The version-compatibility guard (stale imports, removed helpers) lives in
 * Tests\Feature\FilamentApiCompatibilityTest because it applies to every
 * module. What is left here is the half a source scan cannot do: prove these
 * particular forms actually build for a real user with a real record behind
 * them. unit_cost is the field that regressed, so it is the one asserted.
 */
class RestaurantFilamentV5ApiTest extends TestCase
{
    use DatabaseTransactions, GrantsRestaurantRoles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->grantRestaurantRole(RoleEnum::RESTAURANT_MANAGER->value);
    }

    #[Test]
    public function the_stock_item_screens_build_their_forms(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(CreateStockItem::class)->assertOk();

        StockItem::create([
            'name' => 'Flour '.uniqid(),
            'unit' => 'kg',
            'stock_quantity' => 10,
            'min_stock_level' => 2,
            'unit_cost' => 750,
        ]);

        Livewire::test(ListStockItems::class)
            ->assertOk()
            ->assertSee('Flour');
    }

    #[Test]
    public function the_money_fields_still_declare_their_currency(): void
    {
        // A guard that only forbids money() would happily pass if the fields
        // silently lost their currency indicator. Pin the affix.
        $this->actingAs($this->staff());

        Livewire::test(CreateStockItem::class)
            ->assertFormFieldExists(
                'unit_cost',
                fn ($field): bool => $field->getPrefixLabel() === 'NGN',
            );
    }

    private function staff(): User
    {
        $user = User::factory()->create();

        $user->assignRole(Role::findOrCreate(RoleEnum::RESTAURANT_MANAGER->value, 'web'));

        return $user->fresh();
    }
}
