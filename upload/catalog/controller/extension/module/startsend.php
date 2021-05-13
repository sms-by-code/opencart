<?php

class ControllerExtensionModuleStartsend extends Controller
{

    /**
     * Gate
     * @var string
     */
    public $gate = 'https://app.sms.by/v1';

    // Api request methods and URLs
    public static $apiMethods = [
        'get_balance' => ['method' => 'GET', 'url' => '/api/v1/getBalance'],
        'financial_statistics' => ['method' => 'GET', 'url' => '/api/v1/financialStatistics'],
        'get_token' => ['method' => 'POST', 'url' => '/remote-api/login'],
        'register' => ['method' => 'POST', 'url' => '/remote-api/register'],
        'get_api_key' => ['method' => 'POST', 'url' => '/remote-api/getApiKey'],
        'recovery_password' => ['method' => 'GET', 'url' => '/remote-api/recovery-password'],
        'get_alphanames' => ['method' => 'GET', 'url' => '/api/v1/getAlphanames'],
        'get_alphaname_id' => ['method' => 'GET', 'url' => '/api/v1/getAlphanameId'],
        'get_alphaname_category' => ['method' => 'GET', 'url' => '/api/v1/getAlphanameCategory'],
        'create_alphaname' => ['method' => 'POST', 'url' => '/api/v1/createAlphaname'],
        'edit_password' => ['method' => 'POST', 'url' => '/api/v1/editPassword'],
        'edit_notification' => ['method' => 'POST', 'url' => '/api/v1/editNotification'],
        'requisites' => ['method' => 'POST', 'url' => '/api/v1/requisites'],
        'get_requisites' => ['method' => 'POST', 'url' => '/api/v1/getRequisites'],
        'send_quick_sms' => ['method' => 'GET', 'url' => '/api/v1/sendQuickSMS'],
        'create_cms_message' => ['method' => 'GET', 'url' => '/api/v1/createSmsMessage'],
        'send_sms' => ['method' => 'GET', 'url' => '/api/v1/sendSMS'],
        'get_messages_list' => ['method' => 'GET', 'url' => '/api/v1/getMessagesList'],
        'get_sms_by_list' => ['method' => 'GET', 'url' => '/api/v1/getSmsByList'],
        'get_sms_by_list_id' => ['method' => 'GET', 'url' => '/api/v1/getSmsByListId'],
        'send_sms_list' => ['method' => 'GET', 'url' => '/api/v1/sendSmsList'],
    ];


    /**
     * On checkout perform orders
     * @param int $order
     */
    public function onCheckout($order = 0)
    {
        if (is_array($order)) {
            $order_id = $order['order_id'];
        } elseif (($order == 0) && (isset($this->session->data['order_id']))) {
            $order_id = $this->session->data['order_id'];
        } elseif ($order) {
            $order_id = $order;
        }

        if (!is_int($order_id)) {
            return;
        }

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('startsend');
        $sms_log = (isset($setting['startsend-log'])) ? $setting['startsend-log'] : 0;

        if (isset($setting) && ($setting['startsend-enabled']) && (!empty($setting['startsend-apitoken']))) {
            $total = $this->currency->convert($order_info['total'], $order_info['currency_code'], $order_info['currency_code']);
            if (isset($setting['startsend-owner']) && ($setting['startsend-owner'] == 'on')) {
                $original = array("{StoreName}", "{OrderID}", "{Total}", "{LastName}", "{FirstName}", "{Phone}", "{City}", "{Address}", "{Comment}");
                $replace = array($this->config->get('config_name'), $order_id, $total, $order_info['lastname'], $order_info['firstname'], $order_info['telephone'], $order_info['shipping_city'], $order_info['shipping_address_1'], $order_info['comment']);

                $message = str_replace($original, $replace, $setting['startsend-message-admin']);

                $phones = explode(',', $setting['startsend-phone']);
                foreach ($phones as $phone) {
                    $this->sendSMS($phone, $message);
                }
            }
            if (isset($setting['startsend-new-order']) && ($setting['startsend-new-order'] == 'on')) {
                $original = array("{StoreName}", "{OrderID}", "{LastName}", "{FirstName}", "{Total}");
                $replace = array($this->config->get('config_name'), $order_id, $order_info['lastname'], $order_info['firstname'], $total);

                $message = str_replace($original, $replace, $setting['startsend-message-customer']);
                $phone = preg_replace("/[^0-9]/", '', $order_info['telephone']);

                if (preg_match('/(\+|)[0-9]{11,12}/', $phone)) {
                    $this->sendSMS($phone, $message);
                }
            }
        }
    }

