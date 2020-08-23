<!DOCTYPE html>
<html>
<head>
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
</head>
<body>
<h2>PRODUCTION WEIGHT </h2>

<?php
function callSqlInsert($insertArray,$table) {


    $cnt = 0;
    foreach ($insertArray as $key => $value) {
        $cnt++;
        ${$key} = $value;
        echo "$cnt)  $key : $value\n" . "<br>";
//                    debug_to_console("$key => $value");
    }
    // $arrayKeys = array_keys($insertArray);    //--> fetches the keys of array
    ##$lastArrayKey = array_pop($insertArray); //--> fetches the last key of the compiled keys of array
    end($insertArray); // move the internal pointer to the end of the array
    $lastArrayKey = key($insertArray);  // fetches the key of the element pointed to by the internal pointer
    $sqlInsert = "INSERT INTO $table SET ";
    #begin loop
    foreach ($insertArray as $key => $value) {

        ${$key} = trim($value);
        $columnHeader = $key; // creates new variable based on $key values
        //echo $columnHeader." = ".$$columnHeader."<br>";

        /* $dbg->review($columnHeader." = ".$$columnHeader."<br>"); */ //this is for debugging, not yet implemented

        $sqlInsert .= $columnHeader . "=:{$columnHeader}";     //--> adds the key as parameter
        if ($columnHeader != $lastArrayKey) {
            $sqlInsert .= ", ";      //--> if not final key, writes comma to separate between indexes
        } else {
            #do nothing         //--> if yes, do nothing
        }
    }
    # end loop

    echo "\$sqlInsert = $sqlInsert <br>";
    $objInsert = new SQLBINDPARAM($sqlInsert, $insertArray);
    $result = $objInsert->InsertData2();
    echo "$result <br>";
    return $result;
}
include_once 'class/dbh.inc.php';
include_once 'class/variables.inc.php';
include_once 'class/abstract_workpcsnew.inc.php';
include_once 'class/reverse-dimension.inc.php';
include_once 'class/phhdate.inc.php';
$period = trim($_POST['period']);
// $period = '2007';
$tbldata = 'production_scheduling_' . $period;

$tbloutput = 'production_output_' . $period;
//echo "tbldata = $tbldata;;  tbloutput = $tbloutput";
$qr = "SELECT * FROM $tbldata ORDER BY sid";
$objSQL = new SQL($qr);
$results = $objSQL->getResultRowArray();
$arr_mainLog = array();
$count = 0;
$table = "production_weight_".$period; 
?>

<!-- <table style="width: 100%;">
<tr>
    <th>sid</th><th>quono</th><th>Material Code</th><th>Quantity</th><th>Unit weight</th><th>Total Weight</th><th>Dimensions</th><th>BRANCH</th><th>runningno</th>
    <th>jobno</th><th>date of completion</th><th>cid</th><th>Cutting type</th><th>Staff Name</th><th>Machine Model</th>
    <th>Start Date</th>
</tr> -->

<?php

