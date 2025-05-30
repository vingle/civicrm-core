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


namespace api\v4\Custom;

use api\v4\Api4TestBase;
use Civi\Api4\CustomField;
use Civi\Api4\Contact;

/**
 * @group headless
 */
class CreateWithOptionGroupTest extends Api4TestBase {

  public function testGetWithCustomData(): void {
    $group = uniqid('fava');
    $colorField = uniqid('colora');
    $foodField = uniqid('fooda');

    $customGroupId = $this->createTestRecord('CustomGroup', [
      'title' => $group,
      'extends' => 'Contact',
    ])['id'];

    CustomField::create(FALSE)
      ->addValue('label', $colorField)
      ->addValue('name', $colorField)
      ->addValue('option_values', ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'])
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('html_type', 'Select')
      ->addValue('data_type', 'String')
      ->execute();

    CustomField::create(FALSE)
      ->addValue('label', $foodField)
      ->addValue('name', $foodField)
      ->addValue('option_values', ['1' => 'Corn', '2' => 'Potatoes', '3' => 'Cheese'])
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('html_type', 'Select')
      ->addValue('data_type', 'String')
      ->execute();

    $customGroupId = $this->createTestRecord('CustomGroup', [
      'title' => 'FinancialStuff',
      'extends' => 'Contact',
    ])['id'];

    CustomField::create(FALSE)
      ->addValue('label', 'Salary')
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('html_type', 'Number')
      ->addValue('data_type', 'Money')
      ->execute();

    $this->createTestRecord('Contact', [
      'first_name' => 'Jerome',
      'last_name' => 'Tester',
      'contact_type' => 'Individual',
      "$group.$colorField" => 'r',
      "$group.$foodField" => '1',
      'FinancialStuff.Salary' => 50000,
    ]);

    $result = Contact::get(FALSE)
      ->addSelect('first_name')
      ->addSelect("$group.$colorField:label")
      ->addSelect("$group.$foodField:label")
      ->addSelect('FinancialStuff.Salary')
      ->addWhere("$group.$foodField:label", 'IN', ['Corn', 'Potatoes'])
      ->addWhere('FinancialStuff.Salary', '>', '10000')
      ->execute()
      ->first();

    $this->assertEquals('Red', $result["$group.$colorField:label"]);
    $this->assertEquals('Corn', $result["$group.$foodField:label"]);
    $this->assertEquals(50000, $result['FinancialStuff.Salary']);
  }

  public function testWithCustomDataForMultipleContacts(): void {
    $group = uniqid('favb');
    $colorField = uniqid('colorb');
    $foodField = uniqid('foodb');

    $customGroupId = $this->createTestRecord('CustomGroup', [
      'title' => $group,
      'extends' => 'Contact',
    ])['id'];

    CustomField::create(FALSE)
      ->addValue('label', $colorField)
      ->addValue('name', $colorField)
      ->addValue('option_values', ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'])
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('html_type', 'Select')
      ->addValue('data_type', 'String')
      ->execute();

    CustomField::create(FALSE)
      ->addValue('label', $foodField)
      ->addValue('name', $foodField)
      ->addValue('option_values', ['1' => 'Corn', '2' => 'Potatoes', '3' => 'Cheese'])
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('html_type', 'Select')
      ->addValue('data_type', 'String')
      ->execute();

    $customGroupId = $this->createTestRecord('CustomGroup', [
      'title' => 'FinancialStuff',
      'extends' => 'Contact',
    ])['id'];

    CustomField::create(FALSE)
      ->addValue('label', 'Salary')
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('html_type', 'Number')
      ->addValue('data_type', 'Money')
      ->execute();

    $this->createTestRecord('Contact', [
      'first_name' => 'Red',
      'last_name' => 'Corn',
      'contact_type' => 'Individual',
      "$group.$colorField" => 'r',
      "$group.$foodField" => '1',
      'FinancialStuff.Salary' => 10000,
    ]);

    $this->createTestRecord('Contact', [
      'first_name' => 'Blue',
      'last_name' => 'Cheese',
      'contact_type' => 'Individual',
      "$group.$colorField" => 'b',
      "$group.$foodField" => '3',
      'FinancialStuff.Salary' => 500000,
    ]);

    $result = Contact::get(FALSE)
      ->addSelect('first_name')
      ->addSelect('last_name')
      ->addSelect("$group.$colorField:label")
      ->addSelect("$group.$foodField:label")
      ->addSelect('FinancialStuff.Salary')
      ->addWhere("$group.$foodField:label", 'IN', ['Corn', 'Cheese'])
      ->execute();

    $blueCheese = NULL;
    foreach ($result as $contact) {
      if ($contact['first_name'] === 'Blue') {
        $blueCheese = $contact;
      }
    }

    $this->assertEquals('Blue', $blueCheese["$group.$colorField:label"]);
    $this->assertEquals('Cheese', $blueCheese["$group.$foodField:label"]);
    $this->assertEquals(500000, $blueCheese['FinancialStuff.Salary']);
  }

}
