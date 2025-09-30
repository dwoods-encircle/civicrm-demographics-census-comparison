#!/usr/bin/env php
<?php
require_once __DIR__ . '/../autoload.php';

use Civicrm\DemographicsCensusComparison\Service\AuditLogger;
use Civicrm\DemographicsCensusComparison\Service\MaintenanceScheduler;
use Civicrm\DemographicsCensusComparison\Service\OrphanedDataCleaner;
use Civicrm\DemographicsCensusComparison\Settings\EntitySetting;
use Civicrm\DemographicsCensusComparison\Settings\SettingsRepository;

$config = require __DIR__ . '/../config/settings.php';
$storageFile = __DIR__ . '/../data/test_settings.json';
if (file_exists($storageFile)) {
    unlink($storageFile);
}

$repository = new SettingsRepository($storageFile, $config);
$entities = $repository->getEntitySettings();
if (count($entities) !== 4) {
    fwrite(STDERR, "Expected four default entities including Membership.\n");
    exit(1);
}

$membership = array_filter($entities, static function (EntitySetting $setting): bool {
    return $setting->getEntity() === 'Membership';
});
if (count($membership) !== 1) {
    fwrite(STDERR, "Membership entity not present in defaults.\n");
    exit(1);
}

$repository->setCustomDataCleanupConfig(true, 2, 'weeks');
$cleanupConfig = $repository->getCustomDataCleanupConfig();
if ($cleanupConfig['unit'] !== 'weeks') {
    fwrite(STDERR, "Cleanup configuration did not persist week unit.\n");
    exit(1);
}

$auditConfig = $repository->getAuditLogConfig();
$logger = new AuditLogger($auditConfig['path'], 1);
$cleaner = new OrphanedDataCleaner(
    static fn (): array => ['orphan-1', 'orphan-2'],
    static fn (array $ids): int => count($ids)
);
$scheduler = new MaintenanceScheduler($repository, $logger, $cleaner);
$scheduler->run();

// Ensure audit entries exist.
$pdo = new PDO('sqlite:' . $auditConfig['path']);
$count = (int) $pdo->query('SELECT COUNT(*) FROM audit_log')->fetchColumn();
if ($count < 2) {
    fwrite(STDERR, "Expected audit log to contain entity and cleanup records.\n");
    exit(1);
}

unlink($storageFile);
if (file_exists($auditConfig['path'])) {
    unlink($auditConfig['path']);
}

fwrite(STDOUT, "All tests passed.\n");
