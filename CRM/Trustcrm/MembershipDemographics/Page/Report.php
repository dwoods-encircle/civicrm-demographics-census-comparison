<?php

use CRM_Trustcrm_MembershipDemographics_ExtensionUtil as E;

class CRM_Trustcrm_MembershipDemographics_Page_Report extends CRM_Core_Page {
  public function run() {
    CRM_Utils_System::setTitle(E::ts('Membership Demographics vs Census'));
    $settings = [
      'groups' => (array) Civi::settings()->get('trustcrm_md_member_group_ids'),
      'gender_field' => Civi::settings()->get('trustcrm_md_gender_field') ?: 'gender_id',
      'ethnicity_field' => Civi::settings()->get('trustcrm_md_ethnicity_field'),
      'geo_code' => Civi::settings()->get('trustcrm_md_geography_code'),
      'dataset' => Civi::settings()->get('trustcrm_md_dataset') ?: 'census2021',
      'mappings' => json_decode(Civi::settings()->get('trustcrm_md_category_mappings') ?: "{}", TRUE) ?: [],
    ];

    // Aggregate membership counts
    $memberWhere = "";
    if (!empty($settings['groups'])) {
      $in = implode(',', array_map('intval', $settings['groups']));
      $memberWhere = " AND EXISTS (SELECT 1 FROM civicrm_group_contact gc WHERE gc.contact_id = c.id AND gc.group_id IN ($in) AND gc.status = 'Added') ";
    }

    // Gender aggregation
    $genderField = $settings['gender_field'];
    $sqlGender = "SELECT IFNULL($genderField, 'Unknown') val, COUNT(*) cnt
                  FROM civicrm_contact c
                  WHERE c.is_deleted = 0 AND c.contact_type = 'Individual' $memberWhere
                  GROUP BY val";
    $genders = [];
    $totalMembers = 0;
    $dao = CRM_Core_DAO::executeQuery($sqlGender);
    while ($dao->fetch()) {
      $genders[$dao->val] = (int)$dao->cnt;
      $totalMembers += (int)$dao->cnt;
    }

    // Ethnicity aggregation (custom field)
    $eth = [];
    if (!empty($settings['ethnicity_field'])) {
      $ethField = $settings['ethnicity_field'];
      $sqlEth = "SELECT IFNULL($ethField, 'Unknown') val, COUNT(*) cnt
                 FROM civicrm_contact c
                 LEFT JOIN civicrm_value_% ON c.id = %s
                 WHERE c.is_deleted = 0 AND c.contact_type = 'Individual' $memberWhere
                 GROUP BY val";

      // Try to detect custom group table from token like custom_12
      $ethToken = $ethField;
      if (preg_match('/custom_(\d+)/', $ethToken, $m)) {
        $cfId = (int)$m[1];
        $cf = civicrm_api3('CustomField', 'getsingle', ['id' => $cfId]);
        $cg = civicrm_api3('CustomGroup', 'getsingle', ['id' => $cf['custom_group_id']]);
        $table = $cg['table_name'];
        $col = $cf['column_name'];
        // replace placeholders
        $sqlEth = str_replace('civicrm_value_%', $table, $sqlEth);
        $sqlEth = str_replace('%s', ' ' . $table . '.entity_id', $sqlEth);
        $sqlEth = str_replace($ethField, $table . '.' . $col, $sqlEth);
        $dao2 = CRM_Core_DAO::executeQuery($sqlEth);
        while ($dao2->fetch()) {
          $eth[$dao2->val] = (int)$dao2->cnt;
        }
      }
    }

    // Load census benchmarks
    $bench = [
      'gender' => [],
      'ethnicity' => [],
    ];
    $geo = $settings['geo_code'];
    $dataset = $settings['dataset'];
    $dao3 = CRM_Core_DAO::executeQuery("SELECT category_type, category_key, category_label, value, total
      FROM trustcrm_census_dataset WHERE dataset = %1 AND geography_code = %2", [
        1 => [$dataset, 'String'],
        2 => [$geo, 'String'],
    ]);
    while ($dao3->fetch()) {
      $pct = $dao3->total ? ($dao3->value / $dao3->total) : 0;
      $bench[$dao3->category_type][$dao3->category_key] = [
        'label' => $dao3->category_label,
        'value' => (int)$dao3->value,
        'total' => (int)$dao3->total,
        'pct' => $pct,
      ];
    }

    // Map local values to census categories if a mapping is provided
    $maps = $settings['mappings'];
    $genderRows = $this->buildRows($genders, $totalMembers, $bench['gender'], $maps['gender'] ?? []);
    $ethRows = $this->buildRows($eth, $totalMembers, $bench['ethnicity'], $maps['ethnicity'] ?? []);

    $this->assign('genderRows', $genderRows);
    $this->assign('ethRows', $ethRows);
    $this->assign('totalMembers', $totalMembers);
    $this->assign('geoCode', $geo);
    $this->assign('dataset', $dataset);
    $this->assign('settingsUrl', CRM_Utils_System::url('civicrm/trustcrm/membership-demographics/settings'));
    parent::run();
  }

  private function buildRows($localCounts, $localTotal, $bench, $map) {
    $rows = [];
    $aggLocal = [];
    // Map local keys to bench keys
    foreach ($localCounts as $k => $v) {
      $toKey = isset($map[$k]) ? $map[$k] : $k;
      if (!isset($aggLocal[$toKey])) $aggLocal[$toKey] = 0;
      $aggLocal[$toKey] += $v;
    }
    // union of keys
    $keys = array_unique(array_merge(array_keys($aggLocal), array_keys($bench)));
    foreach ($keys as $key) {
      $mCount = (int)($aggLocal[$key] ?? 0);
      $mPct = $localTotal ? $mCount / $localTotal : 0;
      $bPct = isset($bench[$key]) ? $bench[$key]['pct'] : 0;
      $idx = $bPct > 0 ? round(($mPct / $bPct) * 100) : 0;
      $rows[] = [
        'key' => $key,
        'label' => $bench[$key]['label'] ?? $key,
        'member_count' => $mCount,
        'member_pct' => $mPct * 100,
        'area_pct' => $bPct * 100,
        'index' => $idx,
      ];
    }
    // Sort by label
    usort($rows, function ($a, $b) { return strcasecmp($a['label'], $b['label']); });
    return $rows;
  }
}
