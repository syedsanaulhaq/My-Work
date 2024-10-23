<?php

namespace Drupal\obw_utilities;

use Drupal\Core\Database\Connection;
use Drupal\webform\Entity\WebformSubmission;

/**
 *
 */
class EntityQuery {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Array of conditions.
   *
   * @var array
   */
  protected $conditions = [];

  /**
   * Array of sort conditions.
   *
   * @var array
   */
  protected $sort = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * @param int $webform_id
   */
  public function setWebform($webform_id = NULL) {
    if (!is_null($webform_id)) {
      $this->addCondition('webform_id', $webform_id);
    }
  }

  /**
   *
   * @param string $field
   *   Field name.
   * @param mixed $value
   *   Value to compare.
   * @param mixed $operator
   *   Operator.
   *
   * @return $this
   */
  public function addCondition($field, $value = NULL, $operator = '=') {
    // Check for webform_id.
    if ($field === 'webform_id') {
      // Check for existing condition at 0.
      if (key_exists(0, $this->conditions)) {
        $this->conditions[] = $this->conditions[0];
      }
      $this->conditions[0] = [
        'field' => $field,
        'value' => $value,
        'operator' => $operator,
      ];
    }
    else {

      if (empty($operator)) {
        $operator = '=';
      }

      // Validate opertaor.
      $operator = $this->validateOperator($operator);

      // If operator is good then add the condition.
      if ($operator !== '') {
        $this->conditions[] = [
          'field' => $field,
          'value' => $value,
          'operator' => $operator,
        ];
      }
    }

    return $this;
  }

  /**
   *
   */
  public function orderBy($field, $direction = 'ASC') {
    // Make sure direction is valid.
    $direction = ($direction !== 'ASC') ? 'DESC' : 'ASC';

    $this->sort[] = [
      'field' => $field,
      'direction' => $direction,
    ];

    return $this;

  }

  /**
   * Execute the query.
   *
   * @return array
   *   Array of objects with one property: sid
   */
  public function execute() {
    // Generate query elements from the conditions.
    $query_elements = $this->buildQuery();

    // Clear the conditions and sorting.
    $this->conditions = [];
    $this->sort = [];

    // Execute the query.
    $response = $this->connection->query($query_elements['query'], $query_elements['values']);

    // Return the results.
    return $response->fetchAll();
  }

  /**
   * Build the query from the conditions.
   */
  public function buildQuery() {
    $query = 'SELECT DISTINCT sid FROM {webform_submission_data} wsd';
    $values = [];
    foreach ($this->conditions as $key => $condition) {
      // Check if it's the first condition.
      if ($key === 0) {
        // Check for database field webform_id.
        if ($condition['field'] == 'webform_id') {
          $query .= ' WHERE wsd.webform_id ' . $condition['operator'] . ' :' . $condition['field'];
        }
        else {
          if ($condition['operator'] == 'IS NOT NULL') {
            $query .= ' WHERE wsd.name = :' . $condition['field'] . '_name AND wsd.value ' . $condition['operator'];
          }
          else {
            $query .= ' WHERE wsd.name = :' . $condition['field'] . '_name AND wsd.value ' . $condition['operator'] . ' :' . $condition['field'];
            $values[':' . $condition['field'] . '_name'] = $condition['field'];
          }
        }
        $values[':' . $condition['field']] = $condition['value'];
      }
      else {
        // Normal condition for a webform submission field.
        $alias = 'wsd' . $key;
        if ($condition['operator'] == 'IS NOT NULL') {
          $query .= ' AND sid IN (SELECT sid from {webform_submission_data} ' . $alias . ' WHERE ' . $alias . '.name = :' . $condition['field'] . '_name';
          $query .= ' AND ' . $alias . '.value ' . $condition['operator'] . ')';
          $values[':' . $condition['field'] . '_name'] = $condition['field'];
        }
        else {
          $query .= ' AND sid IN (SELECT sid from {webform_submission_data} ' . $alias . ' WHERE ' . $alias . '.name = :' . $condition['field'] . '_name';
          $query .= ' AND ' . $alias . '.value ' . $condition['operator'] . ' :' . $condition['field'] . ')';
          $values[':' . $condition['field'] . '_name'] = $condition['field'];
          $values[':' . $condition['field']] = $condition['value'];
        }

      }

    }

    // Check for sort criteria.
    foreach ($this->sort as $key => $orderby) {
      // Add comma separator for for additional ORDER BY.
      if ($key > 0) {
        $query .= ',';
      }
      // "obt": Order By Table.
      $orderby_alias = 'obt' . $key;

      $query .= ' ORDER BY ('
        . 'SELECT ' . $orderby_alias . '.value FROM {webform_submission_data} ' . $orderby_alias
        . ' WHERE ' . $orderby_alias . '.name=\'' . $orderby['field'] . '\''
        . ' AND ' . $orderby_alias . '.sid=wsd.sid'
        . ') ' . $orderby['direction'];
    }

    return ['query' => $query, 'values' => $values];

  }

