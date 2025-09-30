<?php

require_once 'org.trustcrm.membershipdemographics.civix.php';

use CRM_Trustcrm_MembershipDemographics_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 */
function org_trustcrm_membershipdemographics_civicrm_config(&$config) {
  _org_trustcrm_membershipdemographics_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 */
function org_trustcrm_membershipdemographics_civicrm_install() {
  // Create tables needed for benchmark datasets
  $dao = CRM_Core_DAO::executeQuery("
    CREATE TABLE IF NOT EXISTS trustcrm_census_dataset (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      dataset VARCHAR(64) NOT NULL,
      geography_code VARCHAR(64) NOT NULL,
      geography_label VARCHAR(255) NOT NULL,
      category_type ENUM('gender','ethnicity','age') NOT NULL,
      category_key VARCHAR(128) NOT NULL,
      category_label VARCHAR(255) NOT NULL,
      value INT NOT NULL DEFAULT 0,
      total INT NOT NULL DEFAULT 0,
      version_label VARCHAR(64) DEFAULT NULL,
      created DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY idx_lookup (dataset, geography_code, category_type, category_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");
  _org_trustcrm_membershipdemographics_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 */
function org_trustcrm_membershipdemographics_civicrm_uninstall() {
  CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS trustcrm_census_dataset");
  _org_trustcrm_membershipdemographics_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_navigationMenu().
 */
function org_trustcrm_membershipdemographics_civicrm_navigationMenu(&$menu) {
  _org_trustcrm_membershipdemographics_civix_insert_navigation_menu($menu, 'Reports', array(
    'label'      => E::ts('Membership Demographics vs Census'),
    'name'       => 'trustcrm_membership_demographics_report',
    'url'        => 'civicrm/trustcrm/membership-demographics/report',
    'permission' => 'access CiviReport',
    'operator'   => null,
    'separator'  => 0,
  ));
  _org_trustcrm_membershipdemographics_civix_navigationMenu($menu);
}
