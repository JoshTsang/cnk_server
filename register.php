<?php
   include 'classes/CNK_DB.php';
   
   if (isset($_GET['SN'])) {
       $db = new CNK_DB();
       $db->register($_GET['SN']);
       if ($db->validate()) {
           echo "register succ";
       } else {
           echo "register failed!.";
       }
   } else {
       echo "oOps!";
   }
?>