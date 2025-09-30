<?php

namespace Civicrm\DemographicsCensusComparison\Settings;

use Civicrm\DemographicsCensusComparison\Exception\InvalidConfigurationException;

/**
 * Storage abstraction for extension configuration.
 */
final class SettingsRepository
{
    private string $storageFile;
    private array $defaultConfig;

    public function __construct(string $storageFile, array $defaultConfig)
    {
        $this->storageFile = $storageFile;
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * @return EntitySetting[]
     */
    public function getEntitySettings(): array
    {
        $config = $this->load();
        $entityConfigs = $config['entities'] ?? [];
        $result = [];
        foreach ($entityConfigs as $entityConfig) {
            $result[] = EntitySetting::fromArray($entityConfig);
        }

        return $result;
    }

    public function saveEntitySettings(array $settings): void
    {
        $config = $this->load();
        $config['entities'] = [];
        foreach ($settings as $setting) {
            if (!$setting instanceof EntitySetting) {
                throw new InvalidConfigurationException('Invalid entity setting provided.');
            }
            $config['entities'][] = $setting->toArray();
        }
        $this->persist($config);
    }

    public function getCustomDataCleanupConfig(): array
    {
        $config = $this->load();
        $cleanup = $config['custom_data_cleanup'] ?? [];
        $cleanup['enabled'] = (bool)($cleanup['enabled'] ?? false);
        $cleanup['interval'] = max(1, (int)($cleanup['interval'] ?? 1));
        $cleanup['unit'] = $cleanup['unit'] ?? FrequencyUnit::MONTHS;
        if (!FrequencyUnit::isValid($cleanup['unit'])) {
            throw new InvalidConfigurationException('Invalid frequency unit for custom data cleanup.');
        }

        return $cleanup;
    }

    public function setCustomDataCleanupConfig(bool $enabled, int $interval, string $unit): void
    {
        if ($interval < 1) {
            throw new InvalidConfigurationException('Cleanup interval must be greater than or equal to 1.');
        }
        if (!FrequencyUnit::isValid($unit)) {
            throw new InvalidConfigurationException('Invalid frequency unit for custom data cleanup.');
        }

        $config = $this->load();
        $config['custom_data_cleanup'] = [
            'enabled' => $enabled,
            'interval' => $interval,
            'unit' => strtolower($unit),
        ];
        $this->persist($config);
    }

    public function getAuditLogConfig(): array
    {
        $config = $this->load();
        $audit = $config['audit_log'] ?? [];
        $audit['path'] = $audit['path'] ?? $this->defaultConfig['audit_log']['path'];
        $audit['retention_days'] = max(1, (int)($audit['retention_days'] ?? $this->defaultConfig['audit_log']['retention_days']));

        return $audit;
    }

    public function setAuditLogRetentionDays(int $retentionDays): void
    {
        if ($retentionDays < 1) {
            throw new InvalidConfigurationException('Audit log retention must be at least 1 day.');
        }
        $config = $this->load();
        if (!isset($config['audit_log'])) {
            $config['audit_log'] = [];
        }
        $config['audit_log']['retention_days'] = $retentionDays;
        $this->persist($config);
    }

    private function load(): array
    {
        if (!file_exists($this->storageFile)) {
            return $this->defaultConfig;
        }

        $json = file_get_contents($this->storageFile);
        if ($json === false || $json === '') {
            return $this->defaultConfig;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new InvalidConfigurationException('Unable to decode configuration file.');
        }

        return array_replace_recursive($this->defaultConfig, $data);
    }

    private function persist(array $config): void
    {
        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Unable to create configuration directory "%s".', $dir));
            }
        }

        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new InvalidConfigurationException('Unable to encode configuration file.');
        }

        if (file_put_contents($this->storageFile, $json . PHP_EOL) === false) {
            throw new \RuntimeException(sprintf('Unable to write configuration file "%s".', $this->storageFile));
        }
    }
}
