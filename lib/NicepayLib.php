<?php 
/*
 * ____________________________________________________________
 *
 * Copyright (C) 2016 NICE IT&T
 *
 * Please do not modify this module.
 * This module may used as it is, there is no warranty.
 *
 * @ description : PHP SSL Client module.
 * @ name        : NicepayLite.php
 * @ author      : NICEPAY I&T (tech@nicepay.co.kr)
 * @ date        : 
 * @ modify      : 09.03.2016
 *
 * 09.03.2016 Update Log
 * Please contact it.support@ionpay.net for inquiry
 *
 * ____________________________________________________________
 */

include_once('NicepayRequestor.php');
include_once('NicepayLogger.php');

class NicepayLib {
    public $tXid;
    public $authNo;
    public $bankVacctNo;
    public $resultCd;
    public $resultMsg;

    public $iMid = NICEPAY_IMID;
    public $callBackUrl = NICEPAY_CALLBACK_URL;
    public $dbProcessUrl = NICEPAY_DBPROCESS_URL;
    public $merchantKey = NICEPAY_MERCHANT_KEY;
    public $cartData;

    public $requestData = array ();
    public $resultData = array ();
    public $log;
    public $debug;

    public $request;

    public function __construct() {
        $this->request = new NicepayRequestor();
        $this->log = new NicepayLogger();
    }

