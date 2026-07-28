<?php

namespace Modules\Frontdeskcrm\Services;

use Illuminate\Support\Facades\DB;
use Modules\Frontdeskcrm\Models\Folio;
use Modules\Frontdeskcrm\Models\FolioItem;
use Modules\Frontdeskcrm\Models\Registration;

class FolioService
{
    public function ensureFolio(Registration $registration, ?int $createdBy = null): Folio
    {
        $folio = $registration->folios()->where('status', 'open')->first();

        if (! $folio) {
            $folio = Folio::create([
                'registration_id' => $registration->id,
                'folio_number' => Folio::generateFolioNumber(),
                'folio_name' => 'Main Folio',
                'status' => 'open',
                'balance' => 0,
                'created_by' => $createdBy,
            ]);
        }

        return $folio;
    }

    public function postCharge(Folio $folio, array $data, ?int $postedBy = null): FolioItem
    {
        $totalTax = 0;

        if (! empty($data['tax_code']) && ! empty($data['tax_rate'])) {
            if ($data['tax_type'] ?? 'exclusive' === 'exclusive') {
                $totalTax = ($data['amount'] * $data['tax_rate']) / 100;
            }
        }

        $item = FolioItem::create([
            'folio_id' => $folio->id,
            'sourceable_type' => $data['sourceable_type'] ?? null,
            'sourceable_id' => $data['sourceable_id'] ?? null,
            'charge_type' => $data['charge_type'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'tax_code' => $data['tax_code'] ?? null,
            'tax_rate' => $data['tax_rate'] ?? 0,
            'tax_amount' => $totalTax,
            'post_date' => $data['post_date'] ?? now(),
            'posted_by' => $postedBy,
        ]);

        $folio->increment('balance', (float) $data['amount'] + $totalTax);

        return $item;
    }

    public function splitFolio(Folio $sourceFolio, string $newFolioName, array $itemIds, ?int $createdBy = null): Folio
    {
        return DB::transaction(function () use ($sourceFolio, $newFolioName, $itemIds, $createdBy) {
            $newFolio = Folio::create([
                'registration_id' => $sourceFolio->registration_id,
                'folio_number' => Folio::generateFolioNumber(),
                'folio_name' => $newFolioName,
                'status' => 'open',
                'balance' => 0,
                'created_by' => $createdBy,
            ]);

            $items = FolioItem::whereIn('id', $itemIds)->where('folio_id', $sourceFolio->id)->get();
            $totalMoved = 0;

            foreach ($items as $item) {
                $item->update(['folio_id' => $newFolio->id]);
                $totalMoved += (float) $item->amount + (float) $item->tax_amount;
            }

            $sourceFolio->decrement('balance', $totalMoved);
            $newFolio->increment('balance', $totalMoved);

            return $newFolio;
        });
    }

    public function closeFolio(Folio $folio): Folio
    {
        $folio->update(['status' => 'closed']);

        return $folio;
    }

    public function voidFolio(Folio $folio): Folio
    {
        $folio->update(['status' => 'void', 'balance' => 0]);
        $folio->items()->delete();

        return $folio;
    }

    public function getOutstandingBalance(Registration $registration): float
    {
        $totalCharges = $registration->charges()->sum('amount');
        $totalCharges += $registration->folios()->where('status', 'open')->sum('balance');
        $totalPaid = $registration->payments()->sum('amount');

        return max(0, $totalCharges - $totalPaid);
    }
}
