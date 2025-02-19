<?php

/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */


namespace Civi\Mock;

/**
 * Simple data backend for mock basic api.
 */
class MockEntityDataStorage {

  private static $data = [];

  private static $nextId = 1;

  public static function get() {
    return self::$data;
  }

  public static function write($record) {
    if (empty($record['identifier'])) {
      $record['identifier'] = self::$nextId++;
      self::$data[$record['identifier']] = $record;
    }
    else {
      self::$data[$record['identifier']] = $record + self::$data[$record['identifier']];
    }
    return $record;
  }

  public static function delete($record) {
    unset(self::$data[$record['identifier']]);
    return $record;
  }

}
