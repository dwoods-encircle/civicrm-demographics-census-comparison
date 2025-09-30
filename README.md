# civicrm-demographics-census-comparison

This repository contains support tooling for managing the Demographics Census Comparison extension.

## Configuration

All configurable options are stored in `config/settings.php`. When runtime changes are made they will be persisted to `data/settings.json`.

### Entity schedules

Each entity can now be evaluated on a cadence expressed in **days**, **weeks**, **months**, or **years**. The default configuration includes Individual, Household, Organization, and the newly supported Membership entity. Custom cadences can be applied by updating the interval and unit in the configuration file or persisted JSON settings file.

### Custom data maintenance

Set `custom_data_cleanup.enabled` to `true` to periodically remove orphaned custom data. The cleanup interval and unit use the same day/week/month/year options as entity schedules.

### Audit logging

Every action performed by the maintenance scheduler is written to an SQLite-backed audit log (`data/audit_log.sqlite`). The retention period is configurable through the `audit_log.retention_days` option, and expired records are pruned each time the scheduler executes. The schema used to initialise the table is mirrored in `sql/audit_log_table.sql` for environments that prefer to manage the database manually.

## Running maintenance tasks

Execute the maintenance runner to process entity schedules, clean up orphaned data (when enabled), and rotate the audit log:

```bash
bin/run-maintenance
```
