<?php

use CRM_Trustcrm_MembershipDemographics_ExtensionUtil as E;

class CRM_Trustcrm_MembershipDemographics_Form_Settings extends CRM_Core_Form {

  public function buildQuickForm() {
    $this->add('select', 'trustcrm_md_member_group_ids', E::ts('Member Groups to Include'),
      CRM_Core_PseudoConstant::group(), TRUE, ['multiple' => TRUE, 'class' => 'crm-select2 huge']);
    $this->add('text', 'trustcrm_md_gender_field', E::ts('Gender Field'), [], TRUE);
    $this->add('text', 'trustcrm_md_ethnicity_field', E::ts('Ethnicity Custom Field (e.g. custom_12)'), [], TRUE);
    $this->add('text', 'trustcrm_md_geography_code', E::ts('ONS/NOMIS Geography Code'), [], TRUE);
    $this->add('text', 'trustcrm_md_dataset', E::ts('Dataset Label'), [], TRUE);
    $this->add('textarea', 'trustcrm_md_category_mappings', E::ts('Category Mappings (JSON)'), ['rows' => 8, 'cols' => 60], FALSE);

    // CSV upload to populate trustcrm_census_dataset
    $this->add('file', 'benchmark_csv', E::ts('Upload Benchmark CSV (category_type,category_key,category_label,value,total)'));
    $this->addButtons([
      ['type' => 'upload', 'name' => E::ts('Save & Import CSV')],
      ['type' => 'submit', 'name' => E::ts('Save')],
      ['type' => 'cancel', 'name' => E::ts('Cancel')],
    ]);

    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    foreach ([
      'trustcrm_md_member_group_ids',
      'trustcrm_md_gender_field',
      'trustcrm_md_ethnicity_field',
      'trustcrm_md_geography_code',
      'trustcrm_md_dataset',
      'trustcrm_md_category_mappings',
    ] as $k) {
      Civi::settings()->set($k, $values[$k] ?? NULL);
    }

    // Import CSV if provided
    if (!empty($_FILES['benchmark_csv']['tmp_name'])) {
      $fh = fopen($_FILES['benchmark_csv']['tmp_name'], 'r');
      if ($fh) {
        $geo = $values['trustcrm_md_geography_code'];
        $dataset = $values['trustcrm_md_dataset'];
        // delete existing for dataset+geo
        CRM_Core_DAO::executeQuery("DELETE FROM trustcrm_census_dataset WHERE dataset = %1 AND geography_code = %2", [
          1 => [$dataset, 'String'],
          2 => [$geo, 'String'],
        ]);
        $rownum = 0;
        while (($row = fgetcsv($fh)) !== FALSE) {
          $rownum++;
          if ($rownum == 1 and preg_match('/category_type/i', $row[0])) continue; // header
          list($ctype, $ckey, $clabel, $value, $total) = $row;
          CRM_Core_DAO::executeQuery("INSERT INTO trustcrm_census_dataset
            (dataset, geography_code, geography_label, category_type, category_key, category_label, value, total, version_label)
            VALUES (%1, %2, %3, %4, %5, %6, %7, %8, %9)", [
              1 => [$dataset, 'String'],
              2 => [$geo, 'String'],
              3 => ['Configured geography', 'String'],
              4 => [$ctype, 'String'],
              5 => [$ckey, 'String'],
              6 => [$clabel, 'String'],
              7 => [(int)$value, 'Integer'],
              8 => [(int)$total, 'Integer'],
              9 => ['v1', 'String'],
          ]);
        }
        fclose($fh);
        CRM_Core_Session::setStatus(E::ts('Benchmark CSV imported.'), E::ts('Success'), 'success');
      }
    }

    parent::postProcess();
  }

  public function setDefaultValues() {
    $defaults = [];
    foreach ([
      'trustcrm_md_member_group_ids',
      'trustcrm_md_gender_field',
      'trustcrm_md_ethnicity_field',
      'trustcrm_md_geography_code',
      'trustcrm_md_dataset',
      'trustcrm_md_category_mappings',
    ] as $k) {
      $defaults[$k] = Civi::settings()->get($k);
    }
    return $defaults;
  }
}
