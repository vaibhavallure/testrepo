<?php

class Ebizmarts_BakerlooRestful_Model_Api_Shifts extends Ebizmarts_BakerlooRestful_Model_Api_Api
{

    protected $_model = "bakerloo_restful/shift";

    protected function _getIndexId()
    {
        return 'id';
    }

    public function _createDataObject($id = null, $data = null)
    {
        $result = array();

        if (is_null($data)) {
            $data = Mage::getModel($this->_model)->load($id);
        }

        if ($data->getId()) {
            $activities   = $data->getActivities();
            foreach ($activities as $_i => $_activity) {
                $activities[$_i] = $_activity->getData();

                unset($activities[$_i]['shift_id']);
                unset($activities[$_i]['payment_method']);

                $activities[$_i]['comments'] = (string)$activities[$_i]['comments'];

                if ($_activity->getType() == Ebizmarts_BakerlooRestful_Model_Activity::TYPE_OPEN_SHIFT) {
                    $activities[$_i]['open'] = true;
                    $activities[$_i]['is_adjustment'] = false;
                } elseif ($_activity->getType() == Ebizmarts_BakerlooRestful_Model_Activity::TYPE_CLOSE_SHIFT) {
                    $activities[$_i]['open'] = false;
                    $activities[$_i]['is_adjustment'] = false;
                } else {
                    $activities[$_i]['open'] = false;
                    $activities[$_i]['is_adjustment'] = true;
                }

                $activities[$_i]['movements'] = array();
                foreach ($_activity->getMovements() as $_mov) {
                    $activities[$_i]['movements'][] = array(
                        "amount"        => (float)$_mov->getAmount(),
                        "refunds"       => (float)$_mov->getRefunds(),
                        "balance"       => (float)$_mov->getBalance(),
                        "currency_code" => (string)$_mov->getCurrencyCode()
                    );
                }
            }

            $transactions = $data->getTransactions();
            foreach ($transactions as $_i => $_transaction) {
                $transactions[$_i] = $_transaction->getData();

                unset($transactions[$_i]['shift_id']);
                unset($transactions[$_i]['type']);

                $transactions[$_i]['comments'] = (string)$transactions[$_i]['comments'];

                $transactions[$_i]['movements'] = array();
                foreach ($_transaction->getMovements() as $_mov) {
                    $transactions[$_i]['movements'][] = array(
                        "amount"        => (float)$_mov->getAmount(),
                        "refunds"       => (float)$_mov->getRefunds(),
                        "balance"       => (float)$_mov->getBalance(),
                        "currency_code" => (string)$_mov->getCurrencyCode()
                    );
                }
            }

            $shiftJson = json_decode($data->getJsonPayload(), true);
            $result = array(
                "shift" => array(
                    "id"                => (int)$data->getId(),
                    "guid"              => $data->getShiftGuid(),
                    "device_shift_id"   => (int)$data->getDeviceShiftId(),
                    "device_id"         => $data->getDeviceId(),
                    "state"             => (int)$data->getState(),
                    "user"              => $data->getUser(),
                    "opened_date"       => $data->getOpenDate(),
                    "opened_notes"      => $data->getOpenNotes(),
                    "opened_amounts"    => $data->getOpenAmounts(),
                    "opened_currencies" => $data->getOpenCurrencies(),
                    "closed_date"       => $data->getCloseDate(),
                    "closed_notes"      => $data->getCloseNotes(),
                    "closed_amounts"    => $data->getCloseAmounts(),
                    "closed_currencies" => $data->getCloseCurrencies(),
                    "vat_breakdown"     => $data->getVatBreakdown(),
                    "sales_amount"      => (float)$data->getSalesAmount(),
                    "sales_amount_currency" => $data->getSalesAmountCurrency(),
                    "nextday_currencies" => $data->getNextdayCurrencies()
                ),
                "transactions"      => array_values($transactions),
                "activities"        => array_values($activities),
                "json"              => $shiftJson
            );
        }

        return $result;
    }

