<?php

namespace Modules\Admin\Filament\Pages;

use App\Mail\AccountCreated;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Frontdeskcrm\Rules\ValidEmail;
use Modules\Staff\Models\Employee;
use Spatie\Permission\Models\Role;

class CreateUserFromEmployee extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'admin::filament.pages.create-user-from-employee';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static ?string $navigationLabel = 'Create User From Employee';

    protected static ?string $title = 'Create User From Employee';

    protected static ?string $slug = 'employees/create-user';

    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission('users.manage', 'users.create') ?? false;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->label('Employee')
                    ->options(fn (): array => Employee::query()
                        ->whereNull('end_date')
                        ->whereNull('user_id')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->required()
                    ->helperText('Only active employees without a linked user account are shown.'),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique('users', 'email')
                    ->rules([new ValidEmail]),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->confirmed(),

                TextInput::make('password_confirmation')
                    ->password()
                    ->revealable()
                    ->dehydrated(false),

                Select::make('role')
                    ->options(fn (): array => Role::where('name', '!=', 'guest')->pluck('name', 'name')->all())
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        try {
            $user = DB::transaction(function () use ($data): User {
                $employee = Employee::findOrFail($data['employee_id']);

                $user = User::create([
                    'name' => $employee->name,
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'type' => 'staff',
                ]);

                $employee->update(['user_id' => $user->id]);
                $user->assignRole($data['role']);

                return $user;
            });

            try {
                Mail::to($user->email)->send(new AccountCreated($user, $data['password']));

                Notification::make()
                    ->success()
                    ->title('User account created successfully.')
                    ->body("Login credentials have been sent to {$user->email}.")
                    ->send();
            } catch (\Exception $e) {
                \Log::error('Failed to send account creation email for user '.$user->id.': '.$e->getMessage());

                Notification::make()
                    ->warning()
                    ->title('User account created, but the credentials email could not be delivered.')
                    ->body("The user may need to use the \"Forgot Password\" option or you can resend credentials later ({$user->email}).")
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error creating user account: '.$e->getMessage())
                ->send();
        }
    }
}