  /**
   * Perform basic validation of the operator.
   *
   * @param string $operator
   *
   * @return string
   *   Return operator or nothing.
   */
  public function validateOperator($operator) {
    if ($operator == 'IS NOT NULL') {
      return $operator;
    }
    if (stripos($operator, 'UNION') !== FALSE || strpbrk($operator, '[-\'"();') !== FALSE) {
      trigger_error('Invalid characters in query operator: ' . $operator, E_USER_ERROR);
      return '';
    }

    return $operator;
  }

  /**
   *
   */
  public function getSubmissionVREvent($date, $time) {
    $values = [
      ":webform_id" => "vr_event_form",
      ":time_name" => "time",
      ":time" => $time,
      ":field_account_contact_number_name" => "field_account_contact_number",
      ":field_account_contact_number" => "",
      ":date_name" => "date",
      ":date" => $date,
      ":remind_sms_name" => "remind_sms",
      ":remind_sms" => 0,
    ];
    $sql = 'SELECT DISTINCT wsd.sid, wsd_number.value, ws_nid.entity_id, ws_nid.entity_type, fem2h.field_event_message_sms_2h_value, fem4h.field_event_message_sms_24h_value' .
      ' FROM {webform_submission_data} wsd' .
      ' JOIN {webform_submission} ws_nid ON ws_nid.sid = wsd.sid' .
      ' LEFT JOIN {webform_submission_data} wsd_number ON wsd_number.sid = wsd.sid AND wsd_number.name = :field_account_contact_number_name' .
      ' LEFT JOIN {node__field_event_message_sms_2h} fem2h ON fem2h.entity_id = ws_nid.entity_id' .
      ' LEFT JOIN {node__field_event_message_sms_24h} fem4h ON fem4h.entity_id = ws_nid.entity_id' .
      ' WHERE wsd.webform_id = :webform_id' .
      ' AND wsd.sid IN (SELECT sid from {webform_submission_data} wsd1 WHERE wsd1.name = :time_name AND wsd1.value = :time)' .
      ' AND wsd.sid NOT IN (SELECT sid from {webform_submission_data} wsd2 WHERE wsd2.name = :field_account_contact_number_name AND wsd2.value = :field_account_contact_number)' .
      ' AND wsd.sid IN (SELECT sid from {webform_submission_data} wsd3 WHERE wsd3.name = :date_name AND wsd3.value = :date)' .
      ' AND wsd.sid IN (SELECT sid from {webform_submission_data} wsd4 WHERE wsd4.name = :remind_sms_name AND wsd4.value = :remind_sms)';

    $response = $this->connection->query($sql, $values);

    // Return the results.
    return $response->fetchAll();
  }

