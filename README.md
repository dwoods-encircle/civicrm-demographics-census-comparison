# TrustCRM: Membership Demographics Report (CiviCRM Extension)

Adds **Reports → Membership Demographics vs Census**.

Compares membership demographics (from selected groups) with ONS/NOMIS benchmarks, showing **% Membership**, **% Area**, and **Index**. Has a settings page to choose groups, fields, geography, dataset label, and category mappings; supports CSV import for benchmarks.

## Install
Copy `org.trustcrm.membershipdemographics` into your CiviCRM `ext/` and enable at **Administer → System Settings → Extensions**.

## Configure
**Administer → System Settings → Membership Demographics Settings**

- Member Groups to Include
- Gender Field (e.g. `gender_id`)
- Ethnicity Custom Field (e.g. `custom_12`)
- ONS/NOMIS Geography Code (e.g. `E10000007` for Derbyshire)
- Dataset Label
- Category Mappings (JSON)

### Load Benchmarks via CSV
CSV columns:
```
category_type,category_key,category_label,value,total
gender,M,Male,524112,1066400
gender,F,Female,542288,1066400
ethnicity,white_british,White - English, Welsh, Scottish, NI, British,917416,1055897
```
Rows are stored in `trustcrm_census_dataset` under your dataset label and geography code.

> You can export from ONS/NOMIS, transform to the above schema, and upload here.

## Notes
- The ethnicity field assumes a single-select custom field. Adjust query in `Page/Report.php` for other setups.
- A stub `NomisClient` is included if you want to fetch directly from NOMIS (you'll need to adapt parameters for the dataset you use).