    /**
     * On order status change
     * @param int $order
     */
    public function onHistoryChange($order = 0)
    {

        if (is_array($order)) {
            $order_id = $order['order_id'];
        } elseif (($order == 0) && (isset($this->session->data['order_id']))) {
            $order_id = $this->session->data['order_id'];
        } elseif (($order == 0) && (isset($this->request->get['order_id']))) {
            $order_id = $this->request->get['order_id'];
        } elseif (($order == 0) && (isset($this->request->post['order_id']))) {
            $order_id = $this->request->post['order_id'];
        } elseif ($order != 0) {
            $order_id = $order;
        } else {
            return;
        }

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $this->load->model('setting/setting');
        $this->load->model('extension/module/startsend');

        $setting = $this->model_setting_setting->getSetting('startsend');
        $sms_log = (isset($setting['startsend-log'])) ? $setting['startsend-log'] : 0;

        if (isset($setting) && ($setting['startsend-enabled']) && (!empty($setting['startsend-apitoken'])) || ((isset($setting['startsend-new-order'])) && ($setting['startsend-new-order'] == 'on')) || ((isset($setting['startsend-owner'])) && ($setting['startsend-owner'] == 'on'))) {

            if ($this->model_extension_module_startsend->getHistoryCount($order_id) > 1) {

                $history = $this->model_extension_module_startsend->getHistory($order_id);
                $total = $this->currency->convert($order_info['total'], $order_info['currency_code'], $order_info['currency_code']);
                $status = (isset($order_info['order_status'])) ? $order_info['order_status'] : "";

                $original = array("{StoreName}", "{OrderID}", "{Status}", "{LastName}", "{FirstName}", "{Total}", "{Comment}");
                $replace = array($this->config->get('config_name'), $order_id, $status, $order_info['lastname'], $order_info['firstname'], $total, $history['comment']);

                $phone = preg_replace("/[^0-9]/", '', $order_info['telephone']);

                foreach ($setting['startsend-order-change'] as $order_status_id => $ssoc) {
                    if ($ssoc === 'on' && $setting['startsendmessagetemplate'][$order_status_id]) {
                        $whatChange = $this->model_extension_module_startsend->needToSendSMS($order_id, $order_status_id);

                        $message = str_replace($original, $replace, $setting['startsendmessagetemplate'][$order_status_id]);
                        if (isset($setting['startsend-order-change'][$order_status_id]) && ($setting['startsend-order-change'][$order_status_id] == 'on') && $whatChange['order_history_id']) {
                            if (preg_match('/(\+|)[0-9]{11,12}/', $phone)) {
                                $this->sendSMS($phone, $message);
                                $this->model_extension_module_startsend->changeSMSNotify($whatChange['order_history_id']);
                                return;
                            }
                        }
                    }
                }

                if (isset($setting['startsend-order-change-notice']) && ($setting['startsend-order-change-notice'] == 'on') && ($history['notify'])) {
                    $ok = 1;
                } elseif ((isset($setting['startsend-order-change-notice'])) && ($setting['startsend-order-change-notice'] == 'on') && (!$history['notify'])) {
                    $ok = 0;
                } elseif ((!isset($setting['startsend-order-change-notice'])) && (isset($setting['startsend-order-change'])) && ($setting['startsend-order-change'] == 'on')) {
                    $ok = 1;
                } elseif (!isset($setting['startsend-order-change'])) {
                    $ok = 0;
                } else {
                    $ok = 1;
                }

                $message = str_replace($original, $replace, $setting['startsendmessagetemplate']);

                if ((preg_match('/(\+|)[0-9]{11,12}/', $phone)) && ($ok)) {
                    $this->sendSMS($phone, $message);
                }
            }
        }
    }

    /**
     * @param $api_id
     * @param int $to
     * @param int $text
     * @param string $sender
     * @param int $logRec
     * @return bool
     */
    private function sms_send($api_id, $to = 0, $text = 0, $sender = '', $logRec = 0)
    {
        if (extension_loaded('curl')) {
            $param = array(
                "api_id" => $api_id,
                "to" => $to,
                "text" => $text,
                "from" => $sender,
                "json" => 1,
                "partner_id" => 34316);
            $ch = curl_init("http://sms.ru/sms/send");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            $result = file_get_contents('http://sms.ru/sms/send?api_id=' . $api_id . '&to=' . $to . '$text=' . $text . '&from=' . $sender . '&partner_id=34316&json=1');
        }

        $send_data = json_decode($result, true);

        if ($logRec) {
            $this->load->model('extension/module/startsend');
            $to_log = array();

            if ($send_data['status_code'] == 100) {
                $to_log['error'] = $send_data['sms'][$to]['status_code'];
                $to_log['smsru'] = $send_data['sms'][$to]['sms_id'];
            } else {
                $to_log['error'] = $send_data['status_code'];
                $to_log['smsru'] = 0;
            }

            $to_log['phone'] = $to;
            $to_log['text'] = $text;
            $this->model_extension_module_startsend->setLogRecord($to_log);
        }
        return true;
    }


    /**
     * Sms send
     * @param $text
     * @param int $setting
     * @return array
     */
    private function sendSMS($phone, $message)
    {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('startsend');
        $url = $settings['startsend-gate'].self::$apiMethods['send_quick_sms']['url'];
        $method = self::$apiMethods['send_quick_sms']['method'];
        $response = json_decode($this->curl($url, $method, ['phone' => $phone, 'message' => $message]), true);
        if ($settings['startsend-log'] === 'on' && isset($response['sms_id'])) {
            $log = ['status' => $response['status'], 'sms_id' => $response['sms_id'], 'phone' => $phone, 'text' => $message];
            $this->toLog($log);
        }
        return $response;
    }

    /**
     * Send to lod function
     * @param $log
     */
    private function toLog($log)
    {
        $this->load->model('extension/module/startsend');
        $this->model_extension_module_startsend->setLogRecord($log);
    }

    /**
     * Make request
     * @param $url
     * @param $method
     * @param array $post_data
     * @return string[]
     */
    private function curl($url, $method, $post_data = [])
    {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('startsend');

        $post_data = !is_null($post_data) && !empty($post_data) ?
            array_merge($post_data, ['token' => $settings['startsend-apitoken']]) :
            ['token' => $settings['startsend-apitoken']];

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_USERAGENT, "OpenCart CMS");
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
            curl_setopt($curl, CURLOPT_URL, $url);
        } else if ($method === 'GET') {
            curl_setopt($curl, CURLOPT_POST, false);
            curl_setopt($curl, CURLOPT_URL, $url . '?' . http_build_query($post_data));
        }

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $curl_error =
                "SMS.BY cURL Error " . curl_errno($curl) . ": " . curl_error($curl);
        } else {
            $curl_error = "";
        }

        if ($curl_error) {
            $this->log->write($curl_error);
            return ["error" => $curl_error];
        }

        curl_close($curl);

        return $response;
    }

}