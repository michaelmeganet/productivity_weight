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
<h2>PRODUCTION KPI INDEX </h2>

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
        echo $columnHeader." = ".$$columnHeader."<br>";

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
// $period = trim($_POST['period']);
$period = '2009';
$kpidata = 'production_weight_' . $period;


//echo "tbldata = $tbldata;;  tbloutput = $tbloutput";
$sqlName  = "SELECT DISTINCT staffname FROM $kpidata WHERE poid IS NOT NULL AND jlfor = 'CJ' AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('2020-09-00','%Y %m') AND staffname IS NOT NULL";
#echo "query = $sqlName<br>\n";
$objSQLname = new SQL($sqlName );
$namelist = $objSQLname->getResultRowArray();
#print_r($namelist);
#echo"<br>";
$count = 0;
$index_gain = 0;
foreach($namelist as $array) { 

    foreach($array as $key=>$value){
        $count = 0;
        ${$key} = $value; 
        //echo "$key : $value\n"."<br>";
        $sqlCount = "SELECT count(*) FROM $kpidata WHERE poid IS NOT NULL AND jlfor = 'CJ' AND staffname = '$value' AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('2020-09-00','%Y %m') ";
        #echo "queryCount = $sqlCount<br>\n";
        $objcount = new SQL($sqlCount);
        $recordCount = $objcount->getRowCount();
        echo "record(s) counted of $value in $kpidata is/are ".$recordCount ."<br>";
        $qr = "SELECT * FROM $kpidata WHERE poid IS NOT NULL AND jlfor = 'CJ' AND staffname = '$value' AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('2020-09-00','%Y %m') ORDER BY sid, date_start ASC";

        //echo "\$qr = $qr <br>";
        $objSQL = new SQL($qr);
        $results = $objSQL->getResultRowArray();
        $arr_mainLog = array();

        // $table = "production_weight_".$period; 
        ?>
            <div style="max-height: 350px;overflow-y: scroll">
        <table style="width: 100%;">
        <tr>
            <th>sid</th><th>qid</th><th>Job Code</th><th>Grade</th><th>Quantity</th><th>Unit weight</th><th>Total Weight</th><th>Dimensions</th><th>jlfor</th><th>Index Gain in KG</th>
            <th>jobno</th><th>date of completion</th><th>cid</th><th>Cutting type</th><th>Staff Name</th><th>Machine ID</th><th>Machine Name</th>
            <th>Start Date</th><th>Model</th>
        </tr> 

        <?php


        $insertArray = [];
        try{
            foreach ($results as $datarow) {
                $count++;

                #echo "<pre>Current data : <br>";
                #print_r($datarow);
                #echo "<br></pre><br>";
                $sid = $datarow['sid'];
                $qid = $datarow['qid'];
                $quono=$datarow['quono'];
                $jobcode = $datarow['jobcode'];
                $grade=$datarow['grade'];
                $quantity = $datarow['quantity'];
                $unit_weight = $datarow['unit_weight'];
                $total_weight = $datarow['total_weight'];
                $dimension = $datarow['dimension'];
                $jlfor = $datarow['jlfor'];
                //$runningno = $datarow['runningnno'];
                $jobno = $datarow['jobno'];
                $dateofcompletion = $datarow['dateofcompletion'];
                $cid = $datarow['cid'];
                $cuttingtype = $datarow['cuttingtype'];
                $staffname = $datarow['staffname'];
                $machineid = $datarow['machineid'];
                $qr = "SELECT * FROM machine WHERE machineid = '$machineid'";
                $objSQLmc = new SQL($qr);
                $machineName = $objSQLmc->getResultOneRowArray()['name'];
                //echo "sid = $sid<br>";
                $startdate = $datarow['date_start'];
                $model = $datarow['model'];
                $unit_gain_kg = $unit_weight * $quantity;
                $machine_capacity_per_shift = $datarow['machine_capacity_per_shift'];
                if(isset($unit_gain_kg)){
                    $unit_gain_kg = floatval($unit_gain_kg) * 9.8;
                    #$index_gain_in_kg = floatval($index_gain_in_kg);
                    $index_gain = $index_gain + $unit_gain_kg;
                }
        
                
            echo 
            "<tr>
                <td>$sid</td><td>$qid</td><td>$jobcode</td><td>$grade</td><td>$quantity</td><td>$unit_weight</td><td>$total_weight</td><td>$dimension</td><td>$jlfor</td><td>$unit_gain_kg</td>
                <td>$jobno</td><td>$dateofcompletion</td><td>$cid</td><td>$cuttingtype</td><td>$staffname</td><td>$machineid</td><td>$machineName</td>
                <td>$startdate</td><td>$model</td>
            
            </tr>";
            
            // echo "qid = $qid | quono = $quono | company = $company | cid = $cid | quantity = $quantity | grade = $grade | "
            //      . " dimension = $dimension | process = $process | cuttingtype = $cuttingtype  | cncmach = $cncmach | " 
            //      ." noposition = $noposition | runningno = $runningno | jobno = $jobno | date_issue = $date_issue | "
            //      . " completion_date = $completion_date | dateofcompletion = $dateofcompletion | jlfor = $jlfor | status = $status | "
            //      . " staffname = $staffname |  machineModel  = $machineModel | model = $model | date_start = $date_start | packing = $packing  | operation = $operation | unit_weight = $weight | "
            //      . " total_weight = $total_weight | index_per_shift = $index_per_shift <br>";
            // echo"################################################################################<br>";
            //echo  "index gain = $index_gain, index_gain_in_kg = $index_gain_in_kg <br>";


            }

        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        finally{

        }
        echo "</table></div>";
        echo "\$count = $count , \$recordCount = $recordCount <br>";
        if($count == $recordCount){
            if($machine_capacity_per_shift > 0.00){
            #$KPI_weekday  = number_format($index_gain/$index_per_shift * 9.8, 2);
            #$KPI_holiday = number_format($index_gain/$index_per_shift * 7.35, 2);
            $KPI_weekday  = number_format($index_gain/$machine_capacity_per_shift, 2);
            $KPI_holiday = number_format($index_gain/$machine_capacity_per_shift, 2);
            }else{
                $KPI_weekday = "no value";
                $KPI_holiday = "no value";
            }

            echo "The total index gain in KG for $value is $index_gain <br>";
            echo "The index per shift of $model, $machineid  -  $machineName is $machine_capacity_per_shift <br> ";
            echo "The weekday KPI is $KPI_weekday <br>";
            $index_gain = 0;
        }

    

    }
}// assign all key-valuepair
     
?>




</html>