<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use \App\Traits\RedirectTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    use RedirectTraits;

    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    { 
        $data['invoice_number'] = 'DH';

        $data['subtotal'] = 0;

        $data['total'] = 0;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->load('orderItems');

        $subtotal = $this->record->orderItems->sum('total');

        foreach ($this->record->orderItems as $item) {
            if ($item->product_id && $item->quantity > 0) {
                $product = Product::find($item->product_id);

                if ($product) {
                    $product->inventory_quantity = max(0, $product->inventory_quantity - $item->quantity);
                    $product->save();
                }
            }
        }

        $invoiceNumber = $this->record->invoice_number . str_pad($this->record->id, 6, '0', STR_PAD_LEFT);

        $this->record->update([
            'invoice_number' => $invoiceNumber,
            'subtotal' => $subtotal,
            'paid_amount' => $this->record->payment_status === 'Đã thanh toán' ? $subtotal : 0,
            'due_amount' => $this->record->payment_status === 'Đã thanh toán' ? 0 : $subtotal,
            'total' => $subtotal - $this->record->discount + $this->record->shipping_fee,
            'total_quantity' => $this->record->orderItems->sum('quantity'),
            'total_items' => $this->record->orderItems->count(),
        ]);
    }
}
