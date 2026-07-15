<?php
declare(strict_types=1);

namespace Moe\VendorB2B\Services;

use Illuminate\Support\Facades\DB;
use Moe\Core\Base\BaseService;
use Moe\VendorB2B\Models\PurchaseOrder;

class PurchaseOrderService extends BaseService
{
    /**
     * Create purchase order.
     */
    public function create(int $vendorId, array $items, array $data = []): PurchaseOrder
    {
        return DB::transaction(function () use ($vendorId, $items, $data) {
            $subtotal = collect($items)->sum(fn ($item) => $item['unit_price'] * $item['quantity']);
            $tax = $subtotal * ($data['tax_rate'] ?? 0) / 100;
            $shippingCost = $data['shipping_cost'] ?? 0;

            $po = PurchaseOrder::create([
                'vendor_id' => $vendorId,
                'order_date' => $data['order_date'] ?? now(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'total' => $subtotal + $tax + $shippingCost,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $item) {
                $po->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'received_quantity' => 0,
                    'rejected_quantity' => 0,
                    'unit' => $item['unit'] ?? 'pcs',
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['unit_price'] * $item['quantity'],
                ]);
            }

            return $po;
        });
    }

    /**
     * Submit PO for approval.
     *
     * @throws \Exception
     */
    public function submit(PurchaseOrder $po): void
    {
        if (! $po->canSubmit()) {
            throw new \Exception('PO tidak dapat diajukan');
        }

        $po->update(['status' => PurchaseOrder::STATUS_PENDING]);
    }

    /**
     * Approve PO.
     *
     * @throws \Exception
     */
    public function approve(PurchaseOrder $po): void
    {
        if (! $po->canApprove()) {
            throw new \Exception('PO tidak dapat disetujui');
        }

        $po->update([
            'status' => PurchaseOrder::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Ship PO.
     *
     * @throws \Exception
     */
    public function ship(PurchaseOrder $po): void
    {
        if (! $po->canShip()) {
            throw new \Exception('PO tidak dapat dikirim');
        }

        $po->update(['status' => PurchaseOrder::STATUS_SHIPPED]);
    }

    /**
     * Receive PO with inspection.
     *
     * @throws \Exception
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function receive(PurchaseOrder $po, array $inspectionData): void
    {
        if (! $po->canReceive()) {
            throw new \Exception('PO tidak dapat diterima');
        }

        DB::transaction(function () use ($po, $inspectionData) {
            foreach ($inspectionData as $itemData) {
                $item = $po->items()->findOrFail($itemData['item_id']);

                $item->update([
                    'received_quantity' => $itemData['received_quantity'],
                    'rejected_quantity' => $itemData['rejected_quantity'] ?? 0,
                    'qc_notes' => $itemData['qc_notes'] ?? null,
                ]);

                if ($itemData['received_quantity'] > 0) {
                    $product = $item->product;
                    if ($product && $product->inventory) {
                        $product->inventory->increment('quantity', $itemData['received_quantity']);
                    }
                }
            }

            $po->update([
                'status' => PurchaseOrder::STATUS_RECEIVED,
                'actual_delivery_date' => now(),
                'qc_checked_by' => auth()->id(),
                'qc_checked_at' => now(),
            ]);
        });
    }

    /**
     * Cancel PO.
     *
     * @throws \Exception
     */
    public function cancel(PurchaseOrder $po, string $reason): void
    {
        if (! $po->canCancel()) {
            throw new \Exception('PO tidak dapat dibatalkan');
        }

        $po->update([
            'status' => PurchaseOrder::STATUS_CANCELLED,
            'notes' => $reason,
        ]);
    }
}
