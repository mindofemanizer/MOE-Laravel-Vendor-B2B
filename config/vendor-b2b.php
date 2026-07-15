<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Model Bindings
    |--------------------------------------------------------------------------
    */

    'models' => [

        'user' => 'App\Models\User',

        'product' => 'App\Models\Product',

    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    */

    'tables' => [

        'vendors' => 'vendors',

        'purchase_orders' => 'purchase_orders',

        'purchase_order_items' => 'purchase_order_items',

        'vendor_payouts' => 'vendor_payouts',

        'vendor_documents' => 'vendor_documents',

    ],

    /*
    |--------------------------------------------------------------------------
    | Vendor Business Types
    |--------------------------------------------------------------------------
    */

    'business_types' => [

        'supplier' => 'Supplier',
        'distributor' => 'Distributor',
        'manufacturer' => 'Manufaktur',
        'reseller' => 'Reseller',

    ],

    /*
    |--------------------------------------------------------------------------
    | Payout Settings
    |--------------------------------------------------------------------------
    */

    'payout' => [

        'min_amount' => env('VENDOR_PAYOUT_MIN', 50000),

        'auto_approve' => false,

    ],

];
