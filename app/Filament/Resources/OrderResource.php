<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            TextInput::make('invoice_number')->label('Số hóa đơn')->unique(),
            
            Select::make('user_id')
            ->label('Khách hàng')
            ->searchable()
            ->options(function () {
                return User::query()
                    ->limit(100)
                    ->pluck('name', 'id');
            })
            ->getSearchResultsUsing(function (string $search) {
                return User::query()
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->limit(20)
                    ->pluck('name', 'id');
            })
            ->getOptionLabelUsing(function ($value): ?string {
                return User::find($value)?->name;
            })
            ->required(),        
    
            // Select chọn sản phẩm
            Select::make('selected_product')
                ->label('Chọn sản phẩm')
                ->options(Product::all()->pluck('name', 'id'))
                ->searchable()
                ->columnSpanFull()
                ->live()
                ->afterStateUpdated(function ($state, $component) {
                    if (!$state) return;
                    
                    $product = Product::find($state);
                    $livewire = $component->getLivewire();
                    $current = $livewire->data['product_list'] ?? [];
                
                    if ($product && !collect($current)->pluck('id')->contains($product->id)) {
                        $current[] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->sales_price ?? 100,
                            'quantity' => 1
                        ];
                
                        $livewire->data['product_list'] = $current;
                    }
                
                    // Reset Select về null
                    $livewire->data['selected_product'] = null;
                })
                ->dehydrated(false),
    
            // Repeater hiển thị danh sách đã chọn
            Repeater::make('product_list')
                ->label('Sản phẩm mua')
                ->columnSpanFull()
                ->live()
                ->schema([
                    Hidden::make('id'),
                    TextInput::make('name')
                        ->label('Tên sản phẩm')
                        ->disabled(),
                    TextInput::make('price')
                        ->label('Giá')
                        ->disabled(),
                    TextInput::make('quantity')
                        ->label('Số lượng')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->live(),
                ])
                ->columns(3)
                ->visible(true)
                ->reorderable(false)
                ->addable(false)
                ->deletable(true),

                Hidden::make('subtotal')->default(0),
                Hidden::make('total')->default(0),
    
                TextInput::make('discount')
                ->label('Giảm giá')
                ->numeric()
                ->default(0)
                ->live() // Thêm live() để cập nhật real-time
                ->suffix('VNĐ')
                ->minValue(0),
                
            TextInput::make('shipping_fee')
                ->label('Phí vận chuyển')
                ->numeric()
                ->default(0)
                ->live() // Thêm live() để cập nhật real-time
                ->suffix('VNĐ')
                ->minValue(0),
    
            Select::make('payment_method')
                ->label('Phương thức thanh toán')
                ->options([
                    'COD',
                    'Chuyển khoản'
                ])
                ->default('COD')
                ->searchable()                
                ->required(),
    
            Select::make('order_type')
                ->label('Loại đơn hàng')
                ->options([
                    'Mua tại Cửa hàng',
                    'Mua Online'
                ])
                ->default('Mua tại Cửa hàng')

                ->searchable()                
                ->required(),
    
            Select::make('order_status')
                ->label('Trạng thái đơn hàng')
                ->options([
                    'Chờ xác nhận',
                    'Đã xác nhận',
                    'Đang giao hàng',
                    'Đã giao hàng',
                ])
                ->default('Chờ xác nhận')
                ->searchable()                
                ->required(),
    
            Select::make('payment_status')
                ->label('Trạng thái thanh toán')
                ->options([
                    'Chưa thanh toán',
                    'Đã thanh toán'
                ])
                ->default('Chưa thanh toán')
                ->searchable()                
                ->required(),

            Grid::make(2)
                ->schema([

                Textarea::make('description')
                ->label('Ghi chú')
                ->rows(6),

                Placeholder::make('product_list_display')
                ->label('Thanh toán')
                ->content(function (callable $get) {
                    $products = $get('product_list') ?? [];

                    // Tính toán các giá trị
                    $subtotal = 0;
                    foreach ($products as $product) {
                        $subtotal += $product['price'] * $product['quantity'];
                    }
                    
                    $discount = $get('discount') ?? 0;
                    $shippingFee = $get('shipping_fee') ?? 0;
                    $total = $subtotal - $discount + $shippingFee;

                    $html = '<div class="border rounded-lg overflow-hidden bg-white">';
                    
                    // Phần tính tổng tiền
                    $html .= '<div class="border-t bg-white px-6 py-5">';
                    $html .= '<div class="space-y-2">';
                    
                    // Tạm tính
                    $html .= '<div class="flex justify-between items-center text-sm">';
                    $html .= '<span class="text-gray-600">Tạm tính (' . count($products) . ' sản phẩm):</span>';
                    $html .= '<span class="font-medium text-gray-900">' . number_format($subtotal) . ' VNĐ</span>';
                    $html .= '</div>';
                    
                    // Giảm giá
                    $html .= '<div class="flex justify-between items-center text-sm">';
                    $html .= '<span class="text-gray-600">Giảm giá:</span>';
                    $html .= '<span class="font-medium text-red-600">-' . number_format($discount) . ' VNĐ</span>';
                    $html .= '</div>';
                    
                    // Phí vận chuyển
                    $html .= '<div class="flex justify-between items-center text-sm">';
                    $html .= '<span class="text-gray-600">Phí vận chuyển:</span>';
                    $html .= '<span class="font-medium text-gray-900">+' . number_format($shippingFee) . ' VNĐ</span>';
                    $html .= '</div>';
                    
                    // Đường kẻ phân cách
                    $html .= '<hr class="my-3 border-gray-300">';
                    
                    // Tổng tiền
                    $html .= '<div class="flex justify-between items-center">';
                    $html .= '<span class="text-lg font-semibold text-gray-900">Tổng tiền:</span>';
                    $html .= '<span class="text-lg font-bold text-blue-600">' . number_format($total) . ' VNĐ</span>';
                    $html .= '</div>';
                    
                    $html .= '</div>'; // Close space-y-2
                    $html .= '</div>'; // Close summary section
                    $html .= '</div>'; // Close main container

                    return new HtmlString($html);
                })
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')->searchable()->sortable(),
                TextColumn::make('order_type')->label('Loại đơn hàng'),
                TextColumn::make('user.name')->label('Khách hàng'),
                TextColumn::make('total')->label('Tổng tiền'),
                TextColumn::make('payment_status')->label('Trạng thái thanh toán'),
                TextColumn::make('order_status')->label('Trạng thái đơn hàng'),
                TextColumn::make('created_at')->label('Ngày đặt hàng')->dateTime()->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                ->form([
                    DatePicker::make('from')->label('Từ ngày'),
                    DatePicker::make('until')->label('Đến ngày'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                })
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['from']) {
                    $indicators[] = 'Từ ngày: ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y');
                }

                if ($data['until']) {
                    $indicators[] = 'Đến ngày: ' . \Carbon\Carbon::parse($data['until'])->format('d/m/Y');
                }

                return $indicators;
            }), 
            Filter::make('payment_status')
            ->form([
                Select::make('payment_status')
                ->label('Trạng thái thanh toán')
                ->options([
                    'Chưa thanh toán',
                    'Đã thanh toán'
                ])
                ->searchable()
                ->required(),
            ])
            ->query(function ($query, array $data) {
                return $query
                    ->when($data['payment_status'], fn ($q) => $q->where('payment_status', $data['payment_status']));
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['payment_status']) {
                    $indicators[] = 'Trạng thái thanh toán: ' . $data['payment_status'];
                }

                return $indicators;
            }),
            Filter::make('order_type')
            ->form([
                Select::make('order_type')
                ->label('Loại đơn hàng')
                ->options([
                    'Mua tại cửa hàng',
                    'Mua Online'
                ])
                ->searchable()
                ->required(),
            ])
            ->query(function ($query, array $data) {
                return $query
                    ->when($data['order_type'], fn ($q) => $q->where('order_type', $data['order_type']));
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['order_type']) {
                    $indicators[] = 'Loại đơn hàng: ' . $data['order_type'];
                }

                return $indicators;
            }),
            Filter::make('order_status')
            ->form([
                Select::make('order_status')
                ->label('Trạng thái đơn hàng')
                ->options([
                    'Chờ xác nhận',
                    'Đã xác nhận',
                    'Đang giao hàng',
                    'Đã giao hàng',
                ])
                ->searchable()
                ->required(),
            ])
            ->query(function ($query, array $data) {
                return $query
                    ->when($data['order_status'], fn ($q) => $q->where('order_status', $data['order_status']));
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['order_status']) {
                    $indicators[] = 'Trạng thái đơn hàng: ' . $data['order_status'];
                }

                return $indicators;
            }),
            Filter::make('user_id')
            ->form([
                Select::make('user_id')
                ->label('Khách hàng')
                ->options(function () {
                    return User::query()
                        ->limit(100)
                        ->pluck('name', 'id');
                })
                ->getSearchResultsUsing(function (string $search) {
                    return User::query()
                        ->where('name', 'like', "%{$search}%")
                        ->limit(20)
                        ->pluck('name', 'id');
                })
                ->getOptionLabelUsing(function ($value): ?string {
                    return User::find($value)?->name;
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
                Tables\Actions\ViewAction::make()->label("")->tooltip('Xem'),
                Tables\Actions\Action::make('mark_as_delivered')
                ->tooltip('Đã giao')
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->modalHeading('Xác nhận đã giao?')
                ->modalDescription('Hành động này không thể hoàn tác')
                ->label("")
                ->requiresConfirmation()
                ->action(function (Order $record) {
                    $record->update(['order_status' => 'Đã giao hàng']);
                })
                ->visible(fn ($record) => $record->order_status !== 'Đã giao hàng' && $record->order_status !== 'Đã hủy'),
                Tables\Actions\Action::make('mark_as_confirmed')
                ->tooltip('Đã xác nhận')
                ->color('info')
                ->label("")
                ->modalHeading('Xác nhận đơn hàng?')
                ->modalDescription('Hành động này không thể hoàn tác')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(function (Order $record) {
                    $record->update(['order_status' => 'Đã xác nhận']);
                })
                ->visible(fn ($record) => $record->order_status !== 'Đã xác nhận' && $record->order_status !== 'Đã hủy'),
                Tables\Actions\Action::make('mark_as_shipping')
                ->tooltip('Đang giao')
                ->color('warning')
                ->modalHeading('Xác nhận đang giao?')
                ->modalDescription('Hành động này không thể hoàn tác')
                ->label("")
                ->icon('heroicon-o-truck')
                ->requiresConfirmation()
                ->action(function (Order $record) {
                    $record->update(['order_status' => 'Đang giao hàng']);
                })
                ->visible(fn ($record) => $record->order_status !== 'Đang giao hàng' && $record->order_status != 'Đã hủy' && $record->order_status !== 'Đã giao hàng'),
                Tables\Actions\Action::make('mark_as_cancelled')
                ->label("")
                ->tooltip('Hủy đơn')
                ->color('danger')
                ->icon('heroicon-o-no-symbol')
                ->requiresConfirmation()
                ->modalHeading('Bạn có chắc muốn xóa?')
                ->modalDescription('Hành động này không thể hoàn tác. Dữ liệu sẽ bị xóa vĩnh viễn.')
                ->action(function (Order $record) {
                    $record->update(['order_status' => '']);
                })
                ->visible(fn ($record) => $record->order_status !== 'Đã hủy' && $record->order_status == 'Đang chờ xác nhận'),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
