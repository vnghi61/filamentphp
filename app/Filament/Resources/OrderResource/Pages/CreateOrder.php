<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
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
        $data['subtotal'] = 0;

        $data['total'] = 0;

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;
    
        $subtotal = $record->orderItems()->sum(DB::raw('total'));
    
        $record->update([
            'subtotal' => $subtotal,
            'paid_amount' => $record->payment_status === 'Đã thanh toán' ? $subtotal : 0,
            'due_amount' => $record->payment_status === 'Đã thanh toán' ? 0 : $subtotal,
            'total' => $subtotal - $record->discount + $record->shipping_fee,
            'total_quantity' => $record->orderItems()->sum(DB::raw('quantity')),
            'total_items' => $record->orderItems()->count(),
        ]);
    }    
    
}
