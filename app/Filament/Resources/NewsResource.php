<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use App\Models\NewsCategory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    public static function getnavigationLabel(): string
    {
        return __('filament.news');
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
                ->disk('public')
                ->visibility('public')
                ->imagePreviewHeight('150'),

                FileUpload::make('video')
                ->label(__('filament.video'))
                ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov'])
                ->directory('videos/news')
                ->visibility('public')
                ->disk('public')
                ->maxSize(51200),

                Select::make('news_category_id')
                ->label(__('filament.category'))
                ->relationship('category', 'name')
                ->options(function () {
                    return NewsCategory::query()
                        ->limit(100)
                        ->pluck('name', 'id');
                })
                ->getSearchResultsUsing(function (string $search) {
                    return NewsCategory::query()
                        ->where('name', 'like', "%{$search}%")
                        ->limit(20)
                        ->pluck('name', 'id');
                })
                ->getOptionLabelUsing(function ($value): ?string {
                    return NewsCategory::find($value)?->name;
                })
                ->searchable()
                ->columnSpanFull()
                ->required(),

                TextInput::make('name')
                ->label(__('filament.name'))
                ->required()
                ->afterStateUpdated(fn ($state, callable $set) => 
                $set('slug', Str::slug($state))),
                
                TextInput::make('slug')->unique(ignoreRecord: true),

                RichEditor::make('content')
                ->label(__('filament.content'))
                ->placeholder('Nhập nội dung...')
                ->required()
                ->columnSpanFull(),
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
                TextColumn::make('category.name')
                ->label(__('filament.category')),
                TextColumn::make('created_at')
                ->label(__('filament.created_at'))
                ->sortable()
                ->dateTime('H:i:s d/m/Y'),
            ])
            ->filters([
                Filter::make('news_category_id')
            ->form([
                Select::make('news_category_id')
                ->label('Danh mục')
                ->options(function () {
                    return NewsCategory::query()
                        ->limit(100)
                        ->pluck('name', 'id');
                })
                ->getSearchResultsUsing(function (string $search) {
                    return NewsCategory::query()
                        ->where('name', 'like', "%{$search}%")
                        ->limit(20)
                        ->pluck('name', 'id');
                })
                ->getOptionLabelUsing(function ($value): ?string {
                    return NewsCategory::find($value)?->name;
                })
                ->searchable()
                ->required(),
            ])
            ->query(function ($query, array $data) {
                return $query
                    ->when($data['news_category_id'], fn ($q) => $q->where('news_category_id', $data['news_category_id']));
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['news_category_id']) {
                    $indicators[] = __('filament.category') . $data['news_category_id'];
                }

                return $indicators;
            }),
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
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
