<?php
include_once 'class/dbh.inc.php';
include_once 'class/variables.inc.php';
include_once 'class/abstract_workpcsnew.inc.php';
include_once 'class/reverse-dimension.inc.php';
include_once 'class/phhdate.inc.php';


$received_data = json_decode(file_get_contents("php://input"));

$action = $received_data->action;

switch($action){
    case 'getPeriod':
        $objDate = new DateNow();
        $currentPeriod_int = $objDate->intPeriod();
        $currentPeriod_str = $objDate->strPeriod();

        $EndYYYYmm = 2001;
        $objPeriod = new generatePeriod($currentPeriod_int, $EndYYYYmm);
        $setofPeriod = $objPeriod->generatePeriod3();

        echo json_encode($setofPeriod);
        break;
    case 'getStaffList':
        $period = $received_data->period;
        $prowtab = "production_weight_$period";
        $qr = "SELECT DISTINCT staffname FROM $prowtab";
        $objSQL = new SQL($qr);
        $results = $objSQL->getResultRowArray();
        echo json_encode($results);
        break;
    case 'getMachineListbyStaff':
        $period = $received_data->period;
        $prowtab = "production_weight_$period";
        $staff = $received_data->staff;
        $qr = "SELECT DISTINCT machineModel FROM $prowtab WHERE staffname = '$staff'";
        $objSQL = new SQL($qr);
        $results = $objSQL->getResultRowArray();
        echo json_encode($results);
        break;
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

