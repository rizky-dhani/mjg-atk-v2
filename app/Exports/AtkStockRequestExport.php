<?php

namespace App\Exports;

use App\Models\AtkStockRequestItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AtkStockRequestExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(protected array|int $requestIds)
    {
        if (is_int($this->requestIds)) {
            $this->requestIds = [$this->requestIds];
        }
    }

    public function query(): Builder
    {
        return AtkStockRequestItem::query()
            ->with(['request.requester', 'request.division', 'item', 'category'])
            ->whereIn('request_id', $this->requestIds);
    }

    public function headings(): array
    {
        return [
            'Request Number',
            'Requester',
            'Division',
            'Approval Status',
            'Fulfillment Status',
            'Item Name',
            'Item Category',
            'Requested Quantity',
            'Received Quantity',
            'Item Status',
        ];
    }

    public function map($item): array
    {
        return [
            $item->request->request_number,
            $item->request->requester?->name,
            $item->request->division?->name,
            ucfirst($item->request->approval_status),
            $item->request->fulfillment_status->getLabel(),
            $item->item?->name,
            $item->category?->name,
            $item->quantity,
            $item->received_quantity,
            $item->status->getLabel(),
        ];
    }
}