  /**
   *
   */
  public function getSubmissionIDByUID($webform_id, $uid) {
    $values = [
      ":webform_id" => $webform_id,
      ":uid" => $uid,
    ];
    $sql = 'SELECT DISTINCT wsd.sid' .
      ' FROM {webform_submission} wsd' .
      ' WHERE wsd.webform_id = :webform_id' .
      ' AND wsd.uid = :uid';

    $response = $this->connection->query($sql, $values);

    // Return the results.
    return $response->fetchAll();
  }

  /**
   *
   */
  public function getSubmissionIDByWebformID($webform_id) {
    $values = [
      ":webform_id" => $webform_id,
    ];
    $sql = 'SELECT DISTINCT wsd.sid' .
      ' FROM {webform_submission} wsd' .
      ' WHERE wsd.webform_id = :webform_id';

    $response = $this->connection->query($sql, $values);

    // Return the results.
    return $response->fetchAll();
  }

  /**
   * @param $key
   *
   * @return mixed
   */
  public static function getSubmissionIDByKey($key) {
    $query = \Drupal::database()->select('webform_submission_data', 'wsd')
      ->fields('wsd', ['sid'])
      ->condition('webform_id', 'support_us_tax_form', '=')
      ->condition('name', 'key', '=')
      ->condition('value', '(^|,)' . $key . '($|,)', 'REGEXP');
    $execute = $query->execute();
    return $execute->fetchAll();
  }

  /**
   * @param $mail
   *
   * @return mixed
   */
  public static function getSubmissionIDByMail($mail) {
    $query = \Drupal::database()->select('webform_submission_data', 'wsd')
      ->fields('wsd', ['sid'])
      ->condition('webform_id', 'support_us_tax_form', '=')
      ->condition('value', $mail, '=');
    $execute = $query->execute();
    return $execute->fetchAssoc();
  }

  /**
   * @param $mail
   *
   * @return mixed
   */
  public static function getSubmissionIDByMailInTaxReliefForm($mail) {
    $query = \Drupal::database()->select('webform_submission_data', 'wsd')
      ->fields('wsd', ['sid'])
      ->condition('webform_id', 'support_us_tax_form', '=')
      ->condition('value', $mail, '=');
    $execute = $query->execute();
    return $execute->fetchAssoc();
  }

  /**
   *
   */
  public function getSubmissionIDByEmailInSupportUs($mail) {
    $values = [
      ":webform_id" => 'support_us',
      ":field_payment_email" => 'payment_email',
      ":email" => $mail,
      ":field_payment_status" => 'payment_status',
      ":payment_status_active" => 'active',
      ":payment_status_ongoing" => 'Ongoing',
      ":payment_status_inactive" => 'inactive',
      ":payment_status_cancelled" => 'Cancelled',
      ":payment_status_succeeded" => 'succeeded',
      ":payment_status_complete" => 'Complete',
    ];
    $sql = 'SELECT DISTINCT ws.sid' .
      ' FROM {webform_submission} ws' .
      ' LEFT JOIN {webform_submission_data} wsd ON ws.sid = wsd.sid AND wsd.name = :field_payment_email' .
      ' LEFT JOIN {webform_submission_data} wsd2 ON ws.sid = wsd2.sid AND wsd2.name = :field_payment_status' .
      ' WHERE ws.webform_id = :webform_id AND wsd.value = :email' .
      ' AND (wsd2.value = :payment_status_active' .
      ' OR wsd2.value = :payment_status_succeeded' .
      ' OR wsd2.value = :payment_status_inactive' .
      ' OR wsd2.value = :payment_status_ongoing' .
      ' OR wsd2.value = :payment_status_cancelled' .
      ' OR wsd2.value = :payment_status_complete)' .
      ' ORDER BY ws.sid DESC';

    $response = $this->connection->query($sql, $values);

    // Return the results.
    return $response->fetchAll();
  }

