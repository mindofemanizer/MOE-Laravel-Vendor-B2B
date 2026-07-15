<?php

namespace Moe\VendorB2B\Tests;

use Moe\VendorB2B\Models\PurchaseOrder;
use Moe\VendorB2B\Models\Vendor;

class VendorServiceTest extends TestCase
{
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $userClass = config('vendor-b2b.models.user');
        $this->user = $userClass::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('secret')]);
    }

    public function test_can_create_vendor()
    {
        $vendor = Vendor::create([
            'user_id' => $this->user->id,
            'name' => 'PT Supplier Sejahtera',
            'business_type' => 'supplier',
        ]);

        $this->assertInstanceOf(Vendor::class, $vendor);
        $this->assertEquals('PT Supplier Sejahtera', $vendor->name);
    }

    public function test_can_create_purchase_order()
    {
        $vendor = Vendor::create(['user_id' => $this->user->id, 'name' => 'PT Supplier', 'business_type' => 'supplier']);

        $po = PurchaseOrder::create([
            'vendor_id' => $vendor->id,
            'subtotal' => 100000,
            'tax' => 11000,
            'total' => 111000,
            'status' => 'draft',
        ]);

        $this->assertInstanceOf(PurchaseOrder::class, $po);
        $this->assertEquals('draft', $po->status);
    }

    public function test_can_approve_purchase_order()
    {
        $vendor = Vendor::create(['user_id' => $this->user->id, 'name' => 'PT Supplier', 'business_type' => 'supplier']);

        $po = PurchaseOrder::create([
            'vendor_id' => $vendor->id,
            'subtotal' => 50000,
            'tax' => 5500,
            'total' => 55500,
            'status' => 'draft',
        ]);

        $po->update(['status' => 'approved']);
        $this->assertEquals('approved', $po->fresh()->status);
    }
}
