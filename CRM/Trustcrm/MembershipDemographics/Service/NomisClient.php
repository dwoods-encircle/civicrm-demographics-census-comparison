<?php

class CRM_Trustcrm_MembershipDemographics_Service_NomisClient {

  protected $base = 'https://www.nomisweb.co.uk/api/v01/dataset/';

  public function fetchSimple($dataset, $geogCode, $categoryType) {
    // This is a stub showing how you might fetch NOMIS JSON.
    // In practice you would tailor to the dataset's schema (variable names differ).
    $url = $this->base . rawurlencode($dataset) . '.data.json';
    $url .= '?geography=' . rawurlencode($geogCode);
    $url .= '&measures=20100'; // value
    // ... add variables for gender/ethnicity depending on $categoryType

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $out = curl_exec($ch);
    if ($out === FALSE) throw new Exception('NOMIS request failed: ' . curl_error($ch));
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($status >= 300) throw new Exception("NOMIS HTTP $status: $out");
    $json = json_decode($out, TRUE);
    return $json;
  }
}
