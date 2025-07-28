<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.pages.profile';
    protected static ?string $title = 'Profile';
    protected static ?string $navigationLabel = 'Profile';
    protected static ?int $navigationSort = 99;
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'phone' => auth()->user()->phone,
            'gender' => auth()->user()->gender,
            'birthday' => auth()->user()->birthday,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),
                        Forms\Components\DatePicker::make('birthday'),
                    ]),
                Forms\Components\Section::make('Change Password')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->password()
                            ->currentPassword()
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->rule(Password::default())
                            ->required(),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->same('password')
                            ->required(),
                    ])
                    ->visible(fn () => request()->has('change_password')),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
            Action::make('change_password')
                ->label('Change Password')
                ->url(request()->fullUrlWithQuery(['change_password' => '1']))
                ->visible(fn () => !request()->has('change_password')),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        $user = auth()->user();
        
        if (request()->has('change_password')) {
            $user->update([
                'password' => Hash::make($data['password']),
            ]);
            
            Notification::make()
                ->title('Password updated successfully')
                ->success()
                ->send();
                
            redirect()->to('/admin/profile');
        } else {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'gender' => $data['gender'],
                'birthday' => $data['birthday'],
            ]);
            
            Notification::make()
                ->title('Profile updated successfully')
                ->success()
                ->send();
        }
    }
}