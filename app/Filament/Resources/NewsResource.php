<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use App\Models\NewsCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('image')
                ->image()
                ->label('Ảnh')
                ->imageEditor()
                ->directory('images/news')
                ->disk('public')
                ->visibility('public')
                ->imagePreviewHeight('150'),

                FileUpload::make('video')
                ->label('Video')
                ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov'])
                ->directory('videos/news')
                ->visibility('public')
                ->disk('public')
                ->maxSize(51200),

                Select::make('news_category_id')
                ->label('Danh mục')
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

                TextInput::make('name')->required()->afterStateUpdated(fn ($state, callable $set) => 
                $set('slug', Str::slug($state))),
                TextInput::make('slug')->unique(ignoreRecord: true),

                Textarea::make('content')
                ->label('Nội dung')
                ->rows(5)
                ->placeholder('Nhập nội dung...')
                ->columnSpanFull()
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Ảnh')->circular()->size(40),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Danh mục'),
                TextColumn::make('created_at')->dateTime(),
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
                    ->when($data['user_id'], fn ($q) => $q->where('user_id', $data['user_id']));
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['user_id']) {
                    $indicators[] = 'Khách hàng: ' . $data['user_id'];
                }

                return $indicators;
            }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