  /**
   *
   */
  public function getSubmissionMonthlyIDByEmailInSupportUs($mail) {
    $values = [
      ":webform_id" => 'support_us',
      ":field_payment_email" => 'payment_email',
      ":email" => $mail,
      ":field_payment_status" => 'payment_status',
      ":payment_status_active" => 'active',
      ":payment_status_ongoing" => 'Ongoing',
    ];
    $sql = 'SELECT DISTINCT ws.sid' .
      ' FROM {webform_submission} ws' .
      ' LEFT JOIN {webform_submission_data} wsd ON ws.sid = wsd.sid AND wsd.name = :field_payment_email' .
      ' LEFT JOIN {webform_submission_data} wsd2 ON ws.sid = wsd2.sid AND wsd2.name = :field_payment_status' .
      ' WHERE ws.webform_id = :webform_id AND wsd.value = :email AND (wsd2.value = :payment_status_active OR wsd2.value = :payment_status_ongoing)' .
      ' ORDER BY ws.sid DESC';

    $response = $this->connection->query($sql, $values);

    // Return the results.
    return $response->fetchAll();
  }

  public function getSubmissionMonthlyIDByPaymentIdInSupportUs($email, $payment_id) {
    $values = [
      ":webform_id" => 'support_us',
      ":field_payment_email" => 'payment_email',
      ":email" => $email,
      ":field_payment_status" => 'payment_status',
      ":payment_status_active" => 'active',
      ":payment_status_ongoing" => 'Ongoing',
      ":field_payment_id" => 'payment_id',
      ":payment_id" => $payment_id,
    ];
    $sql = 'SELECT DISTINCT ws.sid' .
      ' FROM {webform_submission} ws' .
      ' LEFT JOIN {webform_submission_data} wsd ON ws.sid = wsd.sid AND wsd.name = :field_payment_email' .
      ' LEFT JOIN {webform_submission_data} wsd2 ON ws.sid = wsd2.sid AND wsd2.name = :field_payment_status' .
      ' LEFT JOIN {webform_submission_data} wsd3 ON ws.sid = wsd3.sid AND wsd3.name = :field_payment_id' .
      ' WHERE ws.webform_id = :webform_id AND wsd.value = :email AND (wsd2.value = :payment_status_active OR wsd2.value = :payment_status_ongoing) AND wsd3.value = :payment_id' .
      ' ORDER BY ws.sid DESC';

    $response = $this->connection->query($sql, $values);

    // Return the results.
    return $response->fetchObject()->sid ?? NULL;
  }

  public function getSubmissionsExistingInTaxRelief() {
    $params = [
      ':wf_id' => 'support_us_tax_form',
      ':mail_name' => 'email',
      ':monthly_name' => 'is_a_monthly_donation',
      ':monthly_value' => 1
    ];
    $sql = "SELECT wsd.sid, wsd.value as email
            FROM webform_submission_data wsd
            LEFT JOIN webform_submission_data wsd2 ON wsd.sid = wsd2.sid AND wsd2.name = :monthly_name
            WHERE wsd.webform_id = :wf_id AND wsd.name = :mail_name AND wsd2.value = :monthly_value ";
    return $this->connection->query($sql, $params);
  }

  public function getSubmissionsMonthlyDonationInSupportUs($except_emails = []) {
    $params = [
      ':wf_id' => 'support_us',
      ':email_name' => 'payment_email',
      ':monthly_name' => 'monthly_basis',
      ':monthly_value' => 1,
      ':country_name' => 'country',
      ':amount_name' => 'payment_amount',
      ':empty' => '',
    ];
    $sql = "SELECT wsd.sid, wsd.value as monthly_basis, wsd2.value as email, wsd3.value as country, wsd4.value as payment_amount
            FROM webform_submission_data wsd
            LEFT JOIN webform_submission_data wsd2 ON wsd2.sid = wsd.sid AND wsd2.name = :email_name
            LEFT JOIN webform_submission_data wsd3 ON wsd3.sid = wsd.sid AND wsd3.name = :country_name
            LEFT JOIN webform_submission_data wsd4 ON wsd4.sid = wsd.sid AND wsd4.name = :amount_name
            WHERE wsd.webform_id  = :wf_id AND wsd.name = :monthly_name
              AND wsd.value = :monthly_value AND wsd2.value != :empty";

    if (!empty($except_emails)) {
      $params[':except_emails[]'] = $except_emails;
      $sql .= " AND wsd2.value NOT IN (:except_emails[])";
    }

    return $this->connection->query($sql, $params);
  }

