<?php namespace Octobro\Nicepay\PaymentTypes;

use Input;
use Flash;
use Redirect;
use Responsiv\Pay\Classes\GatewayBase;
use ApplicationException;
use Exception;
use Responsiv\Pay\Models\Invoice;
use Octobro\Nicepay\Lib\IonPay;
use Octobro\Nicepay\Models\IonPay as IonpayModel;

class NicePay extends GatewayBase
{

    /**
     * {@inheritDoc}
     */
    public function gatewayDetails()
    {
        return [
            'name'        => 'NicePay',
            'description' => 'NicePay payment method from NicePay.'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function defineFormFields()
    {
        return 'fields.yaml';
    }

    /**
     * {@inheritDoc}
     */
    public function defineValidationRules()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function initConfigData($host)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function registerAccessPoints()
    {
        return [];
    }

    /**
     * Status field options.
     */
    public function getDropdownOptions()
    {
        return $this->createInvoiceStatusModel()->listStatuses();
    }

    public function getFormAction($host)
    {
    }

    /**
     * Define post required and optional fields to NicePay
     *
     * @param Model $host Type model object containing configuration fields values.
     * @param Model $invoice Invoice model object.
     *
     * @return array $result
     */
    public function getHiddenFields($host, $invoice)
    {
        $result = [];

        // Credit Card
        if ($host->payment_method == '01') {
            $result['payMethod'] = $host->payment_method;

            return $result;
        }

        /**
         * Virtual Account
         **/
        $result['bankCd'] = $host->bank_code;
        $result['payMethod'] = $host->payment_method;

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function processPaymentForm($data, $host, $invoice)
    {
        $ionpay = new IonPay($data, $host, $invoice);

        $response = $ionpay->processPayment();

        if ( ! $this->saveResponse($response, $invoice)) {
            return;
        }

        if ( ! $redirectUrl = $this->getRedirectUrl($response)) {
            return;
        }

        $invoice->setUrlAttribute($redirectUrl);
    }

    private function saveResponse($response, $invoice)
    {
        $data = isset($response->data) ? $response->data : $response;

        if (isset($data->resultCd) && $data->resultCd == '0000') {
            $ionpay = $invoice->ionpay ?: IonpayModel::make();

            $ionpay->transaction_id = $data->tXid;
            $ionpay->payment_method = isset($data->payMethod) ? $data->payMethod : null;
            $ionpay->response_data = collect($response)->all();

            $invoice->ionpay()->save($ionpay);

            return true;
        }

        if (isset($data->resultCd)) {
            throw new \ApplicationException(sprintf('%s with error code: %s', $data->resultMsg, $data->resultCd));
        }

        throw new \ApplicationException('Connection timeout.');
    }

    private function getRedirectUrl($response)
    {
        // VA doesn't have data attribute and no need redirect url
        if ( ! isset($response->data)) {
            return false;
        }

        return $response->data->requestURL . "?tXid=" . $response->tXid;
    }

}
