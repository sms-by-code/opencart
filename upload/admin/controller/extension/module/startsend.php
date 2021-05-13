<?php

class ControllerExtensionModuleStartsend extends Controller
{
    private $data = array();

    private $status_array = array(
        'NEW' => 'New message',
    );

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

//    /**
//     *
//     * ControllerExtensionModuleStartsend constructor.
//     */
//    public function __construct() {
//        $this->load->model('setting/setting');
//        $data = $this->model_setting_setting->getSetting('startsend');
//        $this->gate = $data['startsend-gate'];
//        _parent::__construct();
//    }

    /**
     * Index setting func
     *
     */
    public function index()
    {
        $this->load->language('extension/module/startsend');
        $this->load->model('extension/module/startsend');
        $this->load->model('localisation/language');
        $this->load->model('setting/setting');

        $this->document->setTitle($this->language->get('heading_title'));

        if (!isset($this->request->get['store_id'])) {
            $this->request->get['store_id'] = 0;
        }

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            if (!$this->user->hasPermission('modify', 'extension/module/startsend')) {
                $this->error['warning'] = $this->language->get('error_permission');
                $this->session->data['error'] = 'You do not have permissions to edit this module!';
            } else {
                $this->model_setting_setting->editSetting('startsend', $this->request->post, 0);
                $this->session->data['success'] = $this->language->get('text_success');
            }
            $this->response->redirect(HTTP_SERVER . 'index.php?route=extension/module/startsend&store_id=' . $this->request->get['store_id'] . '&user_token=' . $this->session->data['user_token']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        $this->load->model('localisation/order_status');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'),
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/startsend', 'user_token=' . $this->session->data['user_token'], 'SSL'),
        );


        $data['module_startsend_status'] = $this->config->get('module_startsend_status');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');


        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');
        $this->data['button_test'] = $this->language->get('button_test');
        $this->data['button_send'] = $this->language->get('button_send');
        $this->data['button_refer'] = $this->language->get('button_refer');
        $this->data['button_orderid'] = $this->language->get('button_orderid');
        $this->data['button_storename'] = $this->language->get('button_storename');
        $this->data['button_name'] = $this->language->get('button_name');
        $this->data['button_lastname'] = $this->language->get('button_lastname');
        $this->data['button_phone'] = $this->language->get('button_phone');
        $this->data['button_city'] = $this->language->get('button_city');
        $this->data['button_address'] = $this->language->get('button_address');
        $this->data['button_comment'] = $this->language->get('button_comment');
        $this->data['button_status'] = $this->language->get('button_status');
        $this->data['button_total'] = $this->language->get('button_total');
        $this->data['button_download'] = $this->language->get('button_download');
        $this->data['button_clear'] = $this->language->get('button_clear');
        $this->data['button_filter'] = $this->language->get('button_filter');

        $this->data['tab_sending'] = $this->language->get('tab_sending');
        $this->data['tab_notice'] = $this->language->get('tab_notice');
        $this->data['tab_gate'] = $this->language->get('tab_gate');
        $this->data['tab_log'] = $this->language->get('tab_log');

        $this->data['entry_to'] = $this->language->get('entry_to');
        $this->data['entry_arbitrary'] = $this->language->get('entry_arbitrary');
        $this->data['entry_sender'] = $this->language->get('entry_sender');
        $this->data['entry_message'] = $this->language->get('entry_message');
        $this->data['entry_enabled'] = $this->language->get('entry_enabled');
        $this->data['entry_message_template'] = $this->language->get('entry_message_template');
        $this->data['entry_message_customer'] = $this->language->get('entry_message_customer');
        $this->data['entry_message_admin'] = $this->language->get('entry_message_admin');
        $this->data['entry_api_key'] = $this->language->get('entry_api_key');
        $this->data['entry_phone'] = $this->language->get('entry_phone');
        $this->data['entry_balance'] = $this->language->get('entry_balance');
        $this->data['entry_characters'] = $this->language->get('entry_characters');

        $this->data['text_description'] = $this->language->get('text_description');
        $this->data['text_newsletter'] = $this->language->get('text_newsletter');
        $this->data['text_all'] = $this->language->get('text_all');
        $this->data['text_all_group'] = $this->language->get('text_all_group');
        $this->data['text_newsletter_group'] = $this->language->get('text_newsletter_group');
        $this->data['text_new_order'] = $this->language->get('text_new_order');
        $this->data['text_order_change'] = $this->language->get('text_order_change');
        $this->data['text_order_change_notice'] = $this->language->get('text_order_change_notice');
        $this->data['text_owner'] = $this->language->get('text_owner');
        $this->data['text_enable'] = $this->language->get('text_enable');
        $this->data['text_disable'] = $this->language->get('text_disable');
        $this->data['text_money_add'] = $this->language->get('text_money_add');
        $this->data['text_refresh'] = $this->language->get('text_refresh');
        $this->data['text_log_disabled'] = $this->language->get('text_log_disabled');
        $this->data['text_arbitrary'] = $this->language->get('text_arbitrary');

        $this->data['help_message_template'] = $this->language->get('help_message_template');
        $this->data['help_message_customer'] = $this->language->get('help_message_customer');
        $this->data['help_message_admin'] = $this->language->get('help_message_admin');
        $this->data['help_message'] = $this->language->get('help_message');
        $this->data['help_sure'] = $this->language->get('help_sure');
        $this->data['help_arbitrary'] = $this->language->get('help_arbitrary');
        $this->data['help_callback'] = $this->language->get('help_callback');
        $this->data['help_phone'] = $this->language->get('help_phone');

        $this->data['entry_date_start'] = $this->language->get('entry_date_start');
        $this->data['entry_date_stop'] = $this->language->get('entry_date_stop');
        $this->data['entry_date_start'] = $this->language->get('entry_date_start');
        $this->data['entry_date_stop'] = $this->language->get('entry_date_stop');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_phone'] = $this->language->get('entry_phone');
        $this->data['entry_text'] = $this->language->get('entry_text');
        $this->data['entry_startsend_log'] = $this->language->get('entry_startsend_log');

        $this->data['error_warning'] = '';
        $this->data['action'] = $this->url->link('extension/module/startsend', 'user_token=' . $this->session->data['user_token'], 'SSL');
        $this->data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');

        $this->data['data'] = $this->model_setting_setting->getSetting('startsend');
        $this->data['balance'] = 0;
        $this->data['user_token'] = $this->session->data['user_token'];
        $this->data['log_href'] = $this->url->link('extension/module/startsend/log', 'user_token=' . $this->session->data['user_token']);

        $this->data['gates'] = $this->model_extension_module_startsend->getGates();
        $this->data['statuses'] = $this->status_array;

        $this->data['callback'] = str_replace("/admin", "", $this->url->link('api/smscallback', '', 'SSL'));

        $this->data['notice'] = ((isset($this->data['data']["startsend-order-change-notice"])) and ($this->data['data']["startsend-order-change-notice"])) ? '' : 'disable';

        $this->data['show_help'] = ((isset($this->data['data']['startsend-log'])) and ($this->data['data']['startsend-log'])) ? '' : 'show';

        if ($this->data['data']['startsend-apitoken'] != '') {
            $respond = json_decode($this->getBalance(), true);
            $this->data['balance'] = isset($respond['result']['0']['balance']) ? number_format($respond['result']['0']['balance'], 2, ',', '') . ' ' . $respond['currency'] : '-';
        }

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->load->model('customer/customer_group');
        $this->data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups(0);

        $this->data['header'] = $this->load->controller('common/header');
        $this->data['column_left'] = $this->load->controller('common/column_left');
        $this->data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/startsend', $this->data));
    }