  /**
   *
   */
  public function checkToolkitWebformSendMail($email, $persona_id) {
    $values = [
      ":webform_id" => 'email_me_a_copy_of_my_persona',
      ":field_email" => 'email',
      ":field_persona_id" => 'persona_id',
      ":field_send_mail" => 'send_mail',
      ":email" => $email,
      ":persona_id" => $persona_id,
    ];

    $sql = 'SELECT DISTINCT wsd1.sid' .
      ' FROM {webform_submission} ws' .
      ' LEFT JOIN {webform_submission_data} wsd1 ON ws.sid = wsd1.sid AND wsd1.name = :field_email' .
      ' LEFT JOIN {webform_submission_data} wsd2 ON ws.sid = wsd2.sid AND wsd2.name = :field_persona_id' .
      ' LEFT JOIN {webform_submission_data} wsd3 ON ws.sid = wsd3.sid AND wsd3.name = :field_send_mail' .
      ' WHERE ws.webform_id = :webform_id' .
      ' AND wsd1.value = :email' .
      ' AND wsd2.value = :persona_id' .
      ' AND wsd3.value = 1';

    $response = $this->connection->query($sql, $values);

    // Return the results.
    return $response->fetchAll();
  }

  /**
   *
   */
  public function getPostBirthday2021ByShape($shape_type, $filter) {
    $submissions = [];
    $query_params = [
      'webform_id' => 'obw_birthday_2021',
      'wf_img_type_field' => 'image_type',
      'wf_img_type_value' => $shape_type,
      'wf_status_field' => 'status',
      'wf_status_value' => 'approved',
    ];

    $connection = \Drupal::database();
    $query = $connection->select('webform_submission', 'ws');
    $query->innerJoin('webform_submission_data', 'wsd1', 'ws.sid = wsd1.sid AND wsd1.name = :wf_img_type_field');
    $query->innerJoin('webform_submission_data', 'wsd2', 'ws.sid = wsd2.sid AND wsd2.name = :wf_status_field');
    if ($filter && $filter != 'all') {
      $query_params['wf_filter_field'] = 'pick_one_of_our_values';
      $query_params['wf_filter_value'] = $filter;
      $query->innerJoin('webform_submission_data', 'wsd3', 'ws.sid = wsd3.sid AND wsd3.name = :wf_filter_field');
      $query->where('ws.webform_id = :webform_id and wsd1.value = :wf_img_type_value and wsd2.value = :wf_status_value and wsd3.value = :wf_filter_value', $query_params);
    }
    else {
      $query->where('ws.webform_id = :webform_id and wsd1.value = :wf_img_type_value and wsd2.value = :wf_status_value', $query_params);
    }
    $query->fields('ws', ['sid']);
    $query->orderRandom();
    $submission_ids = [];
    foreach ($query->execute()->fetchAll() as $value) {
      $submission_ids[] = $value->sid;
    }
    if ($submission_ids) {
      $submissions = WebformSubmission::loadMultiple($submission_ids);
    }
    return $submissions;
  }

