<?php
declare(strict_types=1);

namespace Moe\VendorB2B\Services;

use Moe\Core\Base\BaseService;
use Moe\VendorB2B\Models\VendorPayout;

class VendorPayoutService extends BaseService
{
    /**
     * Create payout request.
     */
    public function create(int $vendorId, int $purchaseOrderId, array $bankDetails): VendorPayout
    {
        $po = \Moe\VendorB2B\Models\PurchaseOrder::where('vendor_id', $vendorId)
            ->where('id', $purchaseOrderId)
            ->where('status', 'received')
            ->firstOrFail();

        // Check if already has active payout
        $existingPayout = VendorPayout::where('vendor_id', $vendorId)
            ->where('purchase_order_id', $purchaseOrderId)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existingPayout) {
            throw new \Exception('PO ini sudah memiliki pengajuan pencairan aktif');
        }

        return VendorPayout::create([
            'vendor_id' => $vendorId,
            'purchase_order_id' => $purchaseOrderId,
            'amount' => $po->total,
            'bank_name' => $bankDetails['bank_name'],
            'bank_account_number' => $bankDetails['bank_account_number'],
            'bank_account_holder' => $bankDetails['bank_account_holder'],
            'notes' => $bankDetails['notes'] ?? null,
        ]);
    }

    /**
     * Approve payout.
     */
    public function approve(VendorPayout $payout): void
    {
        if ($payout->status !== VendorPayout::STATUS_PENDING) {
            throw new \Exception('Hanya pengajuan pending yang bisa disetujui');
        }

        $payout->update([
            'status' => VendorPayout::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject payout.
     */
    public function reject(VendorPayout $payout, string $reason): void
    {
        if ($payout->status !== VendorPayout::STATUS_PENDING) {
            throw new \Exception('Hanya pengajuan pending yang bisa ditolak');
        }

        $payout->update([
            'status' => VendorPayout::STATUS_REJECTED,
            'admin_notes' => $reason,
            'rejected_at' => now(),
        ]);
    }

    /**
     * Mark payout as paid.
     */
    public function markPaid(VendorPayout $payout, string $reference): void
    {
        if (! $payout->canBePaid()) {
            throw new \Exception('Pengajuan ini belum disetujui');
        }

        $payout->markAsPaid($reference, auth()->id());
    }

    /**
     * Cancel payout.
     */
    public function cancel(VendorPayout $payout): void
    {
        if ($payout->status !== VendorPayout::STATUS_PENDING) {
            throw new \Exception('Hanya pengajuan pending yang bisa dibatalkan');
        }

        $payout->update([
            'status' => VendorPayout::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }
}