    public function getUserIP() {
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }
        return $ip;
    }

    public function oneLiner($string) {
        // Return string in one line, remove new lines and white spaces
        return preg_replace(array('/\n/','/\n\r/','/\r\n/','/\r/','/\s+/','/\s\s*/'), ' ', $string);
    }

    public function extractNotification($name) {
        if (is_array($name))
        {
            foreach($name as $value)
            {
                if (isset($_REQUEST[$value]))
                {
                    $this->notification[$value] = $_REQUEST[$value];
                }
                else
                {
                    $this->notification[$value] = null;
                }
            }

        }
        elseif (isset($_REQUEST[$name]))
        {
            $this->notification[$name] = $_REQUEST[$name];
        }
        else
        {
            $this->notification[$name] = null;
        }
    }

    public function getNotification($name) {
        return $this->notification[$name];
    }

    public function merchantToken() {
        // SHA256( Concatenate(iMid + referenceNo + amt + merchantKey) )
        return hash('sha256',   $this->get('iMid').
                                $this->get('referenceNo').
                                $this->get('amt').
                                $this->merchantKey
        );
    }

    public function merchantTokenC() {
        // SHA256( Concatenate(iMid + referenceNo + amt + merchantKey) )
        return hash('sha256',   $this->get('iMid').
            $this->get('tXid').
            $this->get('amt').
            $this->merchantKey
        );
    }

    // Set POST parameter name and its value
    public function set($name, $value) {
        $this->requestData[$name] = $value;
    }

    // Retrieve POST parameter value
    public function get($name)
    {
        if (isset($this->requestData[$name])) {
        return $this->requestData[$name];
        }
        return "";
    }

    // Request VA
    public function requestVA() {
        // Populate data
        $this->set('iMid', $this->iMid);
        $this->set('merchantToken', $this->merchantToken());
        $this->set('dbProcessUrl', $this->dbProcessUrl);
        $this->set('callBackUrl', $this->callBackUrl);
        $this->set('instmntMon', '1');
        $this->set('instmntType', '1');
        $this->set('userIP', $this->getUserIP());
        $this->set('goodsNm', $this->get('description'));
        $this->set('vat', '0');
        $this->set('fee', '0');
        $this->set('notaxAmt', '0');
        if ($this->get('cartData')  == "") {
            $this->set('cartData', '{}');
        }
//        $this->set('cartData', '{}');

        // Check Parameter
        $this->checkParam('iMid', '01');
        $this->checkParam('payMethod', '02');
        $this->checkParam('currency', '03');
        $this->checkParam('amt', '02');
        $this->checkParam('instmntMon', '05');
        $this->checkParam('referenceNo', '06');
        $this->checkParam('goodsNm', '07');
        $this->checkParam('billingNm', '08');
        $this->checkParam('billingPhone', '09');
        $this->checkParam('billingEmail', '10');
        $this->checkParam('billingAddr', '11');
        $this->checkParam('billingCity', '12');
        $this->checkParam('billingState', '13');
        $this->checkParam('billingCountry', '14');
        $this->checkParam('deliveryNm', '15');
        $this->checkParam('deliveryPhone', '16');
        $this->checkParam('deliveryAddr', '17');
        $this->checkParam('deliveryCity', '18');
        $this->checkParam('deliveryState', '19');
        $this->checkParam('deliveryPostCd', '20');
        $this->checkParam('deliveryCountry', '21');
        $this->checkParam('callBackUrl', '22');
        $this->checkParam('dbProcessUrl', '23');
        $this->checkParam('vat', '24');
        $this->checkParam('fee', '25');
        $this->checkParam('notaxAmt', '26');
        $this->checkParam('description', '27');
        $this->checkParam('merchantToken', '28');
        $this->checkParam('bankCd', '29');

        // Send Request
        $this->request->operation('requestVA');
        $this->request->openSocket();
        $this->resultData = $this->request->apiRequest($this->requestData);
        unset($this->requestData);
        return $this->resultData;
    }

    // Charge Credit Card
    public function chargeCard() {
        // Populate data
        $this->set('iMid', $this->iMid);
        $this->set('merchantToken', $this->merchantToken());
        $this->set('dbProcessUrl', $this->dbProcessUrl);
        $this->set('callBackUrl', $this->callBackUrl);
        $this->set('instmntMon', '1');
        $this->set('instmntType', '1');
        $this->set('userIP', $this->getUserIP());
        $this->set('goodsNm', $this->get('description'));
        $this->set('vat', '0');
        $this->set('fee', '0');
        $this->set('notaxAmt', '0');
        if ($this->get('cartData')  == "") {
            $this->set('cartData', '{}');
        }
//        var_dump($this->requestData);
//        exit();
//        $this->set('cartData', '{}');

        // Check Parameter
        $this->checkParam('iMid', '01');
        $this->checkParam('payMethod', '01');
        $this->checkParam('currency', '03');
        $this->checkParam('amt', '02');
        $this->checkParam('instmntMon', '05');
        $this->checkParam('referenceNo', '06');
        $this->checkParam('goodsNm', '07');
        $this->checkParam('billingNm', '08');
        $this->checkParam('billingPhone', '09');
        $this->checkParam('billingEmail', '10');
        $this->checkParam('billingAddr', '11');
        $this->checkParam('billingCity', '12');
        $this->checkParam('billingState', '13');
        $this->checkParam('billingCountry', '14');
        $this->checkParam('deliveryNm', '15');
        $this->checkParam('deliveryPhone', '16');
        $this->checkParam('deliveryAddr', '17');
        $this->checkParam('deliveryCity', '18');
        $this->checkParam('deliveryState', '19');
        $this->checkParam('deliveryPostCd', '20');
        $this->checkParam('deliveryCountry', '21');
        $this->checkParam('callBackUrl', '22');
        $this->checkParam('dbProcessUrl', '23');
        $this->checkParam('vat', '24');
        $this->checkParam('fee', '25');
        $this->checkParam('notaxAmt', '26');
        $this->checkParam('description', '27');
        $this->checkParam('merchantToken', '28');

//        print_r($this->requestData);
//        exit();
//       Send Request
        $this->request->operation('creditCard');
        $this->request->openSocket();
        $this->resultData = $this->request->apiRequest($this->requestData);
        unset($this->requestData);
        return $this->resultData;
    }

    public function checkPaymentStatus($tXid, $referenceNo, $amt) {
        // Populate data
        $this->set('iMid', $this->iMid);
        $this->set('merchantToken', $this->merchantToken());
        $this->set('tXid', $tXid);
        $this->set('referenceNo', $referenceNo);
        $this->set('amt', $amt);

        // Check Parameter
        $this->checkParam('iMid', '01');
        $this->checkParam('amt', '04');
        $this->checkParam('referenceNo', '06');
        $this->checkParam('merchantToken', '28');
        $this->checkParam('tXid', '36');

        // Send Request
        $this->request->operation('checkPaymentStatus');
        $this->request->openSocket();
        $this->resultData = $this->request->apiRequest($this->requestData);
        unset($this->requestData);
        return $this->resultData;
    }

    // Cancel VA (VA can be canceled only if VA status is not paid)
    public function cancelVA($tXid, $amt) {
        // Populate data
        $this->set('iMid', $this->iMid);
        $this->set('merchantToken', $this->merchantTokenC());
        $this->set('tXid', $tXid);
        $this->set('amt', $amt);

        // Check Parameter
        $this->checkParam('iMid', '01');
        $this->checkParam('amt', '04');
        $this->checkParam('merchantToken', '28');
        $this->checkParam('tXid', '36');

        // Send Request
        $this->request->operation('cancelVA');
        $this->request->openSocket();
        $this->resultData = $this->request->apiRequest($this->requestData);
        unset($this->requestData);
        return $this->resultData;
    }

    public function checkParam($requestData, $errorNo)
    {
        if (null == $this->get($requestData))
        {
            die($this->getError($errorNo));
        }
    }

    public function getError($id)
    {
        $error = array(

            // That always Unknown Error :)
            '00' =>   array(
                'errorCode'    => '00000',
                'errorMsg' => 'Unknown error. Contact it.support@ionpay.net.'
            ),
            // General Mandatory parameters
            '01' =>   array(
                'error'    => '10001',
                'errorMsg' => '(iMid) is not set. Please set (iMid).'
            ),
            '02' =>   array(
                'error'    => '10002',
                'errorMsg' => '(payMethod) is not set. Please set (payMethod).'
            ),
            '03' =>   array(
                'error'    => '10003',
                'errorMsg' => '(currency) is not set. Please set (currency).'
            ),
            '04' =>   array(
                'error'    => '10004',
                'errorMsg' => '(amt) is not set. Please set (amt).'
            ),
            '05' =>   array(
                'error'    => '10005',
                'errorMsg' => '(instmntMon) is not set. Please set (instmntMon).'
            ),
            '06' =>   array(
                'error'    => '10006',
                'errorMsg' => '(referenceNo) is not set. Please set (referenceNo).'
            ),
            '07' =>   array(
                'error'    => '10007',
                'errorMsg' => '(goodsNm) is not set. Please set (goodsNm).'
            ),
            '08' =>   array(
                'error'    => '10008',
                'errorMsg' => '(billingNm) is not set. Please set (billingNm).'
            ),
            '09' =>   array(
                'error'    => '10009',
                'errorMsg' => '(billingPhone) is not set. Please set (billingPhone).'
            ),
            '10' =>   array(
                'error'    => '10010',
                'errorMsg' => '(billingEmail) is not set. Please set (billingEmail).'
            ),
            '11' =>   array(
                'error'    => '10011',
                'errorMsg' => '(billingAddr) is not set. Please set (billingAddr).'
            ),
            '12' =>   array(
                'error'    => '10012',
                'errorMsg' => '(billingCity) is not set. Please set (billingCity).'
            ),
            '13' =>   array(
                'error'    => '10013',
                'errorMsg' => '(billingState) is not set. Please set (billingState).'
            ),
            '14' =>   array(
                'error'    => '10014',
                'errorMsg' => '(billingCountry) is not set. Please set (billingCountry).'
            ),
            '15' =>   array(
                'error'    => '10015',
                'errorMsg' => '(deliveryNm) is not set. Please set (deliveryNm).'
            ),
            '16' =>   array(
                'error'    => '10016',
                'errorMsg' => '(deliveryPhone) is not set. Please set (deliveryPhone).'
            ),
            '17' =>   array(
                'error'    => '10017',
                'errorMsg' => '(deliveryAddr) is not set. Please set (deliveryAddr).'
            ),
            '18' =>   array(
                'error'    => '10018',
                'errorMsg' => '(deliveryCity) is not set. Please set (deliveryCity).'
            ),
            '19' =>   array(
                'error'    => '10019',
                'errorMsg' => '(deliveryState) is not set. Please set (deliveryState).'
            ),
            '21' =>   array(
                'error'    => '10020',
                'errorMsg' => '(deliveryPostCd) is not set. Please set (deliveryPostCd).'
            ),
            '22' =>   array(
                'error'    => '10021',
                'errorMsg' => '(deliveryCountry) is not set. Please set (deliveryCountry).'
            ),
            '23' =>   array(
                'error'    => '10022',
                'errorMsg' => '(callBackUrl) is not set. Please set (callBackUrl).'
            ), '8' =>   array(
                'error'    => '10023',
                'errorMsg' => '(dbProcessUrl) is not set. Please set (dbProcessUrl).'
            ),
            '24' =>   array(
                'error'    => '10024',
                'errorMsg' => '(vat) is not set. Please set (vat).'
            ),
            '25' =>   array(
                'error'    => '10025',
                'errorMsg' => '(fee) is not set. Please set (fee).'
            ),
            '26' =>   array(
                'error'    => '10026',
                'errorMsg' => '(notaxAmt) is not set. Please set (notaxAmt).'
            ),
            '27' =>   array(
                'error'    => '10027',
                'errorMsg' => '(description) is not set. Please set (description).'
            ),
            '28' =>   array(
                'error'    => '10028',
                'errorMsg' => '(merchantToken) is not set. Please set (merchantToken).'
            ),
            '29' =>   array(
                'error'    => '10029',
                'errorMsg' => '(bankCd) is not set. Please set (bankCd).'
            ),

            // Mandatory parameters to Check Order Status
            '30' =>   array(
                'error'    => '10030',
                'errorMsg' => '(tXid) is not set. Please set (tXid).'
            )

        );
        return (json_encode($this->oneLiner($error[$id])));
    }

}
