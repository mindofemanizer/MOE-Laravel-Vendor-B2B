<?php
declare(strict_types=1);

namespace Moe\VendorB2B\Contracts;

interface PayoutableInterface
{
    public function getPayoutAmount(): float;
    public function canBePaid(): bool;
    public function markAsPaid(string $reference, int $paidBy): void;
}
