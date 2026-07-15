<?php
declare(strict_types=1);

namespace Moe\VendorB2B\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Moe\VendorB2B\Contracts\VendorInterface;

class Vendor extends Model implements VendorInterface
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'vendor_code',
        'business_type',
        'description',
        'logo',
        'address',
        'contact_name',
        'contact_phone',
        'contact_email',
        'website',
        'tax_id',
        'registration_number',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'bank_verified_at',
        'bank_verified_by',
        'is_active',
        'rating',
        'total_products',
        'commission_rate',
        'payment_term',
    ];

    protected $hidden = [
        'bank_account_number',
    ];

    protected $casts = [
        'bank_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'rating' => 'decimal:2',
        'total_products' => 'integer',
        'commission_rate' => 'decimal:2',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('vendor-b2b.tables.vendors', 'vendors');
    }

    protected static function booted(): void
    {
        static::creating(function (Vendor $vendor) {
            if (empty($vendor->vendor_code)) {
                $vendor->vendor_code = static::generateVendorCode();
            }
            if (empty($vendor->slug)) {
                $vendor->slug = Str::slug($vendor->name);
            }
        });
    }

    public static function generateVendorCode(): string
    {
        return 'VND-' . strtoupper(Str::random(6));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('vendor-b2b.models.user', 'App\\Models\\User'));
    }

    public function products(): HasMany
    {
        return $this->hasMany(config('vendor-b2b.models.product', 'App\\Models\\Product'));
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(VendorPayout::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VendorDocument::class);
    }

    // VendorInterface
    public function getVendorCode(): string
    {
        return $this->vendor_code;
    }

    public function getBusinessType(): string
    {
        return $this->business_type;
    }

    public function isVerified(): bool
    {
        return $this->bank_verified_at !== null;
    }

    public function canRequestPayout(): bool
    {
        return $this->isVerified() && $this->is_active;
    }

    public function getLogoUrlAttribute(): ?string
    {
        $logo = $this->logo;
        if (empty($logo)) {
            return null;
        }
        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            return $logo;
        }
        try {
            return \Illuminate\Support\Facades\Storage::disk(config('filesystems.default'))->url($logo);
        } catch (\Throwable) {
            return $logo;
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function getBusinessTypeLabelAttribute(): string
    {
        return match($this->business_type) {
            'supplier' => 'Supplier',
            'distributor' => 'Distributor',
            'manufacturer' => 'Manufaktur',
            'reseller' => 'Reseller',
            default => ucfirst($this->business_type),
        };
    }
}
