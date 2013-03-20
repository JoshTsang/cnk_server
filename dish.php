<?php
    require('macros.php');
    require('setting/defines.php');
    require('classes/printer.php');
    require('classes/CNK_DB.php');
    
    /**
     * 
     */
    class Dish {
        private $db;
        private $json;
        
        function __construct($json) {
            $this->json = $json;
            $this->db = new CNK_DB();
        }
        
        public function delete() {
            
        }
        
        public function scale() {
            
        }
        
        public function mark($status) {
            
        }
    }
    
?>