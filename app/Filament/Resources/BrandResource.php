<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';

    public static function getNavigationLabel(): string
    {
        return __('filament.brand');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('image')
                ->image()
                ->label(__('filament.image'))
                ->imageEditor()
                ->directory('images/news')
                ->columnSpanFull()
                ->disk('public')
                ->visibility('public')
                ->imagePreviewHeight('150'),

                TextInput::make('name')
                ->required()->afterStateUpdated(fn ($state, callable $set) => 
                $set('slug', Str::slug($state))),

                TextInput::make('slug')->unique(ignoreRecord: true),
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
                TextColumn::make('created_at')
                ->sortable()
                ->label(__('filament.created_at'))
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
