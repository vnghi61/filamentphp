<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getnavigationLabel(): string
    {
        return __('filament.user');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('image')
                ->image()
                ->label(__('filament.image'))
                ->imageEditor()
                ->directory('images/users')
                ->columnSpanFull()
                ->disk('public')
                ->visibility('public')
                ->imagePreviewHeight('150'),

                TextInput::make('name')
                ->required()
                ->label(__('filament.image')),
                TextInput::make('phone')
                ->label(__('filament.phone'))
                ->unique(ignoreRecord: true)
                ->required(),
                TextInput::make('email')
                ->label(__('filament.email'))
                ->unique(ignoreRecord: true),

                Select::make('gender')
                ->label(__('filament.gender'))
                ->options([
                    __('filament.male'),
                    __('filament.female'),
                    __('filament.other')
                ])
                ->searchable()
                ->required(),

                DatePicker::make('birthday')
                ->label(__('filament.birthday'))
                ->columnSpanFull()
                ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                ->label(__('filament.image'))
                ->circular()
                ->size(40),
                TextColumn::make('name')
                ->label(__('filament.name'))
                ->searchable()
                ->sortable(),
                TextColumn::make('phone')
                ->label(__('filament.phone'))
                ->searchable(),
                TextColumn::make('email')
                ->label(__('filament.email'))
                ->searchable(),
                TextColumn::make('created_at')
                ->label(__('filament.created_at'))
                ->sortable()
                ->dateTime('H:i:s d/m/Y'),
            ])
            ->filters([
                Filter::make('created_at')
            ->form([
                DatePicker::make('from')->label(__('filament.day_from')),
                DatePicker::make('until')->label(__('filament.day_to')),
            ])
            ->query(function ($query, array $data) {
                return $query
                    ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                    ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['from']) {
                    $indicators[] = __('filament.day_from') . \Carbon\Carbon::parse($data['from'])->format('d/m/Y');
                }

                if ($data['until']) {
                    $indicators[] = __('filament.day_to') . \Carbon\Carbon::parse($data['until'])->format('d/m/Y');
                }

                return $indicators;
            }), 
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->label("")
                ->tooltip(__('filament.view')),
                Tables\Actions\EditAction::make()
                ->label("")
                ->tooltip(__('filament.edit')),

                Tables\Actions\DeleteAction::make()
                ->label("")
                ->tooltip(__('filament.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
