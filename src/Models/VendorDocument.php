<?php
declare(strict_types=1);

namespace Moe\VendorB2B\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorDocument extends Model
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'vendor_id',
        'type',
        'name',
        'file_path',
        'notes',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('vendor-b2b.tables.vendor_documents', 'vendor_documents');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
