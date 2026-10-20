<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'variant_label',
        'dimensions',
        'material',
        'sale_unit',
        'box_qty',
        'carton_qty',
        'price',
        'currency',
        'availability_status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => Product::formatSentenceCase($value),
            set: fn (?string $value): ?string => Product::formatSentenceCase($value),
        );
    }

    protected function variantLabel(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => Product::formatSentenceCase($value),
            set: fn (?string $value): ?string => Product::formatSentenceCase($value),
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function displayLabel(): string
    {
        if ($this->variant_label && trim($this->variant_label) !== '') {
            return trim($this->variant_label);
        }

        return sprintf('%s — %s', $this->sku, $this->name);
    }

    public function packagingSummary(): string
    {
        $parts = [];
        if ($this->box_qty !== null && trim((string) $this->box_qty) !== '') {
            $parts[] = 'Caja: '.trim((string) $this->box_qty);
        }
        if ($this->carton_qty !== null && trim((string) $this->carton_qty) !== '') {
            $parts[] = 'Cajón: '.trim((string) $this->carton_qty);
        }

        return implode(' • ', $parts);
    }

    public function formattedPrice(?Product $parentProduct = null, ?string $globalMode = null): ?string
    {
        if ($this->price === null || (float) $this->price <= 0) {
            return null;
        }

        $product = $parentProduct ?? ($this->relationLoaded('product') ? $this->product : null);
        if ($product && ! $product->shouldShowPriceForValue((float) $this->price, $globalMode)) {
            return null;
        }

        $symbol = match (strtoupper((string) $this->currency)) {
            'USD' => '$',
            'EUR' => '€',
            default => 'S/',
        };

        return sprintf('%s %s', $symbol, number_format((float) $this->price, 2, '.', ','));
    }

    public function availabilityLabel(): string
    {
        return match ($this->availability_status) {
            'available' => 'Disponible',
            'on_request' => 'Bajo consulta',
            'out_of_stock' => 'Sin stock',
            default => 'Consultar disponibilidad',
        };
    }

    public function availabilityBadgeClass(): string
    {
        return match ($this->availability_status) {
            'available' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'on_request' => 'bg-amber-100 text-amber-800 border-amber-200',
            'out_of_stock' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
