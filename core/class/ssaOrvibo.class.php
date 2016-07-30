<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class ssaOrvibo extends eqLogic {
    
    
    public static function sendIr($eqLogic,$cmd) 
    {   
        
        $port = 10000;
        $ssaEqlogicObj = ssaOrvibo::byId($eqLogic);
        $orbivo_data=$ssaEqlogicObj->getConfiguration('commande');
        
        $log_etat="learnIp |".$orbivo_data['addrIp']."|";
        log::add('ssaOrvibo', 'debug', $ssaEqlogicObj->getHumanName() . '[' . __FUNCTION__ . ']' . ' : ' . $log_etat);
        
        $ssaCmd= $ssaEqlogicObj->getCmd(null, $cmd);
        $codeIr=$ssaCmd->getConfiguration('codeIr');
       
        
        
        try {
            $orv= ssaOrviboDriver::getInstance()->setOrbivoIp($orbivo_data['addrIp'])->setPort($port);
            $orv->createUdpSocket();
            $list=$orv->emitIr($orbivo_data['addrMac'],$codeIr);
            
            
            return ($list);
        
        }
        catch (Exception $e)
        {   
            $log_etat= $e->getMessage();
            log::add('ssaOrvibo','error',  $ssaEqlogicObj->getHumanName().'['.__FUNCTION__.']' .  ' : '. $log_etat); 
            throw new Exception("IP injoignable sur le port $port :".$e->getMessage()."\n");
        }
        
        
        
        

    }
    
    
    
    public static function learnIr($eqLogic) 
    {   $orbivo_ip = '192.168.1.14';
        $port = 10000;
        $ssaEqlogicObj = ssaOrvibo::byId($eqLogic);
        $orbivo_data=$ssaEqlogicObj->getConfiguration('commande');
        
        $log_etat="learnIp |".$orbivo_data['addrIp']."|";
        log::add('ssaOrvibo', 'debug', $ssaEqlogicObj->getHumanName() . '[' . __FUNCTION__ . ']' . ' : ' . $log_etat);
        try {
            $orv= ssaOrviboDriver::getInstance()->setOrbivoIp($orbivo_data['addrIp'])->setPort($port);
            $orv->createUdpSocket();
            $list=$orv->learningIr($orbivo_data['addrMac']);
            return ($list);
        
        }
        catch (Exception $e)
        {   
            $log_etat= $e->getMessage();
            log::add('ssaOrvibo','error',  $ssaEqlogicObj->getHumanName().'['.__FUNCTION__.']' .  ' : '. $log_etat); 
            throw new Exception("IP injoignable sur le port $port :".$e->getMessage()."\n");
        }
        
        
        
        

    }
    
   
    public static function findMacFromIp($eqLogic,$ip) 
    {   $orbivo_ip = '192.168.1.14';
        $port = 10000;
        $ssaEqlogicObj = ssaOrvibo::byId($eqLogic);
        
       
        $log_etat="search |$ip|";
        log::add('ssaOrvibo','debug',  $ssaEqlogicObj->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
        
        try {
            $orv= ssaOrviboDriver::getInstance()->setOrbivoIp($ip)->setPort($port);
            $orv->createUdpSocket();
            $list=$orv->discover();
            $log_etat="search |".json_encode($list)."|";
            log::add('ssaOrvibo','debug',  $ssaEqlogicObj->getHumanName().'['.__FUNCTION__.']' .  ' : '.$log_etat);
            return ($list['mac']);
        }
        catch (Exception $e)
        {   
            $log_etat= $e->getMessage();
            log::add('ssaOrvibo','error',  $ssaEqlogicObj->getHumanName().'['.__FUNCTION__.']' .  ' : '. $log_etat); 
            throw new Exception("IP injoignable sur le port $port :".$e->getMessage()."\n");
        }

    }
    
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDayly() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave()
    {         
        
    }

    public function postSave() {
        
    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*     * **********************Getteur Setteur*************************** */
}
      
class ssaOrviboCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
        
    }

    /*     * **********************Getteur Setteur*************************** */
}

?>
