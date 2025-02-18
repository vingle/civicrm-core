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
 * Test class for CRM_Pledge_BAO_PledgePayment BAO
 *
 * @package   CiviCRM
 * @group headless
 */
class CRM_Pledge_BAO_PledgePaymentTest extends CiviUnitTestCase {

  public function tearDown(): void {
    $this->quickCleanUpFinancialEntities();
    $this->quickCleanup(['civicrm_campaign']);
    parent::tearDown();
  }

  /**
   *  Test for Add/Update Pledge Payment.
   */
  public function testAdd(): void {
    $this->pledgeCreate([]);
    $params = [
      'pledge_id' => $this->ids['Pledge']['default'],
      'scheduled_amount' => 100.55,
      'currency' => 'USD',
      'scheduled_date' => '20100512000000',
      'reminder_date' => '20100520000000',
      'reminder_count' => 5,
      'status_id' => 1,
    ];

    //do test for normal add.
    $payment = CRM_Pledge_BAO_PledgePayment::add($params);
    foreach ($params as $param => $value) {
      $this->assertEquals($value, $payment->$param);
    }

    //do test for update mode.
    $params = [
      'id' => $payment->id,
      'pledge_id' => $this->ids['Pledge']['default'],
      'scheduled_amount' => 55.55,
      'currency' => 'USD',
      'scheduled_date' => '20100415000000',
      'reminder_date' => '20100425000000',
      'reminder_count' => 10,
      'status_id' => 2,
    ];

    $payment = CRM_Pledge_BAO_PledgePayment::add($params);
    foreach ($params as $param => $value) {
      $this->assertEquals($value, $payment->$param);
    }
  }

  /**
   *  Delete Payments payments for one pledge.
   */
  public function testDeletePledgePaymentsNormal(): void {
    $payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_PledgePayment');
    $paymentid = CRM_Pledge_BAO_PledgePayment::deletePayments($payment->pledge_id);

    $this->assertEquals($paymentid, 1, "Deleted one payment");
  }

  /**
   *  Delete Multiple payments for one pledge.
   */
  public function testDeletePledgePayments(): void {
    $contactId = $this->individualCreate();
    $pledgeId = $this->pledgeCreate(['contact_id' => $contactId]);
    CRM_Pledge_BAO_PledgePayment::deletePayments($pledgeId);

    // No payments should be retrieved
    $pledgePayment = CRM_Pledge_BAO_PledgePayment::getPledgePayments($pledgeId);
    $this->assertEquals(count($pledgePayment), 0, "Checking for empty array");
  }

  /**
   *  Pass Null Id for a payment deletion for one pledge.
   */
  public function testDeletePledgePaymentsNullId(): void {
    $payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_PledgePayment');
    $paymentid = CRM_Pledge_BAO_PledgePayment::deletePayments(NULL);
    $this->assertFalse($paymentid, "No payments deleted");
  }

  /**
   *  Pass Zero Id for a payment deletion for one pledge.
   */
  public function testDeletePaymentsZeroId(): void {
    $payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_PledgePayment');
    $paymentid = CRM_Pledge_BAO_PledgePayment::deletePayments(0);
  }

  /**
   *  Test calculateBaseScheduleDate - should give 15th day of month
   */
  public function testcalculateBaseScheduleDateMonth(): void {
    $params = [
      'scheduled_date' => '20110510',
      'frequency_unit' => 'month',
      'frequency_day' => 15,
      'frequency_interval' => 2,
    ];

    $date = CRM_Pledge_BAO_PledgePayment::calculateBaseScheduleDate($params);
    $this->assertEquals('20110515000000', $date);
  }

  /**
   *  Test calculateBaseScheduleDate - should give original date
   */
  public function testcalculateBaseScheduleDateDay(): void {
    $params = [
      'scheduled_date' => '20110510',
      'frequency_unit' => 'day',
      'frequency_day' => 15,
      'frequency_interval' => 2,
    ];

    $date = CRM_Pledge_BAO_PledgePayment::calculateBaseScheduleDate($params);
    $this->assertEquals('20110510000000', $date);
  }

