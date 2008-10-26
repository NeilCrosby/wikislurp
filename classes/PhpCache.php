<?php
   /***************************************************************/
   /* PhpCache - a class for caching arbitrary data
   
      Software License Agreement (BSD License)
   
      Copyright (C) 2005-2007, Edward Eliot.
      All rights reserved.
      
      Redistribution and use in source and binary forms, with or without
      modification, are permitted provided that the following conditions are met:

         * Redistributions of source code must retain the above copyright
           notice, this list of conditions and the following disclaimer.
         * Redistributions in binary form must reproduce the above copyright
           notice, this list of conditions and the following disclaimer in the
           documentation and/or other materials provided with the distribution.
         * Neither the name of Edward Eliot nor the names of its contributors 
           may be used to endorse or promote products derived from this software 
           without specific prior written permission of Edward Eliot.

      THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS "AS IS" AND ANY
      EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
      WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
      DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
      DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
      (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
      LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
      ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
      (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
      SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

      Updated by Neil Crosby for thetenwordreview.com
      Now allows a prefix to be given to the cached file's name.
      Also keeps cached data in memory for the script's duration so that
      re-requesting it on the same page does not incur a reload of the file.
   
      Last Updated:  23rd March 2008
   /***************************************************************/
   
   define('CACHE_PATH', $_SERVER['DOCUMENT_ROOT'].'/cache/');
   
   class PhpCache {
      var $sFile;
      var $sFileLock;
      var $iCacheTime;
      
      var $sShortKey;
      static $aCache = array();
      
      /**
       * @param $sPrefix    Allows an extra string to be prepended to the 
       *                    MD5ed filename to allow for easy identification
       *                    between cached types.
       **/
      function PhpCache($sKey, $iCacheTime, $sPrefix='') {
         $this->sShortKey = $sPrefix.md5($sKey);
         $this->sFile = CACHE_PATH.$this->sShortKey.".txt";
         $this->sFileLock = "$this->sFile.lock";
         $iCacheTime >= 0 ? $this->iCacheTime = $iCacheTime : $this->iCacheTime = 0;
      }
      
      function Check() {
         if ( array_key_exists( $this->sShortKey, self::$aCache ) ) {
           return true;
         }
         if (file_exists($this->sFileLock)) return true;
         return (file_exists($this->sFile) && ($this->iCacheTime == -1 || time() - filemtime($this->sFile) <= $this->iCacheTime));
      }
      
      function Exists() {
         return (array_key_exists( $this->sShortKey, self::$aCache )) || (file_exists($this->sFile) || file_exists($this->sFileLock));
      }
      
      function Set($vContents) {
         if (!file_exists($this->sFileLock)) {
            if (file_exists($this->sFile)) {
               copy($this->sFile, $this->sFileLock);
            }
            $oFile = fopen($this->sFile, 'w');
            fwrite($oFile, serialize($vContents));
            fclose($oFile);
            if (file_exists($this->sFileLock)) {
               unlink($this->sFileLock);
            }
            self::$aCache[$this->sShortKey] = $vContents;
            return true;
         }     
         return false;
      }
      
      function Get() {
         if (array_key_exists( $this->sShortKey, self::$aCache )) {
            return self::$aCache[$this->sShortKey];
         } else if (file_exists($this->sFileLock)) {
            $temp = unserialize(file_get_contents($this->sFileLock));
            self::$aCache[$this->sShortKey] = $temp;
            return $temp;
         } else {
            $temp = unserialize(file_get_contents($this->sFile));
            self::$aCache[$this->sShortKey] = $temp;
            return $temp;
         }
      }
      
      function ReValidate() {
         touch($this->sFile);
      }
   }
?>