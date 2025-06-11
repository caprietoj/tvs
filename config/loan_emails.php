<?php

return [
    'hr' => env('HR_EMAIL', 'recursoshumanos@tvs.edu.co'),
    'finance' => env('FINANCE_EMAIL', 'administrativedirector@tvs.edu.co'),
    'accounting' => env('ACCOUNTING_EMAIL', 'contabilidad@tvs.edu.co'),
    'treasury' => env('TREASURY_EMAIL', 'tesoreria@tvs.edu.co'),
    'notifications' => [
        env('HR_EMAIL', 'recursoshumanos@tvs.edu.co'),
        env('FINANCE_EMAIL', 'administrativedirector@tvs.edu.co'),
        env('ACCOUNTING_EMAIL', 'contabilidad@tvs.edu.co'),
        env('TREASURY_EMAIL', 'tesoreria@tvs.edu.co'),
    ],
];
