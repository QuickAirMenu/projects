<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Quote;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function createQuote(array $data): Quote
    {
        return DB::transaction(function () use ($data) {
            $data['created_by']   = Auth::id();
            $data['quote_number'] = $this->generateQuoteNumber();

            $subtotal       = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $taxPercentage  = $data['tax_percentage'] ?? 0;
            $discountPercentage = $data['discount_percentage'] ?? 0;
            $taxAmount      = round($subtotal * $taxPercentage / 100, 2);
            $discountAmount = round($subtotal * $discountPercentage / 100, 2);
            $total          = $subtotal + $taxAmount - $discountAmount;

            $quote = Quote::create(array_merge($data, [
                'subtotal'         => $subtotal,
                'tax_amount'       => $taxAmount,
                'discount_amount'  => $discountAmount,
                'total'            => $total,
            ]));

            foreach ($data['items'] as $order => $item) {
                $quote->items()->create([
                    'description'     => $item['description'],
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'total'           => $item['quantity'] * $item['unit_price'],
                    'service_type_id' => $item['service_type_id'] ?? null,
                    'sort_order'      => $order,
                ]);
            }

            return $quote;
        });
    }

    /**
     * Update a quote and rebuild its line items atomically.
     */
    public function updateQuote(Quote $quote, array $data): Quote
    {
        return DB::transaction(function () use ($quote, $data) {
            $quote->items()->delete();

            $subtotal       = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $taxPercentage  = $data['tax_percentage'] ?? $quote->tax_percentage;
            $discountPercentage = $data['discount_percentage'] ?? $quote->discount_percentage;
            $taxAmount      = round($subtotal * $taxPercentage / 100, 2);
            $discountAmount = round($subtotal * $discountPercentage / 100, 2);
            $total          = $subtotal + $taxAmount - $discountAmount;

            $quote->update(array_merge(array_diff_key($data, ['items' => '']), [
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total' => $total,
            ]));

            foreach ($data['items'] as $order => $item) {
                $quote->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'service_type_id' => $item['service_type_id'] ?? null,
                    'sort_order' => $order,
                ]);
            }

            return $quote;
        });
    }

    /**
     * Convert an approved quote into a new project.
     */
    public function convertToProject(Quote $quote, array $extra): Project
    {
        return DB::transaction(function () use ($quote, $extra) {
            $projectService = app(ProjectService::class);
            $extra = Arr::only($extra, ['status_id', 'service_type_id', 'manager_id']);

            $project = $projectService->createProject(array_merge([
                'client_id'   => $quote->client_id,
                'name'        => $quote->subject,
                'description' => $quote->description,
                'budget'      => $quote->total,
                'priority'    => 'medium',
            ], $extra));

            $quote->update(['converted_to_project_id' => $project->id]);

            return $project;
        });
    }

    protected function generateQuoteNumber(): string
    {
        $prefix = 'QT-' . date('Ym') . '-';
        $last   = Quote::where('quote_number', 'like', $prefix . '%')
            ->orderByDesc('quote_number')->first();
        $seq = $last ? (int) substr($last->quote_number, -4) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