  /**
   * Test calculateBaseScheduleDateWeek - should give the day in the week as indicated
   * testing each day as this is really the only unit that does anything
   */
  public function testcalculateBaseScheduleDateWeek(): void {
    $params = [
      'scheduled_date' => '20110510',
      'frequency_unit' => 'week',
      'frequency_day' => 1,
      'frequency_interval' => 2,
    ];

    $date = CRM_Pledge_BAO_PledgePayment::calculateBaseScheduleDate($params);
    $this->assertEquals('20110509000000', $date);
    $params['frequency_day'] = 2;
    $date = CRM_Pledge_BAO_PledgePayment::calculateBaseScheduleDate($params);
    $this->assertEquals('20110510000000', $date);
    $params['frequency_day'] = 3;
    $date = CRM_Pledge_BAO_PledgePayment::calculateBaseScheduleDate($params);
    $this->assertEquals('20110511000000', $date);
    $params['frequency_day'] = 4;
    $date = CRM_Pledge_BAO_PledgePayment::calculateBaseScheduleDate($params);
    $this->assertEquals('20110512000000', $date);
    $params['frequency_day'] = 5;
    $date = CRM_Pledge_BAO_PledgePayment::calculateBaseScheduleDate($params);
    $this->assertEquals('20110513000000', $date);
    $params['frequency_day'] = 6;
    $date = CRM_Pledge_BAO_PledgePayment::calculateBaseScheduleDate($params);
    $this->assertEquals('20110514000000', $date);
    $params['frequency_day'] = 7;
    $date = CRM_Pledge_BAO_PledgePayment::calculateBaseScheduleDate($params);
    $this->assertEquals('20110515000000', $date);
  }

  /**
   *  Test calculateBaseScheduleDate - should give original date
   */
  public function testcalculateBaseScheduleDateYear(): void {
    $params = [
      'scheduled_date' => '20110510',
      'frequency_unit' => 'year',
      'frequency_day' => 15,
      'frequency_interval' => 2,
    ];

    $date = CRM_Pledge_BAO_PledgePayment::calculateBaseScheduleDate($params);
    $this->assertEquals('20110510000000', $date);
  }

