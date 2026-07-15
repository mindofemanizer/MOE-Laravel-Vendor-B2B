<?php
declare(strict_types=1);

namespace Moe\VendorB2B\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $table;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Draf',
        self::STATUS_PENDING => 'Menunggu Persetujuan',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_SHIPPED => 'Dikirim',
        self::STATUS_RECEIVED => 'Diterima',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    protected $fillable = [
        'po_number',
        'vendor_id',
        'status',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'subtotal',
        'tax',
        'shipping_cost',
        'total',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'qc_checked_by',
        'qc_checked_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'approved_at' => 'datetime',
        'qc_checked_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('vendor-b2b.tables.purchase_orders', 'purchase_orders');
    }

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $po) {
            if (empty($po->po_number)) {
                $po->po_number = static::generatePONumber();
            }
            if (empty($po->status)) {
                $po->status = self::STATUS_DRAFT;
            }
        });
    }

    public static function generatePONumber(): string
    {
        return 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }

    public function getRouteKeyName(): string
    {
        return 'po_number';
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(config('vendor-b2b.models.user', 'App\\Models\\User'), 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(config('vendor-b2b.models.user', 'App\\Models\\User'), 'approved_by');
    }

    public function qcCheckedBy(): BelongsTo
    {
        return $this->belongsTo(config('vendor-b2b.models.user', 'App\\Models\\User'), 'qc_checked_by');
    }

    public function vendorPayouts(): HasMany
    {
        return $this->hasMany(VendorPayout::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-surface-variant text-on-surface-variant',
            self::STATUS_PENDING => 'bg-tertiary-fixed text-on-tertiary-fixed-variant',
            self::STATUS_APPROVED => 'bg-primary-container/20 text-primary',
            self::STATUS_SHIPPED => 'bg-secondary-container text-on-secondary-container',
            self::STATUS_RECEIVED => 'bg-secondary-container text-on-secondary-container',
            self::STATUS_CANCELLED => 'bg-error-container text-on-error',
            default => 'bg-surface-variant text-on-surface-variant',
        };
    }

    /**
     * Check if the purchase order is editable.
     */
    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if the purchase order can be submitted.
     */
    public function canSubmit(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->items->isNotEmpty();
    }

    /**
     * Check if the purchase order can be approved.
     */
    public function canApprove(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the purchase order can be shipped.
     */
    public function canShip(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the purchase order can be received.
     */
    public function canReceive(): bool
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    /**
     * Check if the purchase order can be cancelled.
     */
    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_APPROVED]);
    }
}
