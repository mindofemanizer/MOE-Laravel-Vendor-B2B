<?php

namespace Moe\VendorB2B\Tests;

use Moe\VendorB2B\Models\Vendor;
use Moe\VendorB2B\Services\VendorService;

class VendorServiceTest extends TestCase
{
    private VendorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VendorService();
    }

    public function test_can_register_vendor()
    {
        $vendor = $this->service->register([
            'name' => 'PT Supplier Sejahtera',
            'email' => 'supplier@example.com',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        $this->assertInstanceOf(Vendor::class, $vendor);
        $this->assertEquals('PT Supplier Sejahtera', $vendor->name);
    }

    public function test_can_update_vendor_status()
    {
        $vendor = $this->service->register([
            'name' => 'PT Supplier Maju',
            'email' => 'maju@example.com',
        ]);

        $updated = $this->service->updateStatus($vendor, 'suspended');
        $this->assertEquals('suspended', $updated->fresh()->status);
    }
}
