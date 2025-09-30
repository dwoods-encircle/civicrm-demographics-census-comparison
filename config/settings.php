<?php

use Civicrm\DemographicsCensusComparison\Settings\FrequencyUnit;

return [
    'audit_log' => [
        'path' => __DIR__ . '/../data/audit_log.sqlite',
        'retention_days' => 365,
    ],
    'custom_data_cleanup' => [
        'enabled' => false,
        'interval' => 1,
        'unit' => FrequencyUnit::MONTHS,
    ],
    'entities' => [
        [
            'entity' => 'Individual',
            'interval' => 1,
            'unit' => FrequencyUnit::MONTHS,
        ],
        [
            'entity' => 'Household',
            'interval' => 1,
            'unit' => FrequencyUnit::MONTHS,
        ],
        [
            'entity' => 'Organization',
            'interval' => 1,
            'unit' => FrequencyUnit::MONTHS,
        ],
        [
            'entity' => 'Membership',
            'interval' => 1,
            'unit' => FrequencyUnit::MONTHS,
        ],
    ],
];
