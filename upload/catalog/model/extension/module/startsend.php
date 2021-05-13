<?php
class ModelExtensionModuleStartsend extends Model {


    private $gates = [
        'https://app.startsend.ru',
        'https://app.sms.by',
    ];

    /**
     * Return gates lists
     * @return string[]
     */
    public function getGates() {
        return $this->gates;
    }

    /**
     * Get order count of notifications
     * @param $order_id
     * @return mixed
     */
	public function getHistoryCount($order_id) {
		$data = array(); 
		$query = $this->db->query("SELECT COUNT(`order_id`) AS `count` FROM `" . DB_PREFIX ."order_history` WHERE `order_id` = ". $order_id.";");
		return $query->row['count'];
	}

    /**
     * Change message status in log table
     * @param int $send_id
     * @param $status
     * @return bool
     */
	public function setNoticeStatus($send_id = 0, $status) {
		$query = $this->db->query("UPDATE `" . DB_PREFIX . "startsend` 
			SET `status` = '".(int)$status."' 
			WHERE `" . DB_PREFIX . "startsend`.`sms_id` = '" . $send_id . "';");
		return true;
	}

    /**
     * Add record to log table
     * @param array $smslog
     * @return bool
     */
	public function setLogRecord($smslog = array()) {
		$sql = "INSERT INTO  `".DB_PREFIX."startsend` (`id`,`date`,`status`,`phone`,`sms_id`, `text`) 
		VALUES (NULL, NOW(), '".$this->db->escape($smslog['status'])."', '".$this->db->escape($smslog['phone'])."', '".$this->db->escape($smslog['sms_id'])."', '".$this->db->escape($smslog['text'])."')";
		$query = $this->db->query($sql);
		return true;
	}

    /**
     * Get order notification and comment by order ID
     * @param $order_id
     * @return array
     */
	public function getHistory($order_id) {
		$query = $this->db->query("SELECT `comment`, `notify` FROM `" . DB_PREFIX ."order_history` WHERE `order_id` = ". $order_id." ORDER BY `order_history_id` DESC LIMIT 1;");
		$data = array('comment' => $query->row['comment'], 'notify' => $query->row['notify']); 
		return $data;
	}

    /**
     * Need to send SMS with specific status
     * @param $order_id
     * @param $order_status_id
     * @return array
     */
	public function needToSendSMS($order_id, $order_status_id) {
	    $query = $this->db->query("SELECT `order_history_id`, `order_status_id`, `notify` FROM `" . DB_PREFIX ."order_history` WHERE `order_id` = ". $order_id." AND `order_status_id` = ". $order_status_id ."  AND `notify` = 0 ORDER BY `order_history_id` DESC LIMIT 1;");
        return array('order_history_id' => $query->row['order_history_id'] ?? '', 'order_status_id' => $order_status_id);
    }


    /**
     * Need to send SMS with specific status
     * @param $order_id
     * @param $order_status_id
     * @return array
     */
    public function changeSMSNotify($order_history_id) {
        $query = $this->db->query("UPDATE `" . DB_PREFIX ."order_history` SET `notify` = 1 WHERE `order_history_id` = ". $order_history_id." ;");
        return;
    }
}
?>