    public function post()
    {
        parent::post();

        $h = Mage::helper('bakerloo_restful');

        if (!$this->getStoreId()) {
            Mage::throwException($h->__('Please provide a Store ID.'));
        }

        $data = $this->getJsonPayload(true);
        if (!isset($data['shift'])) {
            Mage::throwException($h->__('No shift data provided.'));
        }

        $shiftData = $data['shift'];
        $deviceId = $this->getDeviceId();

        $shift = $this->getShiftModel()->load($shiftData['guid'], 'shift_guid');
        if ($shift->getId()) {
            Mage::throwException("Duplicate POST for `{$shiftData['guid']}`.");
        }

        $openCurrencies = $shiftData['json_open_currencies'] ? $shiftData['json_open_currencies'] : json_encode($shiftData['opened_currencies']);
        $closeCurrencies = $shiftData['json_closed_currencies'] ? $shiftData['json_closed_currencies'] : json_encode($shiftData['closed_currencies']);
        $nextdayCurrencies = isset($shiftData['json_nextday_currencies']) and $shiftData['json_nextday_currencies'] ? $shiftData['json_nextday_currencies'] : json_encode($shiftData['nextday_currencies']);
        $vatBreakdown = $shiftData['json_vatbreakdown'] ? $shiftData['json_vatbreakdown'] : json_encode($shiftData['vatbreakdown']);

        $shift->setShiftGuid($shiftData['guid'])
            ->setDeviceShiftId($shiftData['id'])
            ->setDeviceId($deviceId)
            ->setUser($shiftData['user'])
            ->setOpenDate($shiftData['opened_date'])
            ->setOpenNotes($shiftData['opened_notes'])
            ->setJsonOpenCurrencies($openCurrencies)
            ->setCloseDate($shiftData['closed_date'])
            ->setCloseNotes($shiftData['closed_notes'])
            ->setJsonCloseCurrencies($closeCurrencies)
            ->setCountedAmount($shiftData['counted_amount'])
            ->setState($shiftData['state'])
            ->setSalesAmountCurrency($shiftData['sales_amount_currency'])
            ->setJsonVatbreakdown($vatBreakdown)
            ->setJsonNextdayCurrencies($nextdayCurrencies)
            ->setJsonPayload($this->getRequest()->getRawBody())
            ->save();

        $salesAmt = 0;

        $activities = $data['activities'];
        foreach ($activities as $_activity) {
            $activity = $this->getShiftActivity()
                ->setShiftId($shift->getId())
                ->setActivityDate($_activity['date'])
                ->setComment($_activity['comment'])
                ->setType($_activity['activity'])
                ->save();

            foreach ($_activity['movements'] as $_movement) {
                $this->getShiftMovement()
                    ->setActivityId($activity->getId())
                    ->setShiftId($shift->getId())
                    ->setCurrencyCode($_movement['currency_code'])
                    ->setAmount($_movement['amount'])
                    ->setRefunds($_movement['refunds'])
                    ->setBalance($_movement['balance'])
                    ->save();

                $salesAmt += $_movement['amount'];
            }
        }

        $transactions = $shiftData['transactions'];
        foreach ($transactions as $transaction) {
            $orders = implode(', ', $transaction['orders']);
            $comment = empty($orders) ? '' : $h->__("Orders: %s", $orders);

            $payment = "[{$transaction['payment_code']}] \n {$transaction['payment_title']}";

            $activity = $this->getShiftActivity()
                ->setShiftId($shift->getId())
                ->setActivityDate($transaction['date'])
                ->setType(Ebizmarts_BakerlooRestful_Model_Activity::TYPE_TRANSACTION)
                ->setPaymentMethod($payment)
                ->setComments($comment)
                ->save();


            $this->getShiftMovement()
                ->setActivityId($activity->getId())
                ->setShiftId($shift->getId())
                ->setCurrencyCode($transaction['currency_code'])
                ->setAmount($transaction['total'])
                ->setRefunds($transaction['total_refunds'])
                ->save();

            $salesAmt += $transaction['total'];
        }

//        $adjustments = $data->adjustments;
//        foreach($adjustments as $adjustment) {
//            $activity = Mage::getModel('bakerloo_restful/shift_activity')
//                ->setShiftId($shift->getId())
//                ->setActivityDate($adjustment->date)
//                ->setType(Ebizmarts_BakerlooRestful_Model_Activity::TYPE_ADJUSTMENT)
//                ->setComments($adjustment->notes)
//                ->save();
//
//
//            Mage::getModel('bakerloo_restful/shift_movement')
//                ->setActivityId($activity->getId())
//                ->setShiftId($shift->getId())
//                ->setCurrencyCode($adjustment->currency_code)
//                ->setAmount($adjustment->amount)
//                ->setBalance($adjustment->balance)
//                ->save();
//
//            $salesAmt += $adjustment->amount;
//
//        }

        $shift->setSalesAmount($salesAmt)
            ->save();

        return $this->_createDataObject(null, $shift);
    }

    public function getShiftModel()
    {
        return Mage::getModel($this->_model);
    }

    public function getShiftActivity()
    {
        return Mage::getModel('bakerloo_restful/shift_activity');
    }

    public function getShiftMovement()
    {
        return Mage::getModel('bakerloo_restful/shift_movement');
    }
}
