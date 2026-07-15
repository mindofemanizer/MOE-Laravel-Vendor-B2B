<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vendor-B2B depends on the host application's vendor/PO/payout tables.
        // These are owned by the application (KiosKit), so we only create them
        // when they do not already exist (idempotent for shared/monolith setups).
        if (!Schema::hasTable('vendors')) {
            Schema::create('vendors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('vendor_code', 20)->unique();
                $table->string('business_type', 50)->default('supplier');
                $table->text('description')->nullable();
                $table->string('logo')->nullable();
                $table->text('address')->nullable();
                $table->string('contact_name')->nullable();
                $table->string('contact_phone')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('website')->nullable();
                $table->string('tax_id')->nullable();
                $table->string('registration_number')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('bank_account_holder')->nullable();
                $table->timestamp('bank_verified_at')->nullable();
                $table->foreignId('bank_verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->decimal('rating', 3, 2)->default(0);
                $table->integer('total_products')->default(0);
                $table->decimal('commission_rate', 5, 2)->default(0);
                $table->string('payment_term')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number')->unique();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('status', 50)->default('draft');
                $table->date('order_date')->nullable();
                $table->date('expected_delivery_date')->nullable();
                $table->date('actual_delivery_date')->nullable();
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax', 15, 2)->default(0);
                $table->decimal('shipping_cost', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('qc_checked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('qc_checked_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['vendor_id', 'status']);
            });
        }

        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->string('product_name');
                $table->integer('quantity');
                $table->integer('received_quantity')->default(0);
                $table->integer('rejected_quantity')->default(0);
                $table->string('unit', 20)->default('pcs');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('subtotal', 15, 2);
                $table->text('qc_notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('vendor_payouts')) {
            Schema::create('vendor_payouts', function (Blueprint $table) {
                $table->id();
                $table->string('payout_number')->unique();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
                $table->decimal('amount', 15, 2);
                $table->string('status', 50)->default('pending');
                $table->string('bank_name')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('bank_account_holder')->nullable();
                $table->text('notes')->nullable();
                $table->text('admin_notes')->nullable();
                $table->string('proof_url')->nullable();
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('transfer_reference')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['vendor_id', 'status']);
            });
        }

        if (!Schema::hasTable('vendor_documents')) {
            Schema::create('vendor_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('type', 50);
                $table->string('name');
                $table->string('file_path');
                $table->text('notes')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Tables are owned by the host application; only drop when this package
        // created them (i.e. they did not exist before this migration ran).
        if (Schema::hasTable('vendor_documents')) {
            Schema::dropIfExists('vendor_documents');
        }
        if (Schema::hasTable('vendor_payouts')) {
            Schema::dropIfExists('vendor_payouts');
        }
        if (Schema::hasTable('purchase_order_items')) {
            Schema::dropIfExists('purchase_order_items');
        }
        if (Schema::hasTable('purchase_orders')) {
            Schema::dropIfExists('purchase_orders');
        }
        if (Schema::hasTable('vendors')) {
            Schema::dropIfExists('vendors');
        }
    }
};
