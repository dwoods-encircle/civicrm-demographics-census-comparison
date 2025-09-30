<?php

namespace Civicrm\DemographicsCensusComparison\Service;

use Civicrm\DemographicsCensusComparison\Settings\EntitySetting;
use Civicrm\DemographicsCensusComparison\Settings\SettingsRepository;

/**
 * Coordinates scheduled maintenance actions for the extension.
 */
final class MaintenanceScheduler
{
    private SettingsRepository $settingsRepository;
    private AuditLogger $logger;
    private OrphanedDataCleaner $dataCleaner;

    public function __construct(SettingsRepository $settingsRepository, AuditLogger $logger, OrphanedDataCleaner $dataCleaner)
    {
        $this->settingsRepository = $settingsRepository;
        $this->logger = $logger;
        $this->dataCleaner = $dataCleaner;
    }

    public function run(): void
    {
        foreach ($this->settingsRepository->getEntitySettings() as $setting) {
            $this->processEntity($setting);
        }

        $this->maybeCleanupCustomData();

        $purged = $this->logger->purgeExpired();
        if ($purged > 0) {
            $this->logger->log('audit_log.purge', ['removed' => $purged]);
        }
    }

    private function processEntity(EntitySetting $setting): void
    {
        $this->logger->log('entity.evaluate', [
            'entity' => $setting->getEntity(),
            'interval' => $setting->getInterval(),
            'unit' => $setting->getUnit(),
        ]);
    }

    private function maybeCleanupCustomData(): void
    {
        $config = $this->settingsRepository->getCustomDataCleanupConfig();
        if (!$config['enabled']) {
            return;
        }

        $deleted = $this->dataCleaner->cleanup();
        $this->logger->log('custom_data.cleanup', [
            'deleted' => $deleted,
            'interval' => $config['interval'],
            'unit' => $config['unit'],
        ]);
    }
}