  /**
   *
   */
  public function getBirthday2021SubmissionRelated($firstName, $lastName, $ourValue) {
    $query_params = [
      'webform_id' => 'obw_birthday_2021',
      'wf_first_name_cl' => 'first_name',
      'wf_first_name_field' => $firstName,
      'wf_last_name_cl' => 'last_name',
      'wf_last_name_field' => $lastName,
      'wf_pick_one_of_our_values_cl' => 'pick_one_of_our_values',
      'wf_our_value_value' => $ourValue,
      'wf_status_value' => 'approved',
      'wf_status_cl' => 'status',
    ];
    $connection = \Drupal::database();
    $query = $connection->select('webform_submission_data', 'wsd');
    $query->where('wsd.webform_id = :webform_id AND wsd.name = :wf_first_name_cl AND wsd.value = :wf_first_name_field
    AND wsd.sid IN (SELECT sid from {webform_submission_data} wsd1 WHERE wsd1.name = :wf_last_name_cl AND wsd1.value = :wf_last_name_field
    AND wsd1.sid IN (SELECT sid from {webform_submission_data} wsd2 WHERE wsd2.name = :wf_pick_one_of_our_values_cl AND wsd2.value = :wf_our_value_value
    AND wsd2.sid IN (SELECT sid from {webform_submission_data} wsd3 WHERE wsd3.name = :wf_status_cl AND wsd3.value = :wf_status_value)))
    ', $query_params);
    $query->fields('wsd', ['sid']);

    $response = [];
    foreach ($query->execute()->fetchAll() as $value) {
      $response[] = $value->sid;
    }
    return count($response);
  }

  /**
   *
   */
  public function getPostBirthday2021ByAuthor($author) {
    $query_params = [
      'webform_id' => 'obw_birthday_2021',
      'wf_author_field' => 'obw_values_story',
      'wf_author_value' => '%/about-us/our-values?author=' . $author,
    ];

    $connection = \Drupal::database();
    $query = $connection->select('webform_submission_data', 'wsd');
    $query->where('wsd.webform_id = :webform_id and wsd.name = :wf_author_field and wsd.value LIKE :wf_author_value', $query_params);
    $query->fields('wsd', ['sid']);

    $response = [];
    foreach ($query->execute()->fetchAll() as $value) {
      $response[] = $value->sid;
    }
    return $response;
  }

  /**
   *
   */
  public function loadStoriesByFormat($node_type, $field_story_format_value, $article_type, $exclude_nids) {
    $query_params = [
      'node_type' => $node_type,
      'field_story_format_value' => $field_story_format_value,
      'article_type' => $article_type,
    ];

    $connection = \Drupal::database();
    $query = $connection->select('node');
    $query->fields('node', ['nid']);
    $query->fields('nfd', ['title']);
    $query->innerJoin('node__field_story_format', 'nfsf', 'nfsf.entity_id = node.nid');
    $query->innerJoin('node__field_story_mh2020_type', 'nfsmt', 'nfsmt.entity_id = node.nid');
    $query->innerJoin('node_field_data', 'nfd', 'nfd.nid = node.nid');
    if ($exclude_nids) {
      $query->condition('node.nid', $exclude_nids, 'NOT IN');
    }
    $query->where('node.type = :node_type and nfsf.field_story_format_value = :field_story_format_value
      and nfsmt.field_story_mh2020_type_value = :article_type', $query_params);
    return $query->execute()->fetchAll();
  }

  /**
   * @param $email
   *
   * @return string
   */
  public static function loadCountryByEmail($email) {
    $country = '';
    $query = \Drupal::database()->select('webform_submission_data', 'wsd')
      ->fields('wsd', ['sid'])
      ->condition('webform_id', 'support_us_tax_form', '=')
      ->condition('value', $email, '=');
    $execute = $query->execute();
    $result = $execute->fetchAssoc();

    if (isset($result['sid'])) {
      $webform_submission = WebformSubmission::load($result['sid']);
      $webform_submission_data = $webform_submission->getData();
      if (isset($webform_submission_data['country'])) {
        $country = $webform_submission_data['country'];
      }
    }

    return $country;
  }
}
