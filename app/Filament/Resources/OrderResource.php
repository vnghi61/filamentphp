<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function getnavigationLabel(): string
    {
        return __('filament.order');
    }

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            TextInput::make('invoice_number')
            ->label(__('filament.invoice_number'))
            ->unique(),
            
            Select::make('user_id')
            ->label(__('filament.user'))
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
    
            // Select chọn sản phẩm với validate tồn kho
            Select::make('selected_product')
            ->label(__('filament.product'))
                ->options(function () {
                    // Chỉ hiển thị sản phẩm còn hàng
                    return Product::where('inventory_quantity', '>', 0)
                        ->get()
                        ->mapWithKeys(function ($product) {
                            return [$product->id => $product->name . ' (Còn: ' . $product->inventory_quantity . ')'];
                        });
                })
                ->searchable()
                ->required()
                ->columnSpanFull()
                ->live()
                ->afterStateUpdated(function ($state, $component) {
                    if (!$state) return;
                    
                    $product = Product::find($state);
                    $livewire = $component->getLivewire();
                    $current = $livewire->data['orderItems'] ?? [];
                    
                    // Validate sản phẩm tồn tại và còn hàng
                    if (!$product) {
                        $component->getLivewire()->addError('selected_product', 'Sản phẩm không tồn tại.');
                        $component->state(null);
                        return;
                    }
                    
                    if ($product->inventory_quantity <= 0) {
                        $component->getLivewire()->addError('selected_product', 'Sản phẩm "' . $product->name . '" đã hết hàng.');
                        $component->state(null);
                        return;
                    }
                    
                    // Kiểm tra sản phẩm đã có trong danh sách chưa
                    $existingProduct = collect($current)->firstWhere('product_id', $product->id);
                    
                    if ($existingProduct) {
                        // Nếu đã có, kiểm tra xem có thể tăng số lượng không
                        $currentQuantity = $existingProduct['quantity'] ?? 0;
                        if ($currentQuantity >= $product->inventory_quantity) {
                            $component->getLivewire()->addError('selected_product', 
                                'Sản phẩm "' . $product->name . '" chỉ còn ' . $product->inventory_quantity . ' trong kho.');
                            $component->state(null);
                            return;
                        }
                        
                        // Tăng số lượng lên 1
                        $current = collect($current)->map(function ($item) use ($product) {
                            if ($item['product_id'] == $product->id) {
                                $item['quantity'] += 1;
                                $item['total'] = $item['quantity'] * $item['price'];
                            }
                            return $item;
                        })->toArray();
                    } else {
                        // Thêm sản phẩm mới vào danh sách
                        $current[] = [
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->sales_price ?? 0,
                            'discount' => 0,
                            'quantity' => 1,
                            'total' => $product->sales_price ?? 0,
                            'max_quantity' => $product->inventory_quantity, // Lưu số lượng tối đa
                        ];
                    }
                
                    $livewire->data['orderItems'] = $current;
                    
                    // Reset Select về null
                    $livewire->data['selected_product'] = null;
                })
                ->dehydrated(false),
    
            // Repeater hiển thị danh sách đã chọn với validate số lượng
            Repeater::make('orderItems')
                ->label(__('filament.product'))
                ->relationship('orderItems')
                ->columnSpanFull()
                ->default([])
                ->live()
                ->schema([
                    Hidden::make('product_id'),
                    Hidden::make('discount'),
                    Hidden::make('max_quantity'),
                    
                    TextInput::make('name')
                        ->label(__('filament.name'))
                        ->readonly(),
                        
                    TextInput::make('price')
                        ->label(__('filament.sales_price'))
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->live()
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            $quantity = $get('quantity') ?? 0;
                            $price = $get('price') ?? 0;
                            $set('total', $quantity * $price);
                        }),
            
                    TextInput::make('quantity')
                        ->label(__('filament.quantity'))
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->live()
                        ->rules([
                            function (callable $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $productId = $get('product_id');
                                    $product = Product::find($productId);
                                    
                                    if (!$product) {
                                        $fail('Sản phẩm không tồn tại.');
                                        return;
                                    }
                                    
                                    if ($value > $product->inventory_quantity) {
                                        $fail('Số lượng không được vượt quá tồn kho (' . $product->inventory_quantity . ').');
                                        return;
                                    }
                                    
                                    // Kiểm tra tổng số lượng của sản phẩm này trong tất cả các item
                                    $livewire = app()->make('livewire');
                                    $allItems = $livewire->data['orderItems'] ?? [];
                                    $totalQuantity = collect($allItems)
                                        ->where('product_id', $productId)
                                        ->sum('quantity');
                                        
                                    if ($totalQuantity > $product->inventory_quantity) {
                                        $fail('Tổng số lượng sản phẩm này không được vượt quá tồn kho (' . $product->inventory_quantity . ').');
                                    }
                                };
                            }
                        ])
                        ->afterStateUpdated(function (callable $get, callable $set, $state, $component) {
                            $productId = $get('product_id');
                            $product = Product::find($productId);
                            
                            // Validate realtime khi thay đổi số lượng
                            if ($product && $state > $product->inventory_quantity) {
                                $component->getLivewire()->addError(
                                    $component->getStatePath(), 
                                    'Số lượng không được vượt quá tồn kho (' . $product->inventory_quantity . ').'
                                );
                                $set('quantity', $product->inventory_quantity);
                                $state = $product->inventory_quantity;
                            }
                            
                            $quantity = $state ?? 0;
                            $price = $get('price') ?? 0;
                            $set('total', $quantity * $price);
                        })
                        ->helperText(function (callable $get) {
                            $productId = $get('product_id');
                            $product = Product::find($productId);
                            return $product ? 'Tồn kho: ' . $product->inventory_quantity : '';
                        }),
    
                    TextInput::make('total')
                        ->label(__('filament.total'))
                        ->readOnly()
                        ->reactive()
                        ->default(0)
                        ->afterStateHydrated(function (callable $get, callable $set) {
                            $quantity = $get('quantity') ?? 0;
                            $price = $get('price') ?? 0;
                            $set('total', $quantity * $price);
                        })
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            $quantity = $get('quantity') ?? 0;
                            $price = $get('price') ?? 0;
                            $set('total', $quantity * $price);
                        }),
                ])
                ->columns(3)
                ->visible(fn (callable $get) => count($get('orderItems') ?? []) > 0)
                ->reorderable(false)
                ->addable(false)
                ->deletable(true)
                ->deleteAction(function ($action) {
                    return $action->after(function () {
                        // Có thể thêm logic sau khi xóa item nếu cần
                    });
                }),
    
                Hidden::make('subtotal')->default(0),
                Hidden::make('total')->default(0),
    
                TextInput::make('discount')
                ->label(__('filament.discount'))
                ->numeric()
                ->default(0)
                ->live()
                ->suffix('VNĐ')
                ->minValue(0),
                
            TextInput::make('shipping_fee')
                ->label(__('filament.shipping_fee'))
                ->numeric()
                ->default(0)
                ->live()
                ->suffix('VNĐ')
                ->minValue(0),
    
            Select::make('payment_method')
            ->label(__('filament.payment_method'))

                ->options([
                    'COD',
                    'Chuyển khoản'
                ])
                ->default('COD')
                ->searchable()                
                ->required(),
    
            Select::make('order_type')
                ->label(__('filament.order_type'))
                ->options([
                    'Mua tại Cửa hàng',
                    'Mua Online'
                ])
                ->default('Mua tại Cửa hàng')
                ->searchable()                
                ->required(),
    
            Select::make('order_status')
                ->label(__('filament.order_status'))
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
                ->label(__('filament.payment_status'))
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
                ->label(__('filament.description'))
                ->rows(6),
    
                Placeholder::make('product_list_display')
                ->label(__('filament.payment'))
                ->content(function (callable $get) {
                    $products = $get('orderItems') ?? [];
    
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
                TextColumn::make('invoice_number')
                ->label(__('filament.invoice_number'))
                ->searchable()
                ->sortable(),
                TextColumn::make('order_type')
                ->label(__('filament.order_type')),
                TextColumn::make('user.name')
                ->label(__('filament.user')),
                TextColumn::make('total')
                ->label(__('filament.total')),
                TextColumn::make('payment_status')
                ->label(__('filament.payment_status')),
                TextColumn::make('order_status')
                ->label(__('filament.order_status')),
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
            Filter::make('payment_status')
            ->form([
                Select::make('payment_status')
                ->label(__('filament.payment_status'))

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
                    $indicators[] = __('filament.total') . $data['payment_status'];
                }

                return $indicators;
            }),
            Filter::make('order_type')
            ->form([
                Select::make('order_type')
                ->label(__('filament.order_type'))
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
                ->label(__('filament.order_status'))
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
                ->label(__('filament.user'))
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
                    $indicators[] = __('filament.user') . $data['user_id'];
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
