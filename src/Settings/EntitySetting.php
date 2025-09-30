<?php

namespace Civicrm\DemographicsCensusComparison\Settings;

use Civicrm\DemographicsCensusComparison\Exception\InvalidConfigurationException;

/**
 * Value object describing how often an entity should be evaluated.
 */
final class EntitySetting
{
    private string $entity;
    private int $interval;
    private string $unit;

    public function __construct(string $entity, int $interval, string $unit)
    {
        $entity = trim($entity);
        if ($entity === '') {
            throw new InvalidConfigurationException('Entity name cannot be empty.');
        }

        if ($interval < 1) {
            throw new InvalidConfigurationException('Interval must be greater than or equal to 1.');
        }

        if (!FrequencyUnit::isValid($unit)) {
            throw new InvalidConfigurationException(sprintf('Unsupported frequency unit "%s".', $unit));
        }

        $this->entity = $entity;
        $this->interval = $interval;
        $this->unit = strtolower($unit);
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function toArray(): array
    {
        return [
            'entity' => $this->entity,
            'interval' => $this->interval,
            'unit' => $this->unit,
        ];
    }

    public static function fromArray(array $input): self
    {
        if (!isset($input['entity'], $input['interval'], $input['unit'])) {
            throw new InvalidConfigurationException('Entity setting must include entity, interval, and unit.');
        }

        return new self((string) $input['entity'], (int) $input['interval'], (string) $input['unit']);
    }
}