    /**
     * Logging function available in ajax
     */
    public function log()
    {
        $this->load->language('extension/module/startsend');
        $this->data['column_date'] = $this->language->get('column_date');
        $this->data['column_text'] = $this->language->get('column_text');
        $this->data['column_sms_id'] = $this->language->get('column_sms_id');
        $this->data['column_phone'] = $this->language->get('column_phone');
        $this->data['column_status'] = $this->language->get('column_status');

        $this->data['statuses'] = $this->status_array;

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['filter_text'])) {
            $url .= '&filter_text=' . urlencode(html_entity_decode($this->request->get['filter_text'], ENT_QUOTES, 'UTF-8'));
            $filter_text = $this->request->get['filter_text'];
        } else {
            $filter_text = null;
        }

        if (isset($this->request->get['filter_phone'])) {
            $url .= '&filter_phone=' . urlencode(html_entity_decode($this->request->get['filter_phone'], ENT_QUOTES, 'UTF-8'));
            $filter_phone = $this->request->get['filter_phone'];
        } else {
            $filter_phone = null;
        }

        if (isset($this->request->get['filter_date_start'])) {
            $url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
            $filter_date_start = $this->request->get['filter_date_start'];
        } else {
            $filter_date_start = null;
        }

        if (isset($this->request->get['filter_date_stop'])) {
            $url .= '&filter_date_stop=' . $this->request->get['filter_date_stop'];
            $filter_date_stop = $this->request->get['filter_date_stop'];
        } else {
            $filter_date_stop = null;
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = null;
        }

        $this->data['text_no_results'] = $this->language->get('text_no_result');

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
            $url = '&sort=id&order=' . $order;
        } else {
            $sort = '';
        }

        if ($order == 'ASC') {
            $order = 'DESC';
        } else {
            $order = 'ASC';
        }
        $this->data['date'] = $this->url->link('extension/module/startsend/log', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=id&order=' . $order, true);

        $this->load->model('extension/module/startsend');

        $filter_data = array(
            'filter_text' => $filter_text,
            'filter_phone' => $filter_phone,
            'filter_date_start' => $filter_date_start,
            'filter_date_stop' => $filter_date_stop,
            'filter_status' => $filter_status,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $this->data['sends'] = $this->model_extension_module_startsend->getLogRecords($filter_data);
        $total = $this->model_extension_module_startsend->getLogRecordsTotal($filter_data);

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('extension/module/startsend/log', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $this->data['pagination'] = $pagination->render();

        $this->data['results'] = sprintf($this->language->get('text_pagination'), ($total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total - $this->config->get('config_limit_admin'))) ? $total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total, ceil($total / $this->config->get('config_limit_admin')));

        $this->response->setOutput($this->load->view('extension/module/startsend_log', $this->data));
    }

    /**
     * Installatiom
     */
    public function install()
    {
        $this->load->model('extension/module/startsend');
        $this->model_extension_module_startsend->install();
        $this->load->model('setting/event');

        $this->model_setting_event->addEvent('startsend', 'catalog/controller/checkout/success/before', 'extension/module/startsend/onCheckout');
        $this->model_setting_event->addEvent('startsend', 'catalog/model/checkout/order/addOrderHistory/after', 'extension/module/startsend/onHistoryChange');

        $this->load->model('setting/setting');
        $basic = array(
            'startsend-sender' => '',
            'startsend-phone' => '',
            'startsend-apitoken' => '',
            'startsendmessagetemplate' => 'Order №{OrderID} in {StoreName}, changed status to {Status}',
            'startsend-message-customer' => 'New order №{OrderID} in {StoreName}',
            'startsend-message-admin' => 'New order #{OrderID} at the store "{StoreName}". Total {Total}',
            'startsend-order-change' => 0,
            'startsend-order-change-notice' => 0,
            'startsend-new-order' => 0,
            'startsend-owner' => 0,
            'startsend-log' => 0,
            'startsend-enabled' => 0);
        $this->model_setting_setting->editSetting('startsend', $basic, 0);
        $this->model_setting_setting->editSetting('module', array('module_startsend_status'=>'1'));
    }

    /**
     * Uninstall function
     */
    public function uninstall()
    {
        $this->load->model('setting/setting');

        $settings = $this->model_setting_setting->getSetting('startsend');
        $url_callback = str_replace("/admin", "", $this->url->link('api/smscallback', '', 'SSL'));

        $this->model_setting_setting->deleteSetting('startsend_module', 0);
        $this->model_setting_setting->editSetting('module', array('module_startsend_status'=>'0'));
        $this->load->model('extension/module/startsend');
        $this->model_extension_module_startsend->uninstall();
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEvent('startsend');
    }

    /**
     * Send message function
     */
    public function send()
    {
        $json = array();

        $resp = [];

        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('startsend');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if (!$this->user->hasPermission('modify', 'extension/module/startsend')) {
                $json['error'] = 403;
                $json['text'] = 'You do not have permission to perform this action!';
            }

            if (!$this->request->post['message']) {
                $json['error'] = 404;
                $json['text'] = 'The message field should not be empty!';
            }

            if (!$this->request->post['to']) {
                $json['error'] = 404;
                $json['text'] = 'The phone field should not be empty!';
            }

            if (!$json) {
                $phones = explode(",", $this->request->post['to']);
                foreach ($phones as $value) {
                    $phone = trim($value);
                    if ($phone) {
                        $respItem = json_decode($this->sendQuickSMS($phone, $this->request->post['message']), true);
                        $resp[] = $respItem;
                        if ($this->request->post['startsend-log'] === "true" || $settings['startsend-log'] === 'on') {
                            $log = ['status' => $respItem['status'], 'sms_id' => $respItem['sms_id'], 'phone' => $phone, 'text' => $this->request->post['message']];
                            $this->load->model('extension/module/startsend');
                            $this->model_extension_module_startsend->setLogRecord($log);
                        }
                    }
                }
                $this->request->post['startsend-log'] = ($this->request->post['startsend-log'] == "true") ? "on" : 0;
            }
        }
        $this->response->setOutput(json_encode($resp));
    }

    /**
     *  Request for balance json
     */
    public function balance()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/startsend')) {
            $json['error'] = 403;
            $json['text'] = 'You do not have permission to perform this action!';
        } else {
            $json['error'] = 12;
            $api_token = '';
            $api_token = (isset($this->request->post['token'])) ? $this->request->post['token'] : $api_token;
            if ($api_token === '') {
                $this->load->model('setting/setting');
                $settings = $this->model_setting_setting->getSetting('startsend');
                $api_token = $settings['startsend-apitoken'];
            }
            if ($api_token != '') {
                $json = $this->getBalance();
            }
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput($json);
        }
    }

    /**
     *  Request for alphanames json
     */
    public function alphanames()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/startsend')) {
            $json['error'] = 403;
            $json['text'] = 'You do not have permission to perform this action!';
        } else {
            $json['error'] = 12;
            $api_token = '';
            $api_token = (isset($this->request->post['token'])) ? $this->request->post['token'] : $api_token;
            if ($api_token === '') {
                $this->load->model('setting/setting');
                $settings = $this->model_setting_setting->getSetting('startsend');
                $api_token = $settings['startsend-apitoken'];
            }
            if ($api_token != '') {
                $json = $this->getAlphanames();
            }
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput($json);
        }
    }

    /**
     * Mass send messages
     */
    public function massend()
    {
        $this->load->model('extension/module/startsend');
        $json = [];
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->user->hasPermission('modify', 'extension/module/startsend')) {
                $json['error'] = 403;
                $json['text'] = 'You do not have permission to perform this action!';
            }
            if (!$this->request->post['message']) {
                $json['error'] = 404;
                $json['text'] = 'The message field should not be empty!';
            }
            if (!$json) {
                $filter = array();
                if (($this->request->post['to'] > 10) and ($this->request->post['to'] < 100)) {
                    $group = $this->request->post['to'] % 10;
                    $type = intval($this->request->post['to'] / 10);
                    $filter['filter_group'] = $group;
                } elseif ($this->request->post['to'] > 100) {
                    $group = $this->request->post['to'] % 100;
                    $type = intval($this->request->post['to'] / 100);
                    $filter['filter_group'] = $group;
                }
                if ((isset($type)) and ($type == 3))
                    $filter['filter_newsletter'] = 1;
                if ($this->request->post['to'] == 1)
                    $filter['filter_newsletter'] = 1;

                if (($this->request->post['to'] != 4) and (!$this->request->post['arbitrary'])) {
                    $customers = $this->model_extension_module_startsend->getPhones($filter);
                    $query = array();
                    $i = 0;
                    $log_phone = '';
                    foreach ($customers as $customer) {
                        $phone = preg_replace("/[^0-9]/", '', $customer['telephone']);
                        if (preg_match('/(\+|)[0-9]{11,12}/', $phone)) {
                            $i++;
                            $original = array("{StoreName}", "{Name}", "{LastName}");
                            $replace = array($this->config->get('config_name'), $customer['firstname'], $customer['lastname']);
                            $message = str_replace($original, $replace, $this->request->post['message']);
                            $query[$phone] = $message;
                            $log_phone .= $phone . " ";
                            if ($i > 99) {
                                $json[] = $this->sendSMS($phone, $message);
                                $query = array();
                                $log_phone = '';
                                $i = 0;
                            }
                        }
                    }
                } else {
                    $phones = explode(',', $this->request->post['arbitrary']);
                    $query = array();
                    $log_phone = '';
                    foreach ($phones as $phone) {
                        $phone = trim($phone);
                        if (preg_match('/(\+|)[0-9]{11,12}/', $phone)) {
                            $original = array("{StoreName}", "{Name}", "{LastName}");
                            $replace = array($this->config->get('config_name'), '', '');
                            $message = str_replace($original, $replace, $this->request->post['message']);
                            $query[$phone] = $message;
                            $log_phone .= $phone;
                            $json[] = $this->sendSMS($phone, $message);
                        }
                    }
                }
            }
        }
        $this->response->setOutput(json_encode($json));
    }


    /**
     *  Request for alphanames json
     */
    public function alphaname_category()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/startsend')) {
            $json['error'] = 403;
            $json['text'] = 'You do not have permission to perform this action!';
        } else {
            $json['error'] = 12;
            $api_token = '';
            $api_token = (isset($this->request->post['token'])) ? $this->request->post['token'] : $api_token;
            if ($api_token === '') {
                $this->load->model('setting/setting');
                $settings = $this->model_setting_setting->getSetting('startsend');
                $api_token = $settings['startsend-apitoken'];
            }
            if ($api_token != '') {
                $json = $this->getAlphanameCategory();
            }
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput($json);
        }
    }

    /**
     *  Request for alphanames json
     */
    public function create_alphaname()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/startsend')) {
            $json['error'] = 403;
            $json['text'] = 'You do not have permission to perform this action!';
        } else {
            $json['error'] = 12;
            $api_token = '';
            $api_token = (isset($this->request->post['token'])) ? $this->request->post['token'] : $api_token;
            if ($api_token === '') {
                $this->load->model('setting/setting');
                $settings = $this->model_setting_setting->getSetting('startsend');
                $api_token = $settings['startsend-apitoken'];
            }
            if ($api_token != '') {
                $json = $this->createAlphaname($_POST);
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput($json);
        }
    }

    /**
     * Send SMS quick function
     * @param $phone
     * @param $message
     * @return string[]
     */
    private function sendQuickSMS($phone, $message)
    {
        $this->load->model('setting/setting');
        $data = $this->model_setting_setting->getSetting('startsend');
        $gate = $data['startsend-gate'];
        $url = $gate.self::$apiMethods['send_quick_sms']['url'];
        $method = self::$apiMethods['send_quick_sms']['method'];
        return $this->curl($url, $method, ['phone' => $phone, 'message' => $message]);
    }

    /**
     * Get balance
     * @param string $api_id
     * @return array
     */
    private function getAlphanames()
    {
        $this->load->model('setting/setting');
        $data = $this->model_setting_setting->getSetting('startsend');
        $gate = $data['startsend-gate'];
        $url = $gate.self::$apiMethods['get_alphanames']['url'];
        $method = self::$apiMethods['get_alphanames']['method'];
        return $this->curl($url, $method);
    }

    /**
     * Get balance
     * @param string $api_id
     * @return array
     */
    private function createAlphaname($post)
    {
        $this->load->model('setting/setting');
        $data = $this->model_setting_setting->getSetting('startsend');
        $gate = $data['startsend-gate'];
        $url = $gate.self::$apiMethods['create_alphaname']['url'];
        $method = self::$apiMethods['create_alphaname']['method'];
        return $this->curl($url, $method, $post);
    }


    /**
     * Send SMS quick function
     * @return string[]
     */
    private function getAlphanameCategory()
    {
        $this->load->model('setting/setting');
        $data = $this->model_setting_setting->getSetting('startsend');
        $gate = $data['startsend-gate'];
        $url = $gate.self::$apiMethods['get_alphaname_category']['url'];
        $method = self::$apiMethods['get_alphaname_category']['method'];
        return $this->curl($url, $method);
    }

    /**
     * Get balance
     * @param string $api_id
     * @return array
     */
    private function getBalance()
    {
        $this->load->model('setting/setting');
        $data = $this->model_setting_setting->getSetting('startsend');
        $gate = $data['startsend-gate'];
        $url = $gate.self::$apiMethods['get_balance']['url'];
        $method = self::$apiMethods['get_balance']['method'];
        return $this->curl($url, $method);
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
        if ($settings['startsend-log'] === 'on') {
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

?>