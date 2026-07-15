<?php

namespace Moe\VendorB2B\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Moe\VendorB2B\Contracts\PayoutableInterface;

class VendorPayout extends Model implements PayoutableInterface
{
    use SoftDeletes;

    protected $table;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID = 'paid';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    const STATUS_LABELS = [
        self::STATUS_PENDING => 'Menunggu',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_PAID => 'Dibayar',
        self::STATUS_REJECTED => 'Ditolak',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    protected $fillable = [
        'payout_number',
        'vendor_id',
        'purchase_order_id',
        'amount',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'notes',
        'admin_notes',
        'proof_url',
        'requested_at',
        'approved_at',
        'paid_at',
        'rejected_at',
        'cancelled_at',
        'approved_by',
        'paid_by',
        'transfer_reference',
    ];

    protected $hidden = [
        'bank_account_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('vendor-b2b.tables.vendor_payouts', 'vendor_payouts');
    }

    protected static function booted(): void
    {
        static::creating(function (VendorPayout $payout) {
            if (empty($payout->payout_number)) {
                $payout->payout_number = static::generatePayoutNumber();
            }
            if (empty($payout->requested_at)) {
                $payout->requested_at = now();
            }
        });
    }

    public static function generatePayoutNumber(): string
    {
        return 'PYT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function approver()
    {
        return $this->belongsTo(config('vendor-b2b.models.user', 'App\\Models\\User'), 'approved_by');
    }

    public function payer()
    {
        return $this->belongsTo(config('vendor-b2b.models.user', 'App\\Models\\User'), 'paid_by');
    }

    // PayoutableInterface
    public function getPayoutAmount(): float
    {
        return (float) $this->amount;
    }

    public function canBePaid(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function markAsPaid(string $reference, int $paidBy): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'paid_by' => $paidBy,
            'transfer_reference' => $reference,
        ]);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-tertiary-fixed text-on-tertiary-fixed-variant',
            self::STATUS_APPROVED => 'bg-primary-container/20 text-primary',
            self::STATUS_PAID => 'bg-secondary-container text-on-secondary-container',
            self::STATUS_REJECTED => 'bg-error-container text-on-error',
            self::STATUS_CANCELLED => 'bg-surface-variant text-on-surface-variant',
            default => 'bg-surface-variant text-on-surface-variant',
        };
    }
}
