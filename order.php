<?php
    require('macros.php');
    require('setting/defines.php');
    require('classes/printer.php');
    require('classes/CNK_DB.php');
    
    class Order {
        private $db;
        private $json;
        
        function __construct($json) {
            $this->json = $json;
            $this->db = new CNK_DB();
        }
        
        public function get()
        {
            if (!isset($_GET['TID'])) {
                die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
            }
            
            $tableId = $_GET['TID'];
            
            return $this->db->getOrderedDishes($tableId);
            
        }
        
        public function submit()
        {
            if (!isset($_POST['json'])) {
                die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED.",json]");
            }
            $obj = json_decode($this->json); 
            $dishCount = count($obj->order);
            
            if ($dishCount <= 0) {
                die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED.",dishes]");
            }
            
            $printer = new printer(PRINTER_CONF);
            $orderId = $this->db->submitOrder($obj, $_GET['MD5']);
            if ($orderId > 0) {
                $printer->savePrintOrder($this->json, $orderId, isset($_GET['action']));
            } 
            
            $receipt = json_decode($this->json);
            $history = array('type' => isset($_GET['action'])?HISTORY_ADD:HISTORY_ORDER,
                             'table' => $receipt->tableName,
                             'timestamp' => $receipt->timestamp,
                             'receipt' => $this->json,
                             'extra' => $orderId);
            $printer->saveHistory((object)$history);
            return true;
        }

        public function delete() {
            
        }
        
        public function getError() {
            return $this->db->error();
        }
    }

    if (isset($_POST['json'])){
        $order = new Order($_POST['json']);
    } else {
        $order = new Order(null);
    }
   
    $do = "get";
    if (isset($_GET['do'])){
        $do = $_GET['do'];
    }
    
    switch ($do) {
        case 'get':
            $ret = $order->get();
            break;
        case 'submit':
            $ret = $order->submit();
            break;
        case 'delete':
            $ret = $order->delete();
            break;
        default:
            break;
    }
    
    if ($ret === FALSE) {
        echo $order->getError();
    } else if($ret === TRUE){
        //TODO return succ or info
        echo "{succ:true}";
    } else {
        echo $ret;
    }
?>