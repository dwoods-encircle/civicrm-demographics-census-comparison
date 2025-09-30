<?php

namespace Civicrm\DemographicsCensusComparison\Service;

use PDO;
use PDOException;

/**
 * Persists a record of all extension actions.
 */
final class AuditLogger
{
    private PDO $pdo;
    private int $retentionDays;

    public function __construct(string $databasePath, int $retentionDays)
    {
        $directory = dirname($databasePath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('Unable to create audit log directory "%s".', $directory));
            }
        }

        $dsn = 'sqlite:' . $databasePath;
        try {
            $this->pdo = new PDO($dsn);
        } catch (PDOException $e) {
            throw new \RuntimeException('Unable to open audit log database: ' . $e->getMessage(), 0, $e);
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->retentionDays = max(1, $retentionDays);
        $this->initialise();
    }

    private function initialise(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS audit_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            action TEXT NOT NULL,
            details TEXT,
            created_at DATETIME NOT NULL
        )');
    }

    public function log(string $action, array $details = []): void
    {
        $statement = $this->pdo->prepare('INSERT INTO audit_log (action, details, created_at) VALUES (:action, :details, :created_at)');
        $statement->execute([
            ':action' => $action,
            ':details' => json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ':created_at' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('c'),
        ]);
    }

    public function purgeExpired(): int
    {
        $threshold = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->modify(sprintf('-%d days', $this->retentionDays))
            ->format('c');

        $statement = $this->pdo->prepare('DELETE FROM audit_log WHERE created_at < :threshold');
        $statement->execute([':threshold' => $threshold]);

        return $statement->rowCount();
    }

    public function setRetentionDays(int $days): void
    {
        if ($days < 1) {
            throw new \InvalidArgumentException('Retention must be at least 1 day.');
        }
        $this->retentionDays = $days;
    }
}
