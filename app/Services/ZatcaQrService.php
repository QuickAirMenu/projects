<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class ZatcaQrService
{
    private ?array $settings = null;

    public function vatNumber(): string
    {
        return (string) ($this->settings()['vat_number'] ?? '');
    }

    public function sellerName(): string
    {
        $name = $this->settings()['company_name_ar'] ?? null;

        if (blank($name)) {
            $name = $this->settings()['company_name'] ?? null;
        }

        return mb_substr((string) ($name ?: config('app.name')), 0, 100);
    }

    public function payload(Invoice $invoice): string
    {
        $issuedAt = $invoice->created_at ?? $invoice->invoice_date ?? now();

        $tlv = $this->tlv(1, $this->sellerName())
            .$this->tlv(2, $this->vatNumber())
            .$this->tlv(3, $issuedAt->toIso8601String())
            .$this->tlv(4, number_format((float) $invoice->total, 2, '.', ''))
            .$this->tlv(5, number_format((float) $invoice->tax_amount, 2, '.', ''));

        return base64_encode($tlv);
    }

    public function svgDataUri(Invoice $invoice): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(180, 1), new SvgImageBackEnd));

        return 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($this->payload($invoice)));
    }

    protected function tlv(int $tag, string $value): string
    {
        $value = mb_substr($value, 0, 255);

        return chr($tag).chr(strlen($value)).$value;
    }

    protected function settings(): array
    {
        if ($this->settings === null) {
            $this->settings = Setting::query()
                ->whereIn('key', ['company_name', 'company_name_ar', 'vat_number'])
                ->pluck('value', 'key')
                ->toArray();
        }

        return $this->settings;
    }
}