$insertArray = [];
try{
    foreach ($results as $datarow) {
        $count++;
        #echo "<pre>Current data : <br>";
        #print_r($datarow);
        #echo "<br></pre><br>";
        $jlfor=$datarow['jlfor'];
        $dateofcompletion=$datarow['dateofcompletion'];
        $runningno=$datarow['runningno'];
        $jobno = $datarow['jobno'];
        $grade = $datarow['grade'];
        $quono = $datarow['quono'];
        $sid = $datarow['sid'];
        $process = $datarow['process'];
        $cncmach = $datarow['cncmach'];
        $noposition = $datarow['noposition'];
        $date_issue = $datarow['date_issue'];
        $completion_date = $datarow['completion_date'];
        $status = $datarow['status'];
        $packings = $datarow['packings'];
        $operation = $datarow['operation'];
        //echo "sid = $sid<br>";
        $qid = $datarow['qid'];
        $quantity = $datarow['quantity'];
        $fdt = $datarow['fdt'];
        $fdl = $datarow['fdl'];
        $fdw = $datarow['fdw'];
        $dimension = "$fdt x $fdw x $fdl";
        $cid = $datarow['cid'];
        $com = strtolower(trim($datarow['company']));
        $materialcode = $datarow['grade'];
        $cuttingtype = $datarow['cuttingtype'];
        if(isset($weight)){
            if($weight > 0){
                $totalweight = $weight * $quantity;
            }else{
                $totalweight = 0;
            }
        } else {
            $weight = 0;
            $totalweight = 0;
        }
        if ($fdt == 0 && $materialcode != 'hk2p') { // If no finishing, then finishing is same as raw
            $fdt = $datarow['mdt'];
        }
        if ($fdw == 0 && $materialcode != 'hk2p') { // If no finishing, then finishing is same as raw
            $fdw = $datarow['mdw'];
        }
        if ($fdl == 0 && $materialcode != 'hk2p') { // If no finishing, then finishing is same as raw
            $fdl = $datarow['mdl'];
        }
        $dimension_array_legacy = array('mdt' => $fdt, 'mdw' => $fdw, 'mdl' => $fdl, 'quantity' => $quantity);
        if ($materialcode != 'hk2p') {
            $obj = new MATERIAL_SPECIAL_PRICE_CID($cid, $com, $materialcode, $dimension_array_legacy);
            $weight = $obj->getWeight();
        } else {
            $weight = (float) 0.00;
        }
    // echo "<b>quono - $quono </b> , grade = $grade ,   $dimension <br>";
    // echo "<b> Weight = $weight; </b><br>";
        //Get Start_By Staff ID
        $qr2 = "SELECT * FROM $tbloutput WHERE sid = $sid AND NOT jobtype = 'jobtake' ORDER BY poid";
        $objSQL2 = new SQL($qr2);
        $results2 = $objSQL2->getResultOneRowArray();
        if (!empty($results2)) {
            $staffid = $results2['start_by'];
            $startdate = $results2['date_start'];
            $enddate = $results2['date_end'];
            $mcid = $results2['machine_id'];
            $day = date_format(date_create($startdate), 'l');
            $date = date_format(date_create($startdate), 'd-m-Y');
            $netdatetime = strtotime($enddate) - strtotime($startdate);
            $workhourval = $netdatetime / 3600;
            $workhouronly = floor($workhourval);
            $workhourremainder = $workhourval - $workhouronly;
            $workminuteval = $workhourremainder / 60;
            $workminuteonly = floor($workminuteval);
            $workhour = $workhouronly . " Hours, " . $workminuteonly . " Minutes";
        //  echo "startdatestr = " . strtotime($startdate) . "<br>";
        //   echo "enddatestr = " . strtotime($enddate) . "<br>";
        //   echo "netdatetime = " . $netdatetime . "<br>";
        //   echo "workhour = " . $workhour . "<br>";
        } else {
            $staffid = null;
            $startdate = null;
            $enddate = null;
            $day = null;
            $date = null;
            $netdatetime = null;
            $workhour = null;
            $mcid = null;
        }
    // echo "startdate = $startdate<br>";
    //  echo "day = $day<br>";
    //  echo "date = $date<br>";

        if (isset($staffid)) {
            //Get Staff Detail
            $qr3 = "SELECT * FROM admin_staff WHERE staffid = '$staffid'";
            $objSQL3 = new SQL($qr3);
            $results3 = $objSQL3->getResultOneRowArray();
            $staffname = $results3['name'];
        } else {
            $staffname = null;
        }
    //  echo "staffname = $staffname<br>";

        if (isset($mcid)) {
            //Get Machine Data
            $qr4 = "SELECT * FROM machine WHERE mcid = $mcid";
            $objSQL4 = new SQL($qr4);
            $results4 = $objSQL4->getResultOneRowArray();
            $machineid = $results4['machineid'];
            $machineModel = $results4['name'];
        } else {
            $machineid = null;
            $machineModel = null;
        }
    // echo "machineModel - $machineModel<br>";


        //idv  Day   Date   Staff id   Staff name   MachineModel   Cutting Type   start_datetime   end_datetime   net_dattime   work_hour   qty   Weight(kg)
        // $arr_mainLog[] = array(
        //     'id' => $count,
        //     'sid' => $sid,
        //     'Day' => $day,
        //     'Date' => $date,
        //     'Staff_Id' => $staffid,
        //     'Staff_Name' => $staffname,
        //     'MachineModel' => $machineModel,
        //     'Cutting_Type' => $cuttingtype,
        //     'start_datetime' => $startdate,
        //     'end_datetime' => $enddate,
        //     'net_datetime' => $netdatetime,
        //     'work_hour' => $workhour,
        //     'qty' => $quantity,
        //     'Weight (Kg)' => round($weight,2)
        // );
    // echo "-=-=-=-=-=-=-==-=-=-=-=-=-=-=-=-=-=-=-=-=--==-=-=-=-=-=-<br>";
    if($weight * 1 > 0){
            $weight = round($weight,2);
    }else{
        $weight = 0.00;
    }
    // echo 
    // "<tr>
    //         <td>$sid</td><td>$quono</td><td>$materialcode</td><td>$quantity</td><td>$weight</td><td>$totalweight</td><td>$dimension</td><td>$jlfor</td><td>$runningno</td>
    //         <td>$jobno</td><td>$dateofcompletion</td><td>$cid</td><td>$cuttingtype</td><td>$staffname</td><td>$machineModel</td>
    //         <td>$startdate</td>
    
    // </tr>";
    echo "qid = $qid | quono = $quono | company = $company | cid = $cid | quantity = $quantity | grade = $grade | "
         . " dimension = $dimension | process = $process | cuttingtype = $cuttingtype  | cncmach = $cncmach | " 
         ." noposition = $noposition | runningno = $runningno | jobno = $jobno | date_issue = $date_issue | "
         . " completion_date = $completion_date | dateofcompletion = $dateofcompletion | jlfor = $jlfor | status = $status | "
         . " date_start = $startdate | packing = $packing  | operation = $operation | unit_weight = $weight | "
         . " total_weight = $totalweight |<br>";
    echo"################################################################################<br>";
    $insertArray["wid"] = '';
    $insertArray["qid"] = $qid;
    $insertArray["quono"] = "$quono";
    $insertArray["company"] = "$company";
    $insertArray["cid"] = $cid;
    $insertArray["quantity"] = $quantity;
    $insertArray["grade"] = "$grade";
    $insertArray["dimension"] = "$dimension";
    $insertArray["process"] = "$process";
    $insertArray["cuttingtype"] = "$cuttingtype";
    $insertArray["cncmach"] = $cncmach;
    $insertArray["noposition"] = $noposition;
    $insertArray["runningno"] = "$runningno";
    $insertArray["jobno"] = "$jobno";
    $insertArray["date_issue"] = "$date_issue";
    $insertArray["completion_date"] = "$completion_date";
    $insertArray["dateofcompletion"] = "$dateofcompletion";
    $insertArray["jlfor"] = "$jlfor";
    $insertArray["status"] = "$status";
    $insertArray["date_start"] = "$startdate";
    $insertArray["packing"] = "$packing";
    $insertArray["operation"] = $operation;
    $insertArray["unit_weight"] = $weight;
    $insertArray["total_weight"] = $totalweight;
    print_r($insertArray);
    echo "<br>";
    echo "insert an array into $table <br>";
    $insertResult = callSqlInsert($insertArray,$table) ;
    echo "The insert result is ".$insertResult;
    
    }

}
catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
finally{

}

?>

<!-- </table> -->



</html>