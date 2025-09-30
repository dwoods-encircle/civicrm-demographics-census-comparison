<?php
// Minimal shim so this works without running civix. In production, run 'civix' to regenerate.
class CRM_Trustcrm_MembershipDemographics_ExtensionUtil {
  const SHORT_NAME = "org.trustcrm.membershipdemographics";
  public static function ts($text, $params = array()) { return E::ts($text, $params); }
}

class E {
  public static function ts($text, $params = []) { return ts($text, $params); }
}

function _org_trustcrm_membershipdemographics_civix_civicrm_config(&$config) {}
function _org_trustcrm_membershipdemographics_civix_civicrm_install() {}
function _org_trustcrm_membershipdemographics_civix_civicrm_uninstall() {}
function _org_trustcrm_membershipdemographics_civix_insert_navigation_menu(&$menu, $path, $item) {
  // Append to Reports
  foreach ($menu as &$m) {
    if ($m['attributes']['label'] === 'Reports' || $m['attributes']['name'] === 'Reports') {
      $m['child'][] = array('attributes' => $item, 'child' => []);
      return;
    }
    if (!empty($m['child'])) _org_trustcrm_membershipdemographics_civix_insert_navigation_menu($m['child'], $path, $item);
  }
}
function _org_trustcrm_membershipdemographics_civix_navigationMenu(&$menu) {}
