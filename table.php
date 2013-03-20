<?php
    require('macros.php');
    require('config.php');
    require('setting/defines.php');
	require('classes/printer.php');
    require('classes/lisence.php');
    require('classes/CNK_DB.php');
    
    /**
     * 
     */
    class Table {
        private $json;
        private $db;
        
        function __construct($json) {
            $this->json = $json;
            $this->db = new CNK_DB();
        }
        
        public function getTables() {
            if (isset($_GET['UUID'])) {
                $lisence = new Lisence();
                $lisence->updatePadInfo($_GET['UUID']);
            }
            return $this->db->getTableStatus();
        }
        
        public function getTableStatus($tableId) {
            return $this->db->getTableStatus($tableId);
        }
        
        public function updateTable() {
            if (!isset($_GET['TID']) || !isset($_GET['TST'])) {
                die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
            }
            $TableId = $_GET['TID'];
            $TableStatus = $_GET['TST'];
            return $this->db->updateTableStatus($TableId, $TableStatus);
        }
        
        public function combineTables() {
            if (!isset($_GET['srcTID']) || !isset($_GET['destTID']) || !isset($_POST['json'])) {
                die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
            }
            
            $obj = json_decode($this->json); 
            $dishCount = count($obj->order);
            $tableId = $obj->tableId;
            $timestamp = $obj->timestamp;
            $persons = $obj->persons;
            @$datetime = split(" ", $timestamp);
            $printer = new printer(PRINTER_CONF);
            
            $ret = $this->db->changeTable($_GET['srcTID'], $_GET['destTID'], $persons);
            
            if ($dishCount > 0) {
                $printer->printCombine($this->json);
            }
            
            return $ret;
        }
        
        public function cleanTable() {
            if(!isset($_POST['json'])) {
                die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
            }
            $obj = json_decode($this->json);
            
            $order = $obj->order;
            $timestamp = $obj->timestamp;
            $tableIdCount = count($obj->order);
            if ($tableIdCount <= 0) {
                die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
            }
            for($i = 0;$i < $tableIdCount;$i++) {
                $tableId = $order[$i] -> TID;
                return $this->db->cleanTable($tableId, $timestamp);
            }
            return false;
        }
        
        public function changeTable() {
            if (!isset($_GET['srcTID']) || !isset($_GET['destTID']) || !isset($_POST['json'])) {
                die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
            }
            
            $obj = json_decode($this->json); 
            $dishCount = count($obj->order);
            $tableId = $obj->tableId;
            $timestamp = $obj->timestamp;
            @$datetime = split(" ", $timestamp);
            $persons = $obj->persons;
            $printer = new printer(PRINTER_CONF);
            
            $ret = $this->db->changeTable($_GET['srcTID'], $_GET['destTID'], $persons);
            
            if ($dishCount > 0) {
                $printer->printChangeTable($this->json);
            }
            
            return $ret;
        }
        
        public function copyTable() {
            if (!isset($_GET['destTID']) || !isset($_POST['json'])) {
                die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
            }
            
            $printer = new printer(PRINTER_CONF);
            
            $obj = json_decode($this->json); 
            $dishCount = count($obj->order);
            
            if ($dishCount <= 0) {
                die("[MORE_PARAM_NEEDED:".NO_DISH."]");
            }
            
            $printer->printOrder($this->json);
            $this->db->submitOrder($obj);
            $this->db->cleanPhoneOrder($_GET['destTID']);
            $ret = $this->db->updateTableStatus($_GET['destTID'], 1);
        }
        
        public function getError() {
            return $this->db->error();
        }
    }
    
    $do = "get";
    if (isset($_GET['do'])){
        $do = $_GET['do'];
    }
    
    if (isset($_POST['json'])){
        $table = new Table($_POST['json']);
    } else {
        $table = new Table(null);
    }
    
    switch ($do) {
        case 'get':
            if (isset($_GET['TSI'])) {
                $ret = $table->getTableStatus($_GET['TSI']);
            } else {
                $ret = $table->getTables();
            }
            break;
            
        case 'update':
            $ret = $table->updateTable();
            break;
            
        case 'combine':
            $ret = $table->combineTables();
            break;
            
        case 'clean':
            $ret = $table->cleanTable();    
            break;
            
        case 'change':
            $ret = $table->changeTable();
            break;
            
        case 'copy':
            $ret = $table->copyTable();
            break;
            
        default:
            break;
    }
    
    if ($ret === FALSE) {
        echo $table->getError();
    } else if($ret === TRUE){
        //TODO return succ or info
        echo "{succ:true}";
    } else {
        echo $ret;
    }
?>