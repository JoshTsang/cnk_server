<?php
    require('macros.php');
    require('setting/defines.php');
    require('classes/printer.php');
    require('classes/CNK_DB.php');
    
    class Notification {
        private $db;
        private $json;
        
        function __construct($json) {
            $this->json = $json;
            $this->db = new CNK_DB();
        }
        
        public function get() {
            $ret = $this->db->getNotifications();
            
            $fp = fopen("../db/temporary/printer", "r+");
        
            if (flock($fp, LOCK_EX | LOCK_NB)) {  // acquire an exclusive lock
                $printer = new printer(PRINTER_CONF);
                $printer->printSavedOrder();
                flock($fp, LOCK_UN);    // release the lock
            } 
        
            fclose($fp);
            return $ret;
        }
        
        public function getType() {
            return $this->db->getNotificationTypes();
        }
        
        public function clean() {
            $tableId = $_GET['TID'];
            return $this->db->cleanNotification($tableId);
        }
        
        public function getError() {
            return $this->db->error();
        }
    }
    
    if (isset($_POST['json'])){
        $notification = new Notification($_POST['json']);
    } else {
        $notification = new Notification(null);
    }
   
    $do = "get";
    if (isset($_GET['do'])){
        $do = $_GET['do'];
    }
    
    $do = strtoupper($do);
    switch ($do) {
        case 'GET':
            $ret = $notification->get();
            break;
        case 'GETTYPE':
            $ret = $notification->getType();
            break;
        case 'CLEAN':
            $ret = $notification->clean();
            break;
        default:
            echo $do;
            $ret = FALSE;
            break;
    }
    
    if ($ret === FALSE) {
        echo $notification->getError();
    } else if($ret === TRUE){
        //TODO return succ or info
        echo "{succ:true}";
    } else {
        echo $ret;
    }
    
?>