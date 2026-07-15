# MOE-Laravel-Vendor-B2B

Vendor B2B module for MOE ecosystem — Vendor, Purchase Order, Payout.

## Installation

```bash
composer require moe/laravel-vendor-b2b
php artisan vendor:publish --provider="Moe\VendorB2B\VendorB2BServiceProvider" --tag="vendor-b2b-config"
php artisan vendor:publish --provider="Moe\VendorB2B\VendorB2BServiceProvider" --tag="vendor-b2b-migrations"
php artisan migrate
```

## What's Included

### Models

| Model | Table | Description |
|-------|-------|-------------|
| `Vendor` | `vendors` | Vendor B2B |
| `PurchaseOrder` | `purchase_orders` | PO ke vendor |
| `PurchaseOrderItem` | `purchase_order_items` | Item PO |
| `VendorPayout` | `vendor_payouts` | Pengajuan pencairan |
| `VendorDocument` | `vendor_documents` | Dokumen vendor |

### Services

| Service | Description |
|---------|-------------|
| `PurchaseOrderService` | Create, submit, approve, ship, receive, cancel PO |
| `VendorPayoutService` | Create, approve, reject, mark paid, cancel payout |

### Contracts

| Contract | Description |
|----------|-------------|
| `VendorInterface` | Interface untuk vendor |
| `PayoutableInterface` | Interface untuk payout |

## Usage

### Create PO

```php
use Moe\VendorB2B\Services\PurchaseOrderService;

$poService = app(PurchaseOrderService::class);

$po = $poService->create($vendorId, [
    [
        'product_id' => 1,
        'product_name' => 'Produk A',
        'quantity' => 100,
        'unit_price' => 10000,
    ],
], [
    'order_date' => now(),
    'expected_delivery_date' => now()->addDays(7),
]);
```

### PO Workflow

```php
$poService->submit($po);      // draft → pending
$poService->approve($po);     // pending → approved
$poService->ship($po);        // approved → shipped
$poService->receive($po, $inspectionData); // shipped → received
```

### Vendor Payout

```php
use Moe\VendorB2B\Services\VendorPayoutService;

$payoutService = app(VendorPayoutService::class);

$payout = $payoutService->create($vendorId, $poId, [
    'bank_name' => 'BCA',
    'bank_account_number' => '1234567890',
    'bank_account_holder' => 'PT Vendor ABC',
]);

$payoutService->approve($payout);
$payoutService->markPaid($payout, 'TF-20260712-001');
```

## Config

```php
// config/vendor-b2b.php
return [
    'tables' => [
        'vendors' => 'vendors',
        'purchase_orders' => 'purchase_orders',
        // ...
    ],
    'payout' => [
        'min_amount' => 50000,
    ],
];
```

## Requirements

- PHP ^8.2
- Laravel ^12.0|^13.0
- `moe/laravel-core`
- `moe/laravel-inventory`

## License

MIT
