<?php

namespace Moe\VendorB2B\Contracts;

interface VendorInterface
{
    public function getVendorCode(): string;
    public function getBusinessType(): string;
    public function isVerified(): bool;
    public function canRequestPayout(): bool;
}
