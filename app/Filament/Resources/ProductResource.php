<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            FileUpload::make('image')
            ->image()
            ->label('Ảnh')
            ->imageEditor()
            ->directory('images/products')
            ->disk('public')
            ->visibility('public')
            ->imagePreviewHeight('150'),

            FileUpload::make('video')
            ->label('Video')
            ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov'])
            ->directory('videos/products')
            ->visibility('public')
            ->disk('public')
            ->maxSize(51200),

            TextInput::make('name')->required()->afterStateUpdated(fn ($state, callable $set) => 
            $set('slug', Str::slug($state))),

            TextInput::make('slug')->required()->unique(ignoreRecord: true),

            Select::make('category_id')
            ->label('Danh mục')
            ->options(function () {
                return Category::query()
                    ->limit(100)
                    ->pluck('name', 'id');
            })
            ->getSearchResultsUsing(function (string $search) {
                return Category::query()
                    ->where('name', 'like', "%{$search}%")
                    ->limit(20)
                    ->pluck('name', 'id');
            })
            ->getOptionLabelUsing(function ($value): ?string {
                return Category::find($value)?->name;
            })
            ->relationship('category', 'name')
            ->columnSpanFull()
            ->searchable()
            ->required(),

            Select::make('brand_id')
            ->label('Thương hiệu')
            ->options(function () {
                return Brand::query()
                    ->limit(100)
                    ->pluck('name', 'id');
            })
            ->getSearchResultsUsing(function (string $search) {
                return Brand::query()
                    ->where('name', 'like', "%{$search}%")
                    ->limit(20)
                    ->pluck('name', 'id');
            })
            ->getOptionLabelUsing(function ($value): ?string {
                return Brand::find($value)?->name;
            })
            ->relationship('brand', 'name')
            ->searchable(),

            Select::make('unit_id')
            ->label('Đơn vị')
            ->relationship('unit', 'name')
            ->options(function () {
                return Unit::query()
                    ->limit(100)
                    ->pluck('name', 'id');
            })
            ->getSearchResultsUsing(function (string $search) {
                return Unit::query()
                    ->where('name', 'like', "%{$search}%")
                    ->limit(20)
                    ->pluck('name', 'id');
            })
            ->getOptionLabelUsing(function ($value): ?string {
                return Unit::find($value)?->name;
            })
            ->searchable()
            ->required(),

            TextInput::make('item_code')
            ->label('Mã hàng hóa')
            ->required()
            ->suffixAction(
            Action::make('generateCode')
                ->icon('heroicon-m-qr-code')
                ->tooltip('Tạo mã tự động')
                ->action(function (callable $set) {
                    $set('item_code', 'MH-' . strtoupper(Str::random(6)));
                })
            ),

            TextInput::make('inventory_quantity')
            ->label('Tồn kho')
            ->numeric()
            ->required(),

            TextInput::make('purchase_price')
            ->label('Giá nhập')
            ->numeric()
            ->required(),

            TextInput::make('sales_price')
            ->label('Giá bán')
            ->numeric()
            ->required(),

            Toggle::make('display')
            ->label('Kích hoạt')
            ->inline(true)
            ->default(true),

            Textarea::make('description')
            ->label('Mô tả')
            ->rows(5)
            ->placeholder('Nhập mô tả...')
            ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Danh mục'),
                TextColumn::make('purchase_price')->label('Giá nhập'),
                TextColumn::make('sales_price')->label('Giá bán'),
                TextColumn::make('inventory_quantity')->label('Tồn kho'),
                TextColumn::make('created_at')->dateTime(),
                ToggleColumn::make('display')
                ->label('Kích hoạt')
                ->tooltip('Bật/Tắt hiển thị')   
            ])
            ->filters([
                // Filter theo giá mua
                Filter::make('purchase_price')
                    ->form([
                        TextInput::make('min')->numeric()->label('Giá mua từ'),
                        TextInput::make('max')->numeric()->label('Giá mua đến'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min'], fn ($q) => $q->where('purchase_price', '>=', $data['min']))
                            ->when($data['max'], fn ($q) => $q->where('purchase_price', '<=', $data['max']));
                    }),
            
                // Filter theo giá bán
                Filter::make('sales_price')
                    ->form([
                        TextInput::make('min')->numeric()->label('Giá bán từ'),
                        TextInput::make('max')->numeric()->label('Giá bán đến'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min'], fn ($q) => $q->where('sales_price', '>=', $data['min']))
                            ->when($data['max'], fn ($q) => $q->where('sales_price', '<=', $data['max']));
                    }),
            
                // Filter theo mã sản phẩm
                Filter::make('item_code')
                    ->form([
                        TextInput::make('value')->label('Mã sản phẩm'),
                    ])
                    ->query(fn ($query, array $data) =>
                        $query->when($data['value'], fn ($q) => $q->where('item_code', 'like', '%' . $data['value'] . '%'))
                    ),
            
                // Filter theo danh mục (nếu có quan hệ)
                Filter::make('category_id')
                    ->form([
                        Select::make('value')
                            ->label('Danh mục')
                            ->options(function () {
                                return Category::query()
                                    ->limit(100)
                                    ->pluck('name', 'id');
                            })
                            ->getSearchResultsUsing(function (string $search) {
                                return Category::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->limit(20)
                                    ->pluck('name', 'id');
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                return Category::find($value)?->name;
                            })
                            ->searchable()
                    ])
                    ->query(fn ($query, array $data) =>
                        $query->when($data['value'], fn ($q) => $q->where('category_id', $data['value']))
                    ),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
