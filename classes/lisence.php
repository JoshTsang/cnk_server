<?php
   class Lisence {
       public function validatePad($uuid)
       {
           $this->removeNotUsedPad();
           $currentPadNum = $this->getCurrentPadNum();
           $permittedPadNum = $this->getPermittedPadNum();
           if ($currentPadNum > $permittedPadNum) {
                return $permittedPadNum;
           }
           
           if(file_exists(DEV_DIR.$uuid)) {
                return 0;
            } else {
                $this->createPadInfo($uuid);
                return 0;
            }
       }
   
        private function removeNotUsedPad() 
        {
           $time = time();
           $files = $this->getDirFiles();
           if (!$files) {
               return;
           }
           $count = count($files);
           for ($i=0; $i < $count; $i++) { 
                $devTime = $this->getLastRefreshTime($files[$i]);
               if ($devTime) {
                   if (($time - $devTime) < 60) {
                       continue;
                   }
               }
               
               unlink(DEV_DIR.$files[$i]);
           }
        }
        
        private function getCurrentPadNum()
        {
            $files = $this->getDirFiles();
            if ($files) {
                return count($files);
            } else {
                return 0;
            }
        }
        
        private function getPermittedPadNum() 
        {
            @$num = file_get_contents(LISENCE_CONF);
            if ($num) {
                return $num;
            } else {
                return 1;
            }
        }
        
        private function createPadInfo($uuid)
        {
            file_put_contents(DEV_DIR.$uuid, time());
        }
        
        private function getLastRefreshTime($uuid) {
            return file_get_contents(DEV_DIR.$uuid);
        }
        
        private function getDirFiles()   
        {   
            if ($handle = opendir(DEV_DIR)){
                while (false !== ($file = readdir($handle))) {
                    if ($file == ".." || $file == ".") {
                        continue;
                    }   
                    $files[]=$file;   
                 }   
            }   
            
            closedir($handle);
            return $files;
        }   
        
        public function updatePadInfo($uuid) {
            file_put_contents(DEV_DIR.$uuid, time());
        }
   }
?>