<?php
declare(strict_types=1);

namespace Moe\VendorB2B\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $table;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_name',
        'quantity',
        'received_quantity',
        'rejected_quantity',
        'unit',
        'unit_price',
        'subtotal',
        'qc_notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_quantity' => 'integer',
        'rejected_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('vendor-b2b.tables.purchase_order_items', 'purchase_order_items');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(config('vendor-b2b.models.product', 'App\\Models\\Product'));
    }
}