  /**
   *  Test calculateNextScheduledDate - no date provided
   */
  public function testCalculateNextScheduledDateYear(): void {
    $params = [
      'scheduled_date' => '20110510',
      'frequency_unit' => 'year',
      'frequency_day' => 15,
      'frequency_interval' => 2,
    ];

    $date = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 1);
    $this->assertEquals('20130510000000', $date);
  }

  /**
   *  CRM-18316: To calculate pledge scheduled dates with end of a month.
   *  Test culateNextScheduledDateMonth for months.
   */
  public function testCalculateNextScheduledDateMonth(): void {
    $params = [
      'scheduled_date' => '20110510',
      'frequency_unit' => 'month',
      'frequency_day' => 31,
      'frequency_interval' => 1,
    ];
    $nextScheduleDate = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 2);
    $this->assertEquals('20110731000000', $nextScheduleDate);
    // assert pledge scheduled date for month february.
    $nextScheduleDate = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 9);
    $this->assertEquals('20120229000000', $nextScheduleDate);

    //Case: Frequency day = 31 and scheduled date = 31st of any month
    $params['scheduled_date'] = '20110131';
    $params['frequency_day'] = 31;
    $nextScheduleDate = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 1);
    $this->assertEquals('20110228000000', $nextScheduleDate);

    //Case: Frequency day = 30 and scheduled date = 30th of any month
    $params['scheduled_date'] = '20110130';
    $params['frequency_day'] = 30;
    $nextScheduleDate = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 3);
    $this->assertEquals('20110430000000', $nextScheduleDate);

    //Case: Frequency day = 30 and scheduled date = any day of month
    $params['scheduled_date'] = '20110110';
    $params['frequency_day'] = 30;
    $nextScheduleDate = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 4);
    $this->assertEquals('20110530000000', $nextScheduleDate);

    //Case: Frequency day = any and scheduled date = 31st of any month
    $params['scheduled_date'] = '20110131';
    $params['frequency_day'] = 5;
    $nextScheduleDate = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 5);
    $this->assertEquals('20110605000000', $nextScheduleDate);

    //Case: Frequency day = any AND scheduled date = 30th of any month
    $params['scheduled_date'] = '20110130';
    $params['frequency_day'] = 10;
    $nextScheduleDate = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 6);
    $this->assertEquals('20110710000000', $nextScheduleDate);

    //Case: Frequency day = any AND scheduled date = any day month
    $params['scheduled_date'] = '20110124';
    $params['frequency_day'] = 6;
    $nextScheduleDate = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 7);
    $this->assertEquals('20110806000000', $nextScheduleDate);

    //Case: Frequency day = 31 AND scheduled date = 29 Feb
    $params['scheduled_date'] = '20160229';
    $params['frequency_day'] = 31;
    $nextScheduleDate = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 5);
    $this->assertEquals('20160731000000', $nextScheduleDate);
    $nextScheduleDate = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 6);
    $this->assertEquals('20160831000000', $nextScheduleDate);
    //check date for february
    $nextScheduleDate = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 12);
    $this->assertEquals('20170228000000', $nextScheduleDate);
  }

  /**
   *  Test calculateNextScheduledDate - no date provided
   */
  public function testCalculateNextScheduledDateYearDateProvided(): void {
    $params = [
      'scheduled_date' => '20110510',
      'frequency_unit' => 'year',
      'frequency_day' => 15,
      'frequency_interval' => 2,
    ];

    $date = CRM_Pledge_BAO_PledgePayment::calculateNextScheduledDate($params, 3, '20080510');
    $this->assertEquals('20140510000000', $date);
  }

  /**
   * Test pledge payments that cover multiple pledge installments (CRM-17281).
   * Check for rounding bugs. NB: this cannot be done in the API, because the
   * API does not call CRM_Contribute_BAO_Contribution::updateRelatedPledge().
   */
  public function testCreatePledgePaymentForMultipleInstallments(): void {
    $scheduled_date = date('Ymd', mktime(0, 0, 0, date("m"), date("d") + 2, date("y")));
    $contact_id = 2;

    $pledge = $this->callAPISuccess('Pledge', 'create', [
      'contact_id' => $contact_id,
      'pledge_create_date' => date('Ymd'),
      'start_date' => date('Ymd'),
      'scheduled_date' => $scheduled_date,
      'amount' => 1618.80,
      'pledge_status_id' => 2,
      'pledge_financial_type_id' => 1,
      'pledge_original_installment_amount' => 134.90,
      'original_installment_amount' => 134.90,
      'frequency_interval' => 1,
      'frequency_unit' => 'month',
      'frequency_day' => 1,
      'installments' => 12,
      'sequential' => 1,
    ]);

    $contributionID = $this->contributionCreate([
      'contact_id' => $contact_id,
      'financial_type_id' => 1,
      'invoice_id' => 46,
      'trxn_id' => 46,
      'total_amount' => 404.70,
      'fee_amount' => 0.00,
      'net_amount' => 404.70,
      'payment_instrument_id' => 1,
      'non_deductible_amount' => 0.00,
    ]);

    // Fetch the first planned pledge payment/installment
    $pledgePayments = civicrm_api3('PledgePayment', 'get', [
      'pledge_id' => $pledge['id'],
      'sequential' => 1,
    ]);

    // Does all sorts of shenanigans if the amount was not the expected amount,
    // and this is what we really want to test in this function.
    CRM_Contribute_BAO_Contribution::updateRelatedPledge(
      CRM_Core_Action::ADD,
      $pledgePayments['values'][0]['id'],
      $contributionID,
      // adjustTotalAmount
      NULL,
      404.70,
      134.90,
      // contribution_status_id
      1,
      // original_contribution_status_id
      NULL
    );

    // Fetch the pledge payments again to see if the amounts and statuses
    // have been updated correctly.
    $pledgePayments = $this->callAPISuccess('pledge_payment', 'get', [
      'pledge_id' => $pledge['id'],
      'sequential' => 1,
    ]);

    // The status of the first 3 pledges should be set to complete
    $this->assertEquals($pledgePayments['values'][0]['status_id'], 1);
    $this->assertEquals($pledgePayments['values'][0]['actual_amount'], 404.70);

    $this->assertEquals($pledgePayments['values'][1]['status_id'], 1);
    $this->assertEquals($pledgePayments['values'][1]['actual_amount'], 0);

    $this->assertEquals($pledgePayments['values'][2]['status_id'], 1);
    $this->assertEquals($pledgePayments['values'][2]['actual_amount'], 0);

    // Fourth pledge should still be pending
    $this->assertEquals($pledgePayments['values'][3]['status_id'], 2);
  }

  /**
   * Test pledge payments that cover multiple pledge installments (CRM-17281).
   * Check for rounding bugs and correct status of the last pledge payment.
   *
   * More specifically, in the UI this would be equivalent to creating a $100
   * pledge to be paid in 11 installments of $8.33 and one installment of $8.37
   * (to compensate the missing $0.04 from round(100/12)*12.
   * The API does not allow to do this kind of pledge, because the BAO
   * recalculates the 'amount' using original_installment_amount * installment.
   *
   * @throws \CRM_Core_Exception
   */
  public function testCreatePledgePaymentForMultipleInstallments2(): void {
    $scheduled_date = date('Ymd', mktime(0, 0, 0, date("m"), date("d") + 2, date("y")));
    $contact_id = 2;

    $params = [
      'contact_id' => $contact_id,
      'pledge_create_date' => date('Ymd'),
      'start_date' => date('Ymd'),
      'scheduled_date' => $scheduled_date,
      'amount' => 100.00,
      'pledge_status_id' => 2,
      'pledge_financial_type_id' => 1,
      // the API does not allow this
      'original_installment_amount' => (100 / 12),
      'frequency_interval' => 1,
      'frequency_unit' => 'month',
      'frequency_day' => 1,
      'installments' => 12,
      'sequential' => 1,
    ];

    $pledge = $this->callAPISuccess('Pledge', 'create', $params);

    $contributionID = $this->contributionCreate([
      'contact_id' => $contact_id,
      'financial_type_id' => 1,
      'invoice_id' => 47,
      'trxn_id' => 47,
      'total_amount' => 100.00,
      'fee_amount' => 0.00,
      'net_amount' => 100.00,
      'payment_instrument_id' => 1,
      'non_deductible_amount' => 0.00,
    ]);

    // Fetch the first planned pledge payment/installment
    $pledgePayments = civicrm_api3('PledgePayment', 'get', [
      'pledge_id' => $pledge['id'],
      'sequential' => 1,
    ]);

    // The last pledge payment is 8.37 because 12*8.33 = 99.96
    // So CiviCRM automatically creates a larger final pledge to catch the missing cents.
    $last_pp_idx = count($pledgePayments['values']) - 1;
    $this->assertEqualsWithDelta(8.37, $pledgePayments['values'][$last_pp_idx]['scheduled_amount'], 0.01);

    // Does all sorts of shenanigans if the amount was not the expected amount,
    // and this is what we really want to test in this function.
    // This tests an old bug where, given a pledge of 100 in 12 installments,
    // the last pledge payment would have 4¢ left and still be pending.
    CRM_Contribute_BAO_Contribution::updateRelatedPledge(
      CRM_Core_Action::ADD,
      $pledgePayments['values'][0]['id'],
      $contributionID,
    // adjustTotalAmount
      NULL,
      100.00,
      100.00,
    // contribution_status_id
      1,
    // original_contribution_status_id
      NULL
    );

    // Fetch the pledge payments again to see if the amounts and statuses
    // have been updated correctly.
    $pledgePayments = $this->callAPISuccess('pledge_payment', 'get', [
      'pledge_id' => $pledge['id'],
      'sequential' => 1,
    ]);

    foreach ($pledgePayments['values'] as $key => $pp) {
      if ($key == 0) {
        // First pledge payment has the full amount.
        $this->assertEquals(8.33, $pp['scheduled_amount']);
        $this->assertEquals(100.00, $pp['actual_amount']);
      }
      else {
        $this->assertEquals(0, $pp['scheduled_amount']);
        $this->assertEquals(0, $pp['actual_amount']);
      }

      // All pledge payments must be set as 'completed'.
      $this->assertEquals(1, $pp['status_id']);
    }

    $this->assertEquals(count($pledgePayments['values']), CRM_Pledge_BAO_Pledge::pledgeHasFinancialTransactions($pledge['id'], 2));
  }

}
