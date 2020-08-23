<?php


include_once "dbh.inc.php";
include_once "variables.inc.php";
include_once "density.inc.php";
include_once "materialprice.inc.php";

function checkIsSet($parameter) {
    if (isset($parameter)) { // using operator (!==) to check the value and datatype
        return TRUE;
    } else {
        return FALSE;
    }
}

abstract class WORKPCS2 {

    protected $dimensions;
    protected $density;
    protected $weight;
    protected $volume;
    protected $materialType; // such as 'ts' for tools steel type
    protected $materialcode; // material code or grade special key for linking to orderlist

    public function __construct() {

        $density = new DENSITY();
        $this->dimensions = [];
        //$this->density = 0.00;
        $this->weight = 0.00;
        $this->volume = 0.00;
        $this->materialType = "";
        $this->materialcode = "";
    }

    public function setDimension($array) {

        $this->dimensions = $array;
    }

    public function getDimension() {

        return $this->dimensions;
    }

    public function setDensity($input) {

        $this->density = $input;
    }

    public function getDensity() {

        return $this->density;
    }

    public function setWeight($input) {

        $this->weight = $input;
    }

    public function getWeight() {

        return $this->weight;
    }

    public function setVolume($input) {

        $this->volume = $input;
    }

    public function getVolume() {

        return $this->volume;
    }

    public function setMaterialType($input) {

        $this->materialType = $input;
    }

    public function getMaterialType() {

        return $this->materialType;
    }

    public function setMaterialCode($input) {

        $this->materialcode = $input;
    }

    public function getMaterialCode() {

        return $this->materialcode;
    }

//    public function calDensity($materialType) {
//
//
//        $sql = "SELECT plate from material_density WHERE materialtype = '$materialType' ";
//        $obj = new SQL($sql);
//        $result = $obj->getResultOneRowArray();
//        $density = $result['plate'];
//        $this->density = $density; // set the density into objext
//        return $density;
//    }

    public function grabDensitybyMType($materialType) {
        $densityValue = 0.00;
        ##echo "in Line 101, function grabDensitybyMType, \$materialType = $materialType <br>";
        switch ($materialType) {
            case 'ts':
                $densityValue = DENSITY::ts;
                break;
            case 'aa':
                $densityValue = DENSITY::aa;
                break;
            case 'ms':
                $densityValue = DENSITY::ms;
                break;
            case 'brass':
                $densityValue = DENSITY::brass;
                break;
            case 'copper':
                $densityValue = DENSITY::copper;
                break;
            case 'sus':
                $densityValue = DENSITY::sus;
                break;
            case 'hardox':
                $densityValue = DENSITY::copper;
                break;
            case 'bronze':
                $densityValue = DENSITY::bronze;
                break;
            case 'other':
                $densityValue = DENSITY::other;
                break;
            case 'graphite':
                $densityValue = DENSITY::graphite;
                break;
            case 'plastic':
                $densityValue = DENSITY::plastic;
                break;
            default:
                $densityValue = DENSITY::other;
                break;
        }
        return $densityValue;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function calMaterialType($materialcode) {


        $sql = "SELECT materialtype FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
        #print_r($result);
        $materialtype = $result['materialtype'];
        $this->materialType = $materialtype;
        return $materialtype;
    }

}

abstract class ROD extends WORKPCS2 {

    protected $category;
    protected $CrossSectionArea;

    public function __construct() {

        $this->category = 'rod';
    }

}

Class O extends ROD {

    protected $Shape_Code;
    protected $PHI; // diameter of the rod
    protected $L; //Length
    protected $materialcode;
    protected $dimension;
    protected $unitPrice;
    protected $CrossSectionArea;

    //protected $myDimension;

    public function __construct($materialcode, $PHI, $L) {

        $this->Shape_Code = "O"; // shape code O, circular type O cross section area; Shaft
        $this->PHI = $PHI;
        $radius = $PHI / 2;
        $this->L = $L;
        $this->materialcode = $materialcode;
        $dimension = $this->formDimension();
        $this->dimension = $dimension;
        $this->CrossSectionArea = pi() * pow($radius, 2);
        $this->mainProcess();
    }

    public function mainProcess() {
        $materialcode = $this->getMaterialCode();
        $PHI = $this->getPHI();
        $L = $this->getL();
        $Shape_Code = $this->getShape_Code();
        $isShapeCodeMatch = $this->isShapeCodeMatch($materialcode);
        if ($isShapeCodeMatch !== 'yes') {
            ##echo "Material with code : $materialcode have different Shape_Code than current process";
        } else {
            //Shape_Code matches, go to next step
            $materialType = $this->calMaterialType($materialcode);
//            #echo "\$materialType = $materialType <br>";
            $density = $this->grabDensitybyMType($materialType);
//            ##echo "density in line 214 is $density<br>";
            $this->setDensity($density);
            $volume = $this->calVolume();
            $this->setVolume($volume);
            $weight = $this->calWeight($materialcode);
            ##echo "\$volume = $volume<br>";
            ##echo "\$weight = $weight<br>";
            $this->setWeight($weight);
        }
    }

    public function formDimension() {

        $PHI = $this->PHI;
        $L = $this->L;
        $Dimension = array('PHI' => $PHI, 'L' => $L);
        return $Dimension;
    }

    /**
      public function extractDimension() {

      $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
      $PHI = $dimension['0'];
      $L = $dimension['1'];

      $this->PHI = $PHI;
      $this->L = $L;
      //create new array, with keys
      $newDimension = array('PHI' => $PHI, 'L' => $L);

      return $newDimension;
      }
     * */
    public function isShapeCodeMatch() {

        $materialcode = $this->materialcode;
        $sql = "SELECT Shape_Code FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
//        print_r($result);
        $myShape_Code = $result['Shape_Code'];
        $Shape_Code = $this->Shape_Code;
//        $this->setShape_Code($Shape_Code);

        if ($myShape_Code == $Shape_Code) {
            $answer = 'yes';
        } else {
            $answer = 'no';
        }

        return $answer;
    }

    public function fetchPrice($mat, $cid, $com, $iWeight, $iPHI, $iL) { //needs cid and company code
        $PHI = floatval($iPHI);     //PHI Input Value
        $L = floatval($iL);     //Length Input Value + wastage value
        $weight = $iWeight;
        $materialcode = $this->materialcode; //get material code
        $specialPriceTbl = $materialcode . "_" . strtolower(trim($com)) . "_" . $cid; //special price table
        //Old Data Table
        $priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$PHI}'";
        ##echo "\$priceQuery0 = $priceQuery0 <br>";
        //New Data Table, Still Commented
        #$priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE PHI = '{$PHI}'";

        $priceSQL = new SQL($priceQuery0);

        $result = $priceSQL->getResultOneRowArray();
        if (!empty($result)) {
            ##echo "found table {$specialPriceTbl}<br>";
            //O extends ROD
            ##echo "<br>*******ROD-O***************start of calPrice************************************<br>";
            $weightPrice = $this->calPrice($result, $weight, $L);
        } else {
            ##echo "cannot find table {$specialPriceTbl}, Using general table.<br>";
            //old Data Table
            $priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$PHI}";
            //new Data Table; Still Commented
            #$priceQuery1 = "SELECT * FROM {$materialcode} WHERE PHI = {$PHI}";
            unset($priceSQL);
            unset($result);
            $priceSQL = new SQL($priceQuery1);
            $result = $priceSQL->getResultOneRowArray();
            if (!empty($result)) {
                ##echo "found table {$materialcode}<br>";
                ##echo "<br>*************************start of calPrice************************************<br>";
                $weightPrice = $this->calPrice($result, $weight, $L);
            } else {
                #echo "<span style='background-color:red;color:white'>Line 288/ Cannot find price using {$materialcode} and PHI = {$PHI}<br>";
                #echo "Check Material Price has been created or not for {$materialcode}</span><br>";
            }
        }
        ##echo "\$weightPrice = $weightPrice<br>";
        return $weightPrice;
    }

    public function calPrice($arr_matprice, $weight, $L) {
        $objMatPrice = new MaterialPrice($arr_matprice);
        $obLength = $L;
        ##echo "<br>****************************Class O*********************************<br>";
        ##echo "in function calPrice of Class O <br> ";
        //fetch values from OOP
        $tbWidth = $objMatPrice->getWidth();
        $tbW1 = $objMatPrice->getW1();
        $tbW2 = $objMatPrice->getW2();
        $tbPrice = $objMatPrice->getPrice();
        $tbMaxWeight = $objMatPrice->getMaxweight();
        $tbMaxLength = $objMatPrice->getMaxlength();
        $tbMaxPrice = $objMatPrice->getMaxprice();
        $tbLooseLength = $objMatPrice->getLooselength();
        $tbLoosePrice = $objMatPrice->getLooseprice();
        $tbCuttingCharges = $objMatPrice->getCuttingcharges();
        /* #echo "<pre><strong>";
          #echo "tbWidth = $tbWidth;";
          #echo "tbW1 = $tbW1;";
          #echo "tbW2 = $tbW2;";
          #echo "tbPrice = $tbPrice;";
          #echo "tbMaxWeight = $tbMaxWeight";
          #echo "tbMaxLength = $tbMaxLength;";
          #echo "tbMaxPrice = $tbMaxPrice;";
          #echo "tbLooseLength = $tbLooseLength;";
          #echo "tbLoosePrice = $tbLoosePrice;";
          #echo "tbCuttingCharges = $tbCuttingCharges;";
          #echo "</strong></pre>";
          #echo "\$obLength = $obLength<br>"; */
        //end fetch values from OOP
        //check NULL or not
        $val_tbWidth = checkIsSet($tbWidth);
        $val_tbW1 = checkIsSet($tbW1);
        $val_tbW2 = checkIsSet($tbW2);
        $val_tbPrice = checkIsSet($tbPrice);
        $val_tbMaxWeight = checkIsSet($tbMaxWeight);
        $val_tbMaxLength = checkIsSet($tbMaxLength);
        $val_tbMaxPrice = checkIsSet($tbMaxPrice);
        $val_tbLooseLength = checkIsSet($tbLooseLength);
        $val_tbLoosePrice = checkIsSet($tbLoosePrice);
        $val_tbCuttingCharges = checkIsSet($tbCuttingCharges);
        /* #echo "<pre><strong>";
          #echo "val_tbWidth = ";
          var_dump($val_tbWidth);
          #echo "val_tbW1 = ";
          var_dump($val_tbW1);
          #echo "val_tbW2 = ";
          var_dump($val_tbW2);
          #echo "val_tbPrice = ";
          var_dump($val_tbPrice);
          #echo "val_tbMaxWeight = ";
          var_dump($val_tbMaxWeight);
          #echo "val_tbMaxLength = ";
          var_dump($val_tbMaxLength);
          #echo "val_tbMaxPrice = ";
          var_dump($val_tbMaxPrice);
          #echo "val_tbLooseLength = ";
          var_dump($val_tbLooseLength);
          #echo "val_tbLoosePrice = ";
          var_dump($val_tbLoosePrice);
          #echo "val_tbCuttingCharges = ";
          var_dump($val_tbCuttingCharges);
          #echo "</strong></pre>"; */
        //end check NULL or not
        //Variable Explanation
        //$weight = unit weight in oop
        //This is Type A Calculation

        if (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLooseLength && $val_tbLoosePrice)) {
            ##echo "Found Type A <br>";
            #implement calculation A here;
            if ($obLength < $tbMaxLength) {
                $unitPrice = ($tbLoosePrice / $tbLooseLength);
                $weightPrice = $unitPrice * $obLength;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxPrice;
            } else {
                $weightPrice = 0; //cannot find price
            }
        }
        //Type B Calculation
        elseif (($val_tbW1 && $val_tbW2) && ($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice)) {
            ##echo "Found Type B <br>";
            if ($val_tbLooseLength) {
                #implement calculation B here
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif (!$val_tbLooseLength && $val_tbMaxWeight) {
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
                    $weightPrice = $unitPrice * $obLength;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            }
        }
        //Type C Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ///    if($tbCuttingCharges){
            ///        //Type K Calculation
            ///        #echo "Found Type K <br>";
            ///        #implement calculation K here
            ///    }else{
            //Type C Calculation
            ##echo "Line 424 Found Type C <br>";
            $unitPrice = $tbPrice;
            ##echo "Line 439, \$unitPrice =  $unitPrice <br> ";

            $weightPrice = $weight * $unitPrice;
            ///    }
        }
        //Type D Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice) && ($val_tbLoosePrice && $val_tbLooseLength)) {
            ##echo "Found Type D <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type E Calculation
        elseif ($val_tbWidth && (!$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ##echo "Found Type E <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
        }
        //Type F Calculation
        elseif (($val_tbWidth && $val_tbLooseLength && $val_tbLoosePrice) && (!$val_tbMaxPrice && !$val_tbMaxLength)) {
            ##echo "Found Type F <br>";
        }
        //Type G Calculation
        elseif (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
            ##echo "Found Type G <br>";
            if ($obLength < $tbMaxLength) {
                $unitPrice = $tbLoosePrice;
                $weightPrice = $weight * $unitPrice;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxWeight;
            } else {
                $weightPrice = 0; //error
            }
        }
        //Type I Calculation
        elseif (($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbWidth) {
            ///    if($val_tbCuttingCharges){
            ///        //TypeJ Calculation
            ///        #echo "Found Type J <br>";
            ///        #implement calcualtion J here
            ///        #same as type I but with cutting charges/
            ///    }else{
            //Type I Calculation$
            ##echo "Found Type I <br>";
            ##echo "\$obLength = $obLength<br>";
            ##echo "\$tbMaxLength = $tbMaxLength<br>";
            //if ($val_tbLooseLength) {// mean the data structure of type I can be found looseLength field
            if ($tbLooseLength > 0) {// mean the value in the field  >  0
                if ($obLength < $tbMaxLength) {
                    ##echo "\$tbLoosePrice = $tbLoosePrice, \$tbLooseLength = $tbLooseLength <br>";
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $weight;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif ($tbLooseLength == 0) {// mean the value in the field is equal to 0
                // which is the same case in many TYPE I tables the looseLength field value = 0.00
                if ($obLength < $tbMaxLength) {
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $weight * $unitPrice;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = $tbMaxPrice;
                    $weightPrice = $weight * $unitPrice;
                } else {
                    $weightPrice = 0; //error
                }
            }
            //price = (Looseprice / looselength * length) [if length < maxlength]
            //price = maxPrice [if length == maxLength]
            // } elseif (!$val_tbLooseLength) {//
            //!val_tbLooseLength mean the field in the data structure can't be found
            //the data structure do not have this field, which can't be happened
            //in type I table, this concept no need to implement any things.
//                if ($obLength < $tbMaxLength) {
//                    $unitPrice = $tbLoosePrice;
//                    $weightPrice = $weight * $unitPrice;
//                } elseif ($obLength == $tbMaxLength) {
//                    $unitPrice = $tbMaxPrice;
//                    $weightPrice = $weight * $unitPrice;
//                } else {
//                    $weightPrice = 0; //error
//                }
            //}
            ///    }
        } else {
            ##echo "No table type has been found <br>";
        }
//        //Type L Calculation
//        elseif (($val_tbW1 && $val_tbW2 && $val_tbMaxLength && $val_tbMaxWeight && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
//            #echo "Found Type L <br>";
//            if ($obLength < $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
//                $weightPrice = $unitPrice * $obLength;
//            } elseif ($obLength == $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
//                $weightPrice = $unitPrice * $obLength;
//            } else {
//                $weightPrice = 0; //cannot find price
//            }
//        } else {
//            #echo "No table type has been found <br>";
//        }

        /**
          if (($tbWidth&&$tbMaxPrice&&$tbMaxLength&&$tbLoosePrice&&$tbLooseLength&&$tbCuttingCharges)==0){
          //If all value is 0, then this is normal process
          }elseif($tbWidth){
          //If there's tbWidth <> 0, then inputWidth must same as tbWidth;
          }
          //if
          elseif($a){

          }
         * */
        if (isset($unitPrice)) {
            $this->setUnitPrice($unitPrice);
        }
        return $weightPrice;
    }

    public function getCrossSectionArea() {
        return $this->CrossSectionArea;
    }

    public function setCrossSectionArea($input) {
        $this->CrossSectionArea = $input;
    }

    public function getShape_Code() {
        return $this->Shape_Code;
    }

    public function setUnitPrice($input) {
        $this->unitPrice = $input;
    }

    public function getUnitPrice() {
        return $this->unitPrice;
    }

    public function setShape_Code($input) {
        $this->Shape_Code = $input;
    }

    public function getPHI() {
        return $this->PHI;
    }

    public function setPHI($input) {
        $this->PHI = $input;
    }

    public function getL() {
        return $this->L;
    }

    public function setL($input) {
        $this->L = $input;
    }

    protected function calVolume() {

        $PHI = floatval($this->PHI);
        $L = floatval($this->L);
        $radius = $PHI / 2;
        $area = pi() * (pow($radius, 2));
        $vol = $area * $L;

        ##echo "in line 267, \$vol = $vol <br>";
        $this->setVolume($vol);

        return $vol;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function calWeight() {
        $density = $this->density;
        $volume = $this->getVolume();

        #    #echo "<br>=================================================<br>";
        #    #echo "\$density = $density ,  \$volume = $volume <br> ";

        $weight = $volume * $density;
        $this->setWeight($weight);

        return $weight;
    }

}

Class HEX extends ROD {

    protected $Shape_Code;
    protected $HEX; // corss section
    protected $L; //Length
    protected $materialcode;
    protected $dimension;
    protected $unitPrice;
    protected $CrossSectionArea;

    //protected $mydimension;

    public function __construct($materialcode, $HEX, $L) {

        $this->Shape_Code = "HEX"; // shape code O, circular type O cross section area; Shaft
        $this->HEX = $HEX;
        $this->L = $L;
        $this->materialcode = $materialcode;
        $dimension = $this->formDimension();
        $this->dimension = $dimension;
        $this->CrossSectionArea = pow($HEX, 2) * (sqrt(3) / 2);
        $this->mainProcess();
    }

    public function mainProcess() {
        $materialcode = $this->getMaterialCode();
        $HEX = $this->getHEX();
        $L = $this->getL();
        $Shape_Code = $this->getShape_Code();
        $isShapeCodeMatch = $this->isShapeCodeMatch($materialcode);
        if ($isShapeCodeMatch !== 'yes') {
            ##echo "Material with code : $materialcode have different Shape_Code than current process";
        } else {
            //Shape_Code matches, go to next step
            $materialType = $this->calMaterialType($materialcode);
            $this->setMaterialType($materialType);
            $density = $this->grabDensitybyMType($materialType);
            $this->setDensity($density);
            $volume = $this->calVolume();
            $this->setVolume($volume);
            $weight = $this->calWeight($materialcode);
            ##echo "\$volume = $volume<br>";
            ##echo "\$weight = $weight<br>";
            $this->setWeight($weight);
        }
    }

    public function formDimension() {

        $HEX = $this->HEX;
        $L = $this->L;
        $Dimension = array('HEX' => $HEX, 'L' => $L);
        return $Dimension;
    }

    /**
      public function extractDimension(){
      $dimension = $this->myDimension;

      $HEX = $dimension['0'];
      $L = $dimension['1'];

      $this->HEX = $HEX;
      $this->L = $L;

      $newDimension = array('HEX' => $HEX, 'L' => $L);

      return $newDimension;
      }
     * */
    public function isShapeCodeMatch($materialcode) {

        $sql = "SELECT Shape_Code FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
        //      print_r($result);
        $myShape_Code = $result['Shape_Code'];
        $Shape_Code = $this->Shape_Code;
//        $this->setShape_Code($Shape_Code);

        if ($myShape_Code == $Shape_Code) {
            $answer = 'yes';
        } else {
            $answer = 'no';
        }

        return $answer;
    }

    public function fetchPrice($mat, $cid, $com, $iWeight, $iHEX, $iL) { //needs cid and company code
        $HEX = floatval($iHEX);     //PHI Input Value
        $L = floatval($iL);     //Length Input Value
        $weight = $iWeight;
        $materialcode = $this->materialcode; //get material code
        $specialPriceTbl = $materialcode . "_" . strtolower(trim($com)) . "_" . $cid; //special price table
        //Old Data Table
        $priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$HEX}'";
        //New Data Table, Still Commented
        #$priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE HEX = '{$HEX}'";

        $priceSQL = new SQL($priceQuery0);

        $result = $priceSQL->getResultOneRowArray();
        if (!empty($result)) {
            ##echo "found table {$specialPriceTbl}<br>";
            ##echo "<br>*************************start of calPrice************************************<br>";
            $weightPrice = $this->calPrice($result, $weight, $L);
        } else {
            ##echo "cannot find table {$specialPriceTbl}, Using general table.<br>";
            //old Data Table
            $priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$HEX}";
            //new Data Table; Still Commented
            #$priceQuery1 = "SELECT * FROM {$materialcode} WHERE HEX = {$HEX}";
            unset($priceSQL);
            unset($result);
            $priceSQL = new SQL($priceQuery1);
            $result = $priceSQL->getResultOneRowArray();
            if (!empty($result)) {
                ##echo "found table {$materialcode}";
                ##echo "<br>*************************start of calPrice************************************<br>";
                $weightPrice = $this->calPrice($result, $weight, $L);
                #echo "<br>*************************End of calPrice************************************<br>";
            } else {
                #echo "<span style='background-color:red;color:white'>Line 288/ Cannot find price using {$materialcode} and HEX = {$HEX}<br>";
                #echo "Check Material Price has been created or not for {$materialcode}</span><br>";
                $weightPrice = 0;
            }
        }

        #echo "\$weightPrice = $weightPrice<br>";
        return $weightPrice;
    }

    public function calPrice($arr_matprice, $weight, $L) {
        $objMatPrice = new MaterialPrice($arr_matprice);
        $thick = $this->getHEX();
        #echo "<br>****************************Class HEX*********************************<br>";
        #echo "in function calPrice of Class HEX <br> ";
        //fetch values from OOP
        $tbWidth = $objMatPrice->getWidth();
        $tbW1 = $objMatPrice->getW1();
        $tbW2 = $objMatPrice->getW2();
        $tbPrice = $objMatPrice->getPrice();
        $tbMaxWeight = $objMatPrice->getMaxweight();
        $tbMaxLength = $objMatPrice->getMaxlength();
        $tbMaxPrice = $objMatPrice->getMaxprice();
        $tbLooseLength = $objMatPrice->getLooselength();
        $tbLoosePrice = $objMatPrice->getLooseprice();
        $tbCuttingCharges = $objMatPrice->getCuttingcharges();
        /* #echo "<pre><strong>";
          #echo "tbWidth = $tbWidth;";
          #echo "tbW1 = $tbW1;";
          #echo "tbW2 = $tbW2;";
          #echo "tbPrice = $tbPrice;";
          #echo "tbMaxWeight = $tbMaxWeight";
          #echo "tbMaxLength = $tbMaxLength;";
          #echo "tbMaxPrice = $tbMaxPrice;";
          #echo "tbLooseLength = $tbLooseLength;";
          #echo "tbLoosePrice = $tbLoosePrice;";
          #echo "tbCuttingCharges = $tbCuttingCharges;";
          #echo "</strong></pre>";
          #echo "\$obLength = $obLength<br>"; */
        //end fetch values from OOP
        //check NULL or not
        $val_tbWidth = checkIsSet($tbWidth);
        $val_tbW1 = checkIsSet($tbW1);
        $val_tbW2 = checkIsSet($tbW2);
        $val_tbPrice = checkIsSet($tbPrice);
        $val_tbMaxWeight = checkIsSet($tbMaxWeight);
        $val_tbMaxLength = checkIsSet($tbMaxLength);
        $val_tbMaxPrice = checkIsSet($tbMaxPrice);
        $val_tbLooseLength = checkIsSet($tbLooseLength);
        $val_tbLoosePrice = checkIsSet($tbLoosePrice);
        $val_tbCuttingCharges = checkIsSet($tbCuttingCharges);
        /* #echo "<pre><strong>";
          #echo "val_tbWidth = ";
          var_dump($val_tbWidth);
          #echo "val_tbW1 = ";
          var_dump($val_tbW1);
          #echo "val_tbW2 = ";
          var_dump($val_tbW2);
          #echo "val_tbPrice = ";
          var_dump($val_tbPrice);
          #echo "val_tbMaxWeight = ";
          var_dump($val_tbMaxWeight);
          #echo "val_tbMaxLength = ";
          var_dump($val_tbMaxLength);
          #echo "val_tbMaxPrice = ";
          var_dump($val_tbMaxPrice);
          #echo "val_tbLooseLength = ";
          var_dump($val_tbLooseLength);
          #echo "val_tbLoosePrice = ";
          var_dump($val_tbLoosePrice);
          #echo "val_tbCuttingCharges = ";
          var_dump($val_tbCuttingCharges);
          #echo "</strong></pre>"; */
        //end check NULL or not
        //Variable Explanation
        //$weight = unit weight in oop
        //This is Type A Calculation

        if (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLooseLength && $val_tbLoosePrice)) {
            #echo "Found Type A <br>";
            #implement calculation A here;
            if ($obLength < $tbMaxLength) {
                $unitPrice = ($tbLoosePrice / $tbLooseLength);
                $weightPrice = $unitPrice * $obLength;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxPrice;
                $unitPrice = $weightPrice / $weight;
            } else {
                $weightPrice = 0; //cannot find price
            }
        }
        //Type B Calculation
        elseif (($val_tbW1 && $val_tbW2) && ($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice)) {
            #echo "Found Type B <br>";
            if ($val_tbLooseLength) {
                #implement calculation B here
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                    $unitPrice = $weightPrice / $weight;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif (!$val_tbLooseLength && $val_tbMaxWeight) {
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
                    $weightPrice = $unitPrice * $obLength;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            }
        }
        //Type C Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ///    if($tbCuttingCharges){
            ///        //Type K Calculation
            ///        #echo "Found Type K <br>";
            ///        #implement calculation K here
            ///    }else{
            //Type C Calculation
            #echo "Line 859  Found Type C <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
            ///    }
        }
        //Type D Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice) && ($val_tbLoosePrice && $val_tbLooseLength)) {
            #echo "Found Type D <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type E Calculation
        elseif ($val_tbWidth && (!$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            #echo "Found Type E <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
        }
        //Type F Calculation
        elseif (($val_tbWidth && $val_tbLooseLength && $val_tbLoosePrice) && (!$val_tbMaxPrice && !$val_tbMaxLength)) {
            #echo "Found Type F <br>";
        }
        //Type G Calculation
        elseif (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
            #echo "Found Type G <br>";
            if ($obLength < $tbMaxLength) {
                $unitPrice = $tbLoosePrice;
                $weightPrice = $weight * $unitPrice;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxWeight;
                $unitPrice = $weightPrice / $weight;
            } else {
                $weightPrice = 0; //error
            }
        }
        //Type I Calculation
        elseif (($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbWidth) {
            ///    if($val_tbCuttingCharges){
            ///        //TypeJ Calculation
            ///        #echo "Found Type J <br>";
            ///        #implement calcualtion J here
            ///        #same as type I but with cutting charges/
            ///    }else{
            //Type I Calculation$
            #echo "Found Type I <br>";
            #echo "\$obLength = $obLength<br>";
            #echo "\$tbMaxLength = $tbMaxLength<br>";
            //if ($val_tbLooseLength) {// mean the data structure of type I can be found looseLength field
            if ($tbLooseLength > 0) {// mean the value in the field  >  0
                if ($obLength < $tbMaxLength) {
                    #echo "\$tbLoosePrice = $tbLoosePrice, \$tbLooseLength = $tbLooseLength <br>";
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength / $tbLooseLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                    $unitPrice = $weightPrice / $weight;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif ($tbLooseLength == 0) {// mean the value in the field is equal to 0
                // which is the same case in many TYPE I tables the looseLength field value = 0.00
                if ($obLength < $tbMaxLength) {
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $weight * $unitPrice;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = $tbMaxPrice;
                    $weightPrice = $weight * $unitPrice;
                } else {
                    $weightPrice = 0; //error
                }
            }
            //price = (Looseprice / looselength * length) [if length < maxlength]
            //price = maxPrice [if length == maxLength]
            // } elseif (!$val_tbLooseLength) {//
            //!val_tbLooseLength mean the field in the data structure can't be found
            //the data structure do not have this field, which can't be happened
            //in type I table, this concept no need to implement any things.
//                if ($obLength < $tbMaxLength) {
//                    $unitPrice = $tbLoosePrice;
//                    $weightPrice = $weight * $unitPrice;
//                } elseif ($obLength == $tbMaxLength) {
//                    $unitPrice = $tbMaxPrice;
//                    $weightPrice = $weight * $unitPrice;
//                } else {
//                    $weightPrice = 0; //error
//                }
            //}
            ///    }
        } else {
            #echo "No table type has been found <br>";
        }
//        //Type L Calculation
//        elseif (($val_tbW1 && $val_tbW2 && $val_tbMaxLength && $val_tbMaxWeight && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
//            #echo "Found Type L <br>";
//            if ($obLength < $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
//                $weightPrice = $unitPrice * $obLength;
//            } elseif ($obLength == $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
//                $weightPrice = $unitPrice * $obLength;
//            } else {
//                $weightPrice = 0; //cannot find price
//            }
//        } else {
//            #echo "No table type has been found <br>";
//        }

        /**
          if (($tbWidth&&$tbMaxPrice&&$tbMaxLength&&$tbLoosePrice&&$tbLooseLength&&$tbCuttingCharges)==0){
          //If all value is 0, then this is normal process
          }elseif($tbWidth){
          //If there's tbWidth <> 0, then inputWidth must same as tbWidth;
          }
          //if
          elseif($a){

          }
         * */
        $this->setUnitPrice($unitPrice);
        return $weightPrice;
    }

    public function getCrossSectionArea() {
        return $this->CrossSectionArea;
    }

    public function setCrossSectionArea($input) {
        $this->CrossSectionArea = $input;
    }

    public function getUnitPrice() {
        return $this->unitPrice;
    }

    public function setUnitPrice($input) {
        $this->unitPrice = $input;
    }

    public function getShape_Code() {
        return $this->Shape_Code;
    }

    public function setShape_Code($input) {
        $this->Shape_Code = $input;
    }

    public function getHEX() {
        return $this->HEX;
    }

    public function setHEX($input) {
        $this->HEX = $input;
    }

    public function getL() {
        return $this->L;
    }

    public function setL($input) {
        $this->L = $input;
    }

    protected function calVolume() {

        $HEX = floatval($this->HEX);
        $L = floatval($this->L);

        $area = pow($HEX, 2) * (sqrt(3) / 2);
        $vol = $area * $L;

        ##echo "in line 267, \$vol = $vol <br>";
        $this->setVolume($vol);

        return $vol;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function calWeight() {
        $density = $this->density;
        $volume = $this->getVolume();

        # #echo "<br>=================================================<br>";
        # #echo "\$density = $density ,  \$volume = $volume <br> ";

        $weight = $volume * $density;
        $this->setWeight($weight);

        return $weight;
    }

}

Class SS extends ROD {

    protected $Shape_Code;
    protected $W1; // 1st side width
    protected $W2; // 2nd Side Width
    protected $L; //Length
    protected $materialcode;
    protected $dimension;
    protected $unitPrice;
    protected $CrossSectionArea;

    //protected $mydimension;

    public function __construct($materialcode, $W1, $W2, $L) {

        $this->Shape_Code = "SS"; // shape code O, circular type O cross section area; Shaft
        $this->W1 = $W1;
        $this->W2 = $W2;
        $this->L = $L;
        $this->materialcode = $materialcode;
        $dimension = $this->formDimension();
        $this->dimension = $dimension;
        $this->CrossSectionArea = $W1 * $W2;
        $this->mainProcess();
    }

    public function mainProcess() {
        $materialcode = $this->getMaterialCode();
        $W1 = $this->getW1();
        $W2 = $this->getW2();
        $L = $this->getL();
        $Shape_Code = $this->getShape_Code();
        $isShapeCodeMatch = $this->isShapeCodeMatch($materialcode);
        if ($isShapeCodeMatch !== 'yes') {
            #echo "Material with code : $materialcode have different Shape_Code than current process";
        } else {
            //Shape_Code matches, go to next step
            $materialType = $this->calMaterialType($materialcode);
            $this->setMaterialType($materialType);
            $density = $this->grabDensitybyMType($materialType);
            $this->setDensity($density);
            $volume = $this->calVolume();
            $this->setVolume($volume);
            $weight = $this->calWeight($materialcode);
//            #echo "\$volume = $volume<br>";
//            #echo "\$weight = $weight<br>";
            $this->setWeight($weight);
        }
    }

    public function formDimension() {
        $W1 = $this->W1;
        $W2 = $this->W2;
        $L = $this->L;
        $Dimension = array('W1' => $W1, 'W2' => $W2, 'L' => $L);
        return $Dimension;
    }

    /**
      public function extractDimension(){
      $dimension = $this->myDimension;

      $W1 = $dimension['0'];
      $W2 = $dimension['1'];
      $L = $dimension['2'];

      $this->W1 = $W1;
      $this->W2 = $W2;
      $this->L = $L;

      $newDimension = array('W1' => $ID, 'W2' => $OD, 'L' => $L);

      return $newDimension;
      }
     * */
    public function isShapeCodeMatch($materialcode) {

        $sql = "SELECT Shape_Code FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
        //   print_r($result);
        $myShape_Code = $result['Shape_Code'];
        $Shape_Code = $this->Shape_Code;
//        $this->setShape_Code($Shape_Code);

        if ($myShape_Code == $Shape_Code) {
            $answer = 'yes';
        } else {
            $answer = 'no';
        }

        return $answer;
    }

    public function fetchPrice($mat, $cid, $com, $iWeight, $iW1, $iW2, $iL) { //needs cid and company code
        #echo "in fetchPrice function <br>";
        $W1 = floatval($iW1);   //W1 Input Value
        $W2 = floatval($iW2);   //W2 Input Value
        $L = floatval($iL);     //Length Input Value
        $weight = $iWeight;
        $materialcode = $this->materialcode; //get material code
        $specialPriceTbl = $materialcode . "_" . strtolower(trim($com)) . "_" . $cid; //special price table
        //Old Data Table
        $priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$W1}'";
        #echo " \$priceQuery0 =  $priceQuery0 <br>";
        //New Data Table, Still Commented
        #$priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE W1 = '{$W1}'";

        $priceSQL = new SQL($priceQuery0);

        $result = $priceSQL->getResultOneRowArray();
        if (!empty($result)) {
            #echo "found table {$specialPriceTbl}<br>";
            $weightPrice = $this->calPrice($result, $weight, $L);
        } else {
            #echo "cannot find table {$specialPriceTbl}, Using general table.<br>";
            //old Data Table
            $priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$W1}";
            #echo " \$priceQuery1 =  $priceQuery1 <br>";
            //new Data Table; Still Commented
            #$priceQuery1 = "SELECT * FROM {$materialcode} WHERE W1 = {$W1}";
            unset($priceSQL);
            unset($result);
            $priceSQL = new SQL($priceQuery1);
            $result = $priceSQL->getResultOneRowArray();
            if (!empty($result)) {
                #echo "found table {$materialcode}<br>";
                #echo "###################################<br>";
                //        #echo "print_r(\$result)<br>";
                //       print_r($result);
                #echo"<br>";
                #echo "\$weight = $weight,  \$L = $L <br> ";
                $weightPrice = $this->calPrice($result, $weight, $L);
            } else {
                #echo "<span style='background-color:red;color:white'>Line 288/ Cannot find price using {$materialcode} and W1 = {$W1}<br>";
                #echo "Check Material Price has been created or not for {$materialcode}</span><br>";
            }
        }

        #echo "\$weightPrice = $weightPrice<br>";
        return $weightPrice;
    }

    public function calPrice($arr_matprice, $weight, $L) {
        $objMatPrice = new MaterialPrice($arr_matprice);
        $thick = $this->getW1();
        ///$obLength = $L;
        #echo "<br>************************Class SS***********************************<br>";
        #echo "in function calPrice of Class SS <br> ";
        //fetch values from OOP
        $tbWidth = $objMatPrice->getWidth();
        $tbW1 = $objMatPrice->getW1();
        $tbW2 = $objMatPrice->getW2();
        $tbPrice = $objMatPrice->getPrice();
        $tbMaxWeight = $objMatPrice->getMaxweight();
        $tbMaxLength = $objMatPrice->getMaxlength();
        $tbMaxPrice = $objMatPrice->getMaxprice();
        $tbLooseLength = $objMatPrice->getLooselength();
        $tbLoosePrice = $objMatPrice->getLooseprice();
        $tbCuttingCharges = $objMatPrice->getCuttingcharges();
        /* #echo "<pre><strong>";
          #echo "tbWidth = $tbWidth;";
          #echo "tbW1 = $tbW1;";
          #echo "tbW2 = $tbW2;";
          #echo "tbPrice = $tbPrice;";
          #echo "tbMaxWeight = $tbMaxWeight";
          #echo "tbMaxLength = $tbMaxLength;";
          #echo "tbMaxPrice = $tbMaxPrice;";
          #echo "tbLooseLength = $tbLooseLength;";
          #echo "tbLoosePrice = $tbLoosePrice;";
          #echo "tbCuttingCharges = $tbCuttingCharges;";
          #echo "</strong></pre>";
          #echo "\$obLength = $obLength<br>"; */
        //end fetch values from OOP
        //check NULL or not
        $val_tbWidth = checkIsSet($tbWidth);
        $val_tbW1 = checkIsSet($tbW1);
        $val_tbW2 = checkIsSet($tbW2);
        $val_tbPrice = checkIsSet($tbPrice);
        $val_tbMaxWeight = checkIsSet($tbMaxWeight);
        $val_tbMaxLength = checkIsSet($tbMaxLength);
        $val_tbMaxPrice = checkIsSet($tbMaxPrice);
        $val_tbLooseLength = checkIsSet($tbLooseLength);
        $val_tbLoosePrice = checkIsSet($tbLoosePrice);
        $val_tbCuttingCharges = checkIsSet($tbCuttingCharges);
        /* #echo "<pre><strong>";
          #echo "val_tbWidth = ";
          var_dump($val_tbWidth);
          #echo "val_tbW1 = ";
          var_dump($val_tbW1);
          #echo "val_tbW2 = ";
          var_dump($val_tbW2);
          #echo "val_tbPrice = ";
          var_dump($val_tbPrice);
          #echo "val_tbMaxWeight = ";
          var_dump($val_tbMaxWeight);
          #echo "val_tbMaxLength = ";
          var_dump($val_tbMaxLength);
          #echo "val_tbMaxPrice = ";
          var_dump($val_tbMaxPrice);
          #echo "val_tbLooseLength = ";
          var_dump($val_tbLooseLength);
          #echo "val_tbLoosePrice = ";
          var_dump($val_tbLoosePrice);
          #echo "val_tbCuttingCharges = ";
          var_dump($val_tbCuttingCharges);
          #echo "</strong></pre>"; */
        //end check NULL or not
        //Variable Explanation
        //$weight = unit weight in oop
        //This is Type A Calculation

        if (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLooseLength && $val_tbLoosePrice)) {
            #echo "Found Type A <br>";
            #implement calculation A here;
            if ($obLength < $tbMaxLength) {
                $unitPrice = ($tbLoosePrice / $tbLooseLength);
                $weightPrice = $unitPrice * $obLength;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxPrice;
                $unitPrice = $weightPrice / $weight;
            } else {
                $weightPrice = 0; //cannot find price
            }
        }
        //Type B Calculation
        elseif (($val_tbW1 && $val_tbW2) && ($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice)) {
            #echo "Found Type B <br>";
            if ($val_tbLooseLength) {
                #implement calculation B here
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                    $unitPrice = $weightPrice / $weight;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif (!$val_tbLooseLength && $val_tbMaxWeight) {
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
                    $weightPrice = $unitPrice * $obLength;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            }
        }
        //Type C Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ///    if($tbCuttingCharges){
            ///        //Type K Calculation
            ///        #echo "Found Type K <br>";
            ///        #implement calculation K here
            ///    }else{
            //Type C Calculation
            #echo "Line 1295 Found Type C <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
            ///    }
        }
        //Type D Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice) && ($val_tbLoosePrice && $val_tbLooseLength)) {
            #echo "Found Type D <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type E Calculation
        elseif ($val_tbWidth && (!$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            #echo "Found Type E <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
        }
        //Type F Calculation
        elseif (($val_tbWidth && $val_tbLooseLength && $val_tbLoosePrice) && (!$val_tbMaxPrice && !$val_tbMaxLength)) {
            #echo "Found Type F <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type G Calculation
        elseif (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
            #echo "Found Type G <br>";
            if ($obLength < $tbMaxLength) {
                $unitPrice = $tbLoosePrice;
                $weightPrice = $weight * $unitPrice;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxWeight;
                $unitPrice = $weightPrice / $weight;
            } else {
                $weightPrice = 0; //error
            }
        }
        //Type I Calculation
        elseif (($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbWidth) {
            ///    if($val_tbCuttingCharges){
            ///        //TypeJ Calculation
            ///        #echo "Found Type J <br>";
            ///        #implement calcualtion J here
            ///        #same as type I but with cutting charges/
            ///    }else{
            //Type I Calculation$
            #echo "Found Type I <br>";
            #echo "\$obLength = $obLength<br>";
            #echo "\$tbMaxLength = $tbMaxLength<br>";
            //if ($val_tbLooseLength) {// mean the data structure of type I can be found looseLength field
            if ($tbLooseLength > 0) {// mean the value in the field  >  0
                if ($obLength < $tbMaxLength) {
                    #echo "\$tbLoosePrice = $tbLoosePrice, \$tbLooseLength = $tbLooseLength <br>";
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $weight;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                    $unitPrice = $weightPrice / $weight;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif ($tbLooseLength == 0) {// mean the value in the field is equal to 0
                // which is the same case in many TYPE I tables the looseLength field value = 0.00
                if ($obLength < $tbMaxLength) {
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $weight * $unitPrice;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = $tbMaxPrice;
                    $weightPrice = $weight * $unitPrice;
                } else {
                    $weightPrice = 0; //error
                }
            }
            //price = (Looseprice / looselength * length) [if length < maxlength]
            //price = maxPrice [if length == maxLength]
            // } elseif (!$val_tbLooseLength) {//
            //!val_tbLooseLength mean the field in the data structure can't be found
            //the data structure do not have this field, which can't be happened
            //in type I table, this concept no need to implement any things.
//                if ($obLength < $tbMaxLength) {
//                    $unitPrice = $tbLoosePrice;
//                    $weightPrice = $weight * $unitPrice;
//                } elseif ($obLength == $tbMaxLength) {
//                    $unitPrice = $tbMaxPrice;
//                    $weightPrice = $weight * $unitPrice;
//                } else {
//                    $weightPrice = 0; //error
//                }
            //}
            ///    }
        } else {
            #echo "No table type has been found <br>";
        }
//        //Type L Calculation
//        elseif (($val_tbW1 && $val_tbW2 && $val_tbMaxLength && $val_tbMaxWeight && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
//            #echo "Found Type L <br>";
//            if ($obLength < $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
//                $weightPrice = $unitPrice * $obLength;
//            } elseif ($obLength == $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
//                $weightPrice = $unitPrice * $obLength;
//            } else {
//                $weightPrice = 0; //cannot find price
//            }
//        } else {
//            #echo "No table type has been found <br>";
//        }

        /**
          if (($tbWidth&&$tbMaxPrice&&$tbMaxLength&&$tbLoosePrice&&$tbLooseLength&&$tbCuttingCharges)==0){
          //If all value is 0, then this is normal process
          }elseif($tbWidth){
          //If there's tbWidth <> 0, then inputWidth must same as tbWidth;
          }
          //if
          elseif($a){

          }
         * */
        /*
          //Begin Check for checkMinPrice

          if ($weightPrice !== 0) {
          $minPrice = $this->checkMinPrice($weightPrice);
          if ($minPrice == 0){

          }else{
          $weightPrice = $minPrice;
          $unitPrice = $minPrice;
          }
          }
          //end check
         */
        $this->setUnitPrice($unitPrice);
        return $weightPrice;
    }

    public function checkMinPrice($weightPrice, $com, $cid) {
        #echo "<br>Line 1470 function checkMinPrice $$$$$$$$$$$$$$$$$$$$$$$$$$$$<br>";
        $materialcode = $this->getMaterialCode();
        $materialtype = $this->getMaterialType();
        #echo "on function checkMinPrice, materialType = $materialtype, weightPrice = $weightPrice<br>";
        switch ($materialtype) {
            case 'ts':
                #echo "found 'ts'<br>";
                if ($weightPrice <= 5.00) {
                    $MinPrice = 5.00;
                } else {
                    $MinPrice = 0.00;
                }
                break;
            case 'aa':
                #echo "found 'aa'<br>";
                if ($materialcode == 'a5052ap' || $materialcode == 'ss303p' || $materialcode == 'yh52p' || $materialcode == 'aa6061t651p') {
                    if ($weightPrice <= 0.5) {
                        $MinPrice = 0.5;
                    } elseif ($unitPrice <= 3.0) {
                        $MinPrice = 5.00;
                    }
                } else {
                    if ($weightPrice <= 3.0) {
                        $MinPrice = 3.00;
                    } else {
                        $MinPrice = 0.00;
                    }
                }
                break;
            case 'ms':
                #echo "found 'ms'<br>";
                if ($weightPrice <= 3.00) {
                    $MinPrice = 3.00;
                } else {
                    $MinPrice = 0.00;
                }
                break;

            default:
                if ($materialcode == 'mlcgphh' || $materialcode == 'mlcgpst') {
                    if (($com == 'PST' && $cid = 5171) || ($com == 'PST' && $cid == 21187) || ($com == 'PST' && $cid == 21188)) {
                        if ($weightPrice < 0.5) {
                            $MinPrice = 0.5;
                        } else {
                            $MinPrice = 0.00;
                        }
                    } else {
                        if ($weightPrice < 3) {
                            $MinPrice = 3.00;
                        } else {
                            $MinPrice = 0.00;
                        }
                    }
                } else {
                    if ($weightPrice < 3) {
                        $MinPrice = 3.00;
                    } else {
                        $MinPrice = 0.00;
                    }
                }
                break;
        }
        return $MinPrice;
    }

    public function getCrossSectionArea() {
        return $this->CrossSectionArea;
    }

    public function setCrossSectionArea($input) {
        $this->CrossSectionArea = $input;
    }

    public function setUnitPrice($input) {
        $this->unitPrice = $input;
    }

    public function getUnitPrice() {
        return $this->unitPrice;
    }

    public function getShape_Code() {
        return $this->Shape_Code;
    }

    public function setShape_Code($input) {
        $this->Shape_Code = $input;
    }

    public function getW1() {
        return $this->W1;
    }

    public function setW1($input) {
        $this->W1 = $input;
    }

    public function getW2() {
        return $this->W2;
    }

    public function setW2($input) {
        $this->W2 = $input;
    }

    public function getL() {
        return $this->L;
    }

    public function setL($input) {
        $this->L = $input;
    }

    public function calVolume() {

        $W1 = floatval($this->W1);
        $W2 = floatval($this->W2);
        $L = floatval($this->L);

        $area = $W1 * $W2;

        $vol = $area * $L;

        ##echo "in line 267, \$vol = $vol <br>";
        $this->setVolume($vol);

        return $vol;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function calWeight() {
        $density = $this->density;
        $volume = $this->getVolume();

        #    #echo "<br>=================================================<br>";
        #    #echo "\$density = $density ,  \$volume = $volume <br> ";

        $weight = $volume * $density;
        $this->setWeight($weight);

        return $weight;
    }

}

Class A extends ROD {

    protected $Shape_Code;
    protected $T; //thickness
    protected $W1; // 1st side width
    protected $W2; // 2nd Side Width
    protected $L; //Length
    protected $materialcode;
    protected $dimension;
    protected $unitPrice;
    protected $CrossSectionArea;

    //protected $mydimension;

    public function __construct($materialcode, $T, $W1, $W2, $L) {

        $this->Shape_Code = "A"; // shape code O, circular type O cross section area; Shaft
        $this->T = $T;
        $this->W1 = $W1;
        $this->W2 = $W2;
        $this->L = $L;
        $this->materialcode = $materialcode;
        $dimension = $this->formDimension();
        $this->dimension = $dimension;
        // Explanation for CrossSectionArea :
        // https://www.evernote.com/shard/s436/sh/168b3c51-93ef-134c-ed65-967caa5347cf/b67112c794e8b5af820f63494b754b25
        $this->CrossSectionArea = $T * ($W1 + $W2 - $T);
        $this->mainProcess();
    }

    public function mainProcess() {
        $materialcode = $this->getMaterialCode();
        $T = $this->getT();
        $W1 = $this->getW1();
        $W2 = $this->getW2();
        $L = $this->getL();
        $Shape_Code = $this->getShape_Code();
        $isShapeCodeMatch = $this->isShapeCodeMatch($materialcode);
        if ($isShapeCodeMatch !== 'yes') {
            #echo "Material with code : $materialcode have different Shape_Code than current process";
        } else {
            //Shape_Code matches, go to next step
            $materialType = $this->calMaterialType($materialcode);
            $density = $this->grabDensitybyMType($materialType);
            $this->setDensity($density);
            $volume = $this->calVolume();
            $this->setVolume($volume);
            $weight = $this->calWeight($materialcode);
//            #echo "\$volume = $volume<br>";
//            #echo "\$weight = $weight<br>";
            $this->setWeight($weight);
        }
    }

    public function formDimension() {
        $T = $this->T;
        $W1 = $this->W1;
        $W2 = $this->W2;
        $L = $this->L;
        $Dimension = array('T' => $T, 'W1' => $W1, 'W2' => $W2, 'L' => $L);
        return $Dimension;
    }

    /**
      public function extractDimension(){
      $dimension = $this->myDimension;

      $W1 = $dimension['0'];
      $W2 = $dimension['1'];
      $L = $dimension['2'];

      $this->W1 = $W1;
      $this->W2 = $W2;
      $this->L = $L;

      $newDimension = array('W1' => $ID, 'W2' => $OD, 'L' => $L);

      return $newDimension;
      }
     * */
    public function isShapeCodeMatch($materialcode) {

        $sql = "SELECT Shape_Code FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
        // print_r($result);
        $myShape_Code = $result['Shape_Code'];
        $Shape_Code = $this->Shape_Code;
//        $this->setShape_Code($Shape_Code);

        if ($myShape_Code == $Shape_Code) {
            $answer = 'yes';
        } else {
            $answer = 'no';
        }

        return $answer;
    }

    public function fetchPrice($mat, $cid, $com, $iWeight, $iT, $iW1, $iW2, $iL) { //needs cid and company code
        $T = floatval($iT);     //Thickness Input Value
        $W1 = floatval($iW1);   //W1 Input Value
        $W2 = floatval($iW2);   //W2 Input Value
        $L = floatval($iL);     //Length Input Value
        $weight = $iWeight;
        $materialcode = $this->materialcode; //get material code
        $specialPriceTbl = $materialcode . "_" . strtolower(trim($com)) . "_" . $cid; //special price table
        $lWidth = $iW1 . " x " . $iW2;
        #echo"\$lWidth = $lWidth";
        //Old Data Table
        $priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}' AND width LIKE '{$lWidth}'";
        //New Data Table, Still Commented
        #$priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}' AND W1 = '{$W1}' AND W2 = '{W2}'";

        $priceSQL = new SQL($priceQuery0);

        $result = $priceSQL->getResultOneRowArray();
        if (!empty($result)) {
            #echo "found table {$specialPriceTbl}<br>";
            #echo "<br>*************************start of calPrice************************************<br>";
            $weightPrice = $this->calPrice($result, $weight, $L);
        } else {
            #echo "cannot find table {$specialPriceTbl}, Using general table.<br>";
            //old Data Table
            $priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T} AND width LIKE '{$lWidth}'";
            //new Data Table; Still Commented
            #$priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T} AND W1 = '{$W1}' AND W2 = '{W2}'";
            unset($priceSQL);
            unset($result);
            $priceSQL = new SQL($priceQuery1);
            $result = $priceSQL->getResultOneRowArray();
            if (!empty($result)) {
                #echo "found table {$materialcode}";
                #echo "<br>*************************start of calPrice************************************<br>";
                $weightPrice = $this->calPrice($result, $weight, $L);
            } else {
                #echo "<span style='background-color:red;color:white'>Line 288/ Cannot find price using {$materialcode} and thickness = {$T}<br>";
                #echo "Check Material Price has been created or not for {$materialcode}</span><br>";
            }
        }

        #echo "\$weightPrice = $weightPrice<br>";
        return $weightPrice;
    }

    public function calPrice($arr_matprice, $weight, $L) {
        $objMatPrice = new MaterialPrice($arr_matprice);
        $thick = $this->T;
        ///$obLength = $L;
        #echo "<br>************************Class A***********************************<br>";
        #echo "in function calPrice of Class A <br> ";
        //fetch values from OOP
        $tbWidth = $objMatPrice->getWidth();
        $tbW1 = $objMatPrice->getW1();
        $tbW2 = $objMatPrice->getW2();
        $tbPrice = $objMatPrice->getPrice();
        $tbMaxWeight = $objMatPrice->getMaxweight();
        $tbMaxLength = $objMatPrice->getMaxlength();
        $tbMaxPrice = $objMatPrice->getMaxprice();
        $tbLooseLength = $objMatPrice->getLooselength();
        $tbLoosePrice = $objMatPrice->getLooseprice();
        $tbCuttingCharges = $objMatPrice->getCuttingcharges();
        /* #echo "<pre><strong>";
          #echo "tbWidth = $tbWidth;";
          #echo "tbW1 = $tbW1;";
          #echo "tbW2 = $tbW2;";
          #echo "tbPrice = $tbPrice;";
          #echo "tbMaxWeight = $tbMaxWeight";
          #echo "tbMaxLength = $tbMaxLength;";
          #echo "tbMaxPrice = $tbMaxPrice;";
          #echo "tbLooseLength = $tbLooseLength;";
          #echo "tbLoosePrice = $tbLoosePrice;";
          #echo "tbCuttingCharges = $tbCuttingCharges;";
          #echo "</strong></pre>";
          #echo "\$obLength = $obLength<br>"; */
        //end fetch values from OOP
        //check NULL or not
        $val_tbWidth = checkIsSet($tbWidth);
        $val_tbW1 = checkIsSet($tbW1);
        $val_tbW2 = checkIsSet($tbW2);
        $val_tbPrice = checkIsSet($tbPrice);
        $val_tbMaxWeight = checkIsSet($tbMaxWeight);
        $val_tbMaxLength = checkIsSet($tbMaxLength);
        $val_tbMaxPrice = checkIsSet($tbMaxPrice);
        $val_tbLooseLength = checkIsSet($tbLooseLength);
        $val_tbLoosePrice = checkIsSet($tbLoosePrice);
        $val_tbCuttingCharges = checkIsSet($tbCuttingCharges);
        /* #echo "<pre><strong>";
          #echo "val_tbWidth = ";
          var_dump($val_tbWidth);
          #echo "val_tbW1 = ";
          var_dump($val_tbW1);
          #echo "val_tbW2 = ";
          var_dump($val_tbW2);
          #echo "val_tbPrice = ";
          var_dump($val_tbPrice);
          #echo "val_tbMaxWeight = ";
          var_dump($val_tbMaxWeight);
          #echo "val_tbMaxLength = ";
          var_dump($val_tbMaxLength);
          #echo "val_tbMaxPrice = ";
          var_dump($val_tbMaxPrice);
          #echo "val_tbLooseLength = ";
          var_dump($val_tbLooseLength);
          #echo "val_tbLoosePrice = ";
          var_dump($val_tbLoosePrice);
          #echo "val_tbCuttingCharges = ";
          var_dump($val_tbCuttingCharges);
          #echo "</strong></pre>"; */
        //end check NULL or not
        //Variable Explanation
        //$weight = unit weight in oop
        //This is Type A Calculation

        if (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLooseLength && $val_tbLoosePrice)) {
            #echo "Found Type A <br>";
            #implement calculation A here;
            if ($obLength < $tbMaxLength) {
                $unitPrice = ($tbLoosePrice / $tbLooseLength);
                $weightPrice = $unitPrice * $obLength;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxPrice;
                $unitPrice = $tbMaxPrice / $weight;
            } else {
                $weightPrice = 0; //cannot find price
            }
        }
        //Type B Calculation
        elseif (($val_tbW1 && $val_tbW2) && ($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice)) {
            #echo "Found Type B <br>";
            if ($val_tbLooseLength) {
                #implement calculation B here
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                    $unitPrice = $weightPrice / $weight;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif (!$val_tbLooseLength && $val_tbMaxWeight) {
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
                    $weightPrice = $unitPrice * $obLength;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            }
        }
        //Type C Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ///    if($tbCuttingCharges){
            ///        //Type K Calculation
            ///        #echo "Found Type K <br>";
            ///        #implement calculation K here
            ///    }else{
            //Type C Calculation
            #echo "Line 1742 Found Type C <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
            ///    }
        }
        //Type D Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice) && ($val_tbLoosePrice && $val_tbLooseLength)) {
            #echo "Found Type D <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type E Calculation
        elseif ($val_tbWidth && (!$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            #echo "Found Type E <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
        }
        //Type F Calculation
        elseif (($val_tbWidth && $val_tbLooseLength && $val_tbLoosePrice) && (!$val_tbMaxPrice && !$val_tbMaxLength)) {
            #echo "Found Type F <br>";
        }
        //Type G Calculation
        elseif (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
            #echo "Found Type G <br>";
            if ($obLength < $tbMaxLength) {
                $unitPrice = $tbLoosePrice;
                $weightPrice = $weight * $unitPrice;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxWeight;
                $unitPrice = $weightPrice / $weight;
            } else {
                $weightPrice = 0; //error
            }
        }
        //Type I Calculation
        elseif (($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbWidth) {
            ///    if($val_tbCuttingCharges){
            ///        //TypeJ Calculation
            ///        #echo "Found Type J <br>";
            ///        #implement calcualtion J here
            ///        #same as type I but with cutting charges/
            ///    }else{
            //Type I Calculation$
            #echo "Found Type I <br>";
            #echo "\$obLength = $obLength<br>";
            #echo "\$tbMaxLength = $tbMaxLength<br>";
            //if ($val_tbLooseLength) {// mean the data structure of type I can be found looseLength field
            if ($tbLooseLength > 0) {// mean the value in the field  >  0
                if ($obLength < $tbMaxLength) {
                    #echo "\$tbLoosePrice = $tbLoosePrice, \$tbLooseLength = $tbLooseLength <br>";
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $weight;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                    $unitPrice = $weightPrice / $weight;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif ($tbLooseLength == 0) {// mean the value in the field is equal to 0
                // which is the same case in many TYPE I tables the looseLength field value = 0.00
                if ($obLength < $tbMaxLength) {
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $weight * $unitPrice;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = $tbMaxPrice;
                    $weightPrice = $weight * $unitPrice;
                } else {
                    $weightPrice = 0; //error
                }
            }
            //price = (Looseprice / looselength * length) [if length < maxlength]
            //price = maxPrice [if length == maxLength]
            // } elseif (!$val_tbLooseLength) {//
            //!val_tbLooseLength mean the field in the data structure can't be found
            //the data structure do not have this field, which can't be happened
            //in type I table, this concept no need to implement any things.
//                if ($obLength < $tbMaxLength) {
//                    $unitPrice = $tbLoosePrice;
//                    $weightPrice = $weight * $unitPrice;
//                } elseif ($obLength == $tbMaxLength) {
//                    $unitPrice = $tbMaxPrice;
//                    $weightPrice = $weight * $unitPrice;
//                } else {
//                    $weightPrice = 0; //error
//                }
            //}
            ///    }
        } else {
            #echo "No table type has been found <br>";
        }
//        //Type L Calculation
//        elseif (($val_tbW1 && $val_tbW2 && $val_tbMaxLength && $val_tbMaxWeight && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
//            #echo "Found Type L <br>";
//            if ($obLength < $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
//                $weightPrice = $unitPrice * $obLength;
//            } elseif ($obLength == $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
//                $weightPrice = $unitPrice * $obLength;
//            } else {
//                $weightPrice = 0; //cannot find price
//            }
//        } else {
//            #echo "No table type has been found <br>";
//        }

        /**
          if (($tbWidth&&$tbMaxPrice&&$tbMaxLength&&$tbLoosePrice&&$tbLooseLength&&$tbCuttingCharges)==0){
          //If all value is 0, then this is normal process
          }elseif($tbWidth){
          //If there's tbWidth <> 0, then inputWidth must same as tbWidth;
          }
          //if
          elseif($a){

          }
         * */
        $this->setUnitPrice($unitPrice);
        return $weightPrice;
    }

    public function checkMinPrice($weightPrice, $com, $cid) {
        #echo "<br>Line 1470 function checkMinPrice $$$$$$$$$$$$$$$$$$$$$$$$$$$$<br>";
        $materialcode = $this->getMaterialCode();
        $materialtype = $this->getMaterialType();
        #echo "on function checkMinPrice, materialType = $materialtype, weightPrice = $weightPrice<br>";
        switch ($materialtype) {
            case 'ts':
                #echo "found 'ts'<br>";
                if ($weightPrice <= 5.00) {
                    $MinPrice = 5.00;
                } else {
                    $MinPrice = 0.00;
                }
                break;
            case 'aa':
                #echo "found 'aa'<br>";
                if ($materialcode == 'a5052ap' || $materialcode == 'ss303p' || $materialcode == 'yh52p' || $materialcode == 'aa6061t651p') {
                    if ($weightPrice <= 0.5) {
                        $MinPrice = 0.5;
                    } elseif ($unitPrice <= 3.0) {
                        $MinPrice = 5.00;
                    }
                } else {
                    if ($weightPrice <= 3.0) {
                        $MinPrice = 3.00;
                    } else {
                        $MinPrice = 0.00;
                    }
                }
                break;
            case 'ms':
                #echo "found 'ms'<br>";
                if ($weightPrice <= 3.00) {
                    $MinPrice = 3.00;
                } else {
                    $MinPrice = 0.00;
                }
                break;

            default:
                if ($materialcode == 'mlcgphh' || $materialcode == 'mlcgpst') {
                    if (($com == 'PST' && $cid = 5171) || ($com == 'PST' && $cid == 21187) || ($com == 'PST' && $cid == 21188)) {
                        if ($weightPrice < 0.5) {
                            $MinPrice = 0.5;
                        } else {
                            $MinPrice = 0.00;
                        }
                    } else {
                        if ($weightPrice < 3) {
                            $MinPrice = 3.00;
                        } else {
                            $MinPrice = 0.00;
                        }
                    }
                } else {
                    if ($weightPrice < 3) {
                        $MinPrice = 3.00;
                    } else {
                        $MinPrice = 0.00;
                    }
                }
                break;
        }
        return $MinPrice;
    }

    public function getCrossSectionArea() {
        return $this->CrossSectionArea;
    }

    public function setCrossSectionArea($input) {
        $this->CrossSectionArea = $input;
    }

    public function getUnitPrice() {
        return $this->unitPrice;
    }

    public function setUnitPrice($input) {
        $this->unitPrice = $input;
    }

    public function getShape_Code() {
        return $this->Shape_Code;
    }

    public function setShape_Code($input) {
        $this->Shape_Code = $input;
    }

    public function getT() {
        return $this->T;
    }

    public function setT($input) {
        $this->T = $input;
    }

    public function getW1() {
        return $this->W1;
    }

    public function setW1($input) {
        $this->W1 = $input;
    }

    public function getW2() {
        return $this->W2;
    }

    public function setW2($input) {
        $this->W2 = $input;
    }

    public function getL() {
        return $this->L;
    }

    public function setL($input) {
        $this->L = $input;
    }

    protected function calVolume() {
        $T = floatval($this->T);
        $W1 = floatval($this->W1);
        $W2 = floatval($this->W2);
        $L = floatval($this->L);

        $oSide = $W1 * $W2;
        $iSide = ($W1 - $T) * ($W2 - $T);

        $area = $oSide - $iSide;
        $vol = $area * $L;

        ##echo "in line 267, \$vol = $vol <br>";
        $this->setVolume($vol);

        return $vol;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function calWeight() {
        $density = $this->density;
        $volume = $this->getVolume();

        #    #echo "<br>=================================================<br>";
        #    #echo "\$density = $density ,  \$volume = $volume <br> ";

        $weight = $volume * $density;
        $this->setWeight($weight);

        return $weight;
    }

}

abstract class TUBE extends WORKPCS2 {

    protected $category;

    public function __construct() {

        $this->category = 'tube';
    }

}

Class HP extends TUBE {

    protected $Shape_Code;
    protected $ID; //Inner Diameter
    protected $OD; //Outer Diameter
    protected $L; //Length
    protected $materialcode;
    protected $dimension;
    protected $CrossSectionArea;

    //protected $mydimension;

    public function __construct($materialcode, $ID, $OD, $L) {

        $this->Shape_Code = "HP"; // shape code O, circular type O cross section area; Shaft
        $this->ID = $ID;
        $this->OD = $OD;
        $this->L = $L;
        $this->materialcode = $materialcode;
        $dimension = $this->formDimension();
        $this->dimension = $dimension;
        // Explanation for CrossSectionArea :
        // https://www.evernote.com/shard/s436/sh/168b3c51-93ef-134c-ed65-967caa5347cf/b67112c794e8b5af820f63494b754b25
        $this->CrossSectionArea = ( pi() * (pow($OD, 2) - pow($ID, 2)) ) / 4;
        $this->mainProcess();
    }

    public function mainProcess() {
        $materialcode = $this->getMaterialCode();
        $L = $this->getL();
        $ID = $this->getID();
        $OD = $this->getOD();
        $Shape_Code = $this->getShape_Code();
        $isShapeCodeMatch = $this->isShapeCodeMatch($materialcode);
        if ($isShapeCodeMatch !== 'yes') {
            #echo "Material with code : $materialcode have different Shape_Code than current process";
        } else {
            //Shape_Code matches, go to next step
            $materialType = $this->getMaterialType();
            $density = $this->grabDensitybyMType($materialType);
            $this->setDensity($density);
            $volume = $this->calVolume();
            $this->setVolume($volume);
            $weight = $this->calWeight($materialcode);
            #echo "\$volume = $volume<br>";
            #echo "\$weight = $weight<br>";
            $this->setWeight($weight);
        }
    }

    public function formDimension() {

        $ID = $this->ID;
        $OD = $this->OD;
        $L = $this->L;
        $Dimension = array('ID' => $ID, 'OD' => $OD, 'L' => $L);
        return $Dimension;
    }

    public function isShapeCodeMatch($materialcode) {

        $sql = "SELECT Shape_Code FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
        // print_r($result);
        $myShape_Code = $result['Shape_Code'];
        $Shape_Code = $this->Shape_Code;
//        $this->setShape_Code($Shape_Code);

        if ($myShape_Code == $Shape_Code) {
            $answer = 'yes';
        } else {
            $answer = 'no';
        }

        return $answer;
    }

    public function fetchPrice($mat, $cid, $com, $iWeight, $iID, $iOD, $iL) { //needs cid and company code
        $ID = floatval($iID);     //Thickness Input Value
        $OD = floatval($iOD);   //W1 Input Value
        $L = floatval($iL);     //Length Input Value
        $weight = $iWeight;
        $materialcode = $this->materialcode; //get material code
        $specialPriceTbl = $materialcode . "_" . strtolower(trim($com)) . "_" . $cid; //special price table
        //get the Thickness
        $T = ($OD - $ID) / 2;
        //Old Data Table
        $priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}'";
        //New Data Table, Still Commented
        #$priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE PHI = '{$OD}'";

        $priceSQL = new SQL($priceQuery0);

        $result = $priceSQL->getResultOneRowArray();
        if (!empty($result)) {
            #echo "found table {$specialPriceTbl}<br>";
            #echo "<br>*************************start of calPrice************************************<br>";
            $weightPrice = $this->calPrice($result, $weight, $L);
        } else {
            #echo "cannot find table {$specialPriceTbl}, Using general table.<br>";
            //old Data Table
            $priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T}";
            //new Data Table; Still Commented
            #$priceQuery1 = "SELECT * FROM {$materialcode} WHERE PHI = {$OD}";
            unset($priceSQL);
            unset($result);
            $priceSQL = new SQL($priceQuery1);
            $result = $priceSQL->getResultOneRowArray();
            if (!empty($result)) {
                #echo "found table {$materialcode}";
                #echo "<br>*************************start of calPrice************************************<br>";
                $weightPrice = $this->calPrice($result, $weight, $L);
            } else {
                #echo "<span style='background-color:red;color:white'>Line 288/ Cannot find price using {$materialcode} and thickness = {$T}<br>";
                #echo "Check Material Price has been created or not for {$materialcode}</span><br>";
            }
        }

        #echo "\$weightPrice = $weightPrice<br>";
        return $weightPrice;
    }

    public function calPrice($arr_matprice, $weight, $L) {
        $objMatPrice = new MaterialPrice($arr_matprice);
        $obLength = $L;
        #echo "<br>***************************HP**********************************<br>";
        #echo "in function calPrice of Class HP <br> ";
        //fetch values from OOP
        $tbWidth = $objMatPrice->getWidth();
        $tbW1 = $objMatPrice->getW1();
        $tbW2 = $objMatPrice->getW2();
        $tbPrice = $objMatPrice->getPrice();
        $tbMaxWeight = $objMatPrice->getMaxweight();
        $tbMaxLength = $objMatPrice->getMaxlength();
        $tbMaxPrice = $objMatPrice->getMaxprice();
        $tbLooseLength = $objMatPrice->getLooselength();
        $tbLoosePrice = $objMatPrice->getLooseprice();
        $tbCuttingCharges = $objMatPrice->getCuttingcharges();
        /* #echo "<pre><strong>";
          #echo "tbWidth = $tbWidth;";
          #echo "tbW1 = $tbW1;";
          #echo "tbW2 = $tbW2;";
          #echo "tbPrice = $tbPrice;";
          #echo "tbMaxWeight = $tbMaxWeight";
          #echo "tbMaxLength = $tbMaxLength;";
          #echo "tbMaxPrice = $tbMaxPrice;";
          #echo "tbLooseLength = $tbLooseLength;";
          #echo "tbLoosePrice = $tbLoosePrice;";
          #echo "tbCuttingCharges = $tbCuttingCharges;";
          #echo "</strong></pre>";
          #echo "\$obLength = $obLength<br>"; */
        //end fetch values from OOP
        //check NULL or not
        $val_tbWidth = checkIsSet($tbWidth);
        $val_tbW1 = checkIsSet($tbW1);
        $val_tbW2 = checkIsSet($tbW2);
        $val_tbPrice = checkIsSet($tbPrice);
        $val_tbMaxWeight = checkIsSet($tbMaxWeight);
        $val_tbMaxLength = checkIsSet($tbMaxLength);
        $val_tbMaxPrice = checkIsSet($tbMaxPrice);
        $val_tbLooseLength = checkIsSet($tbLooseLength);
        $val_tbLoosePrice = checkIsSet($tbLoosePrice);
        $val_tbCuttingCharges = checkIsSet($tbCuttingCharges);
        /* #echo "<pre><strong>";
          #echo "val_tbWidth = ";
          var_dump($val_tbWidth);
          #echo "val_tbW1 = ";
          var_dump($val_tbW1);
          #echo "val_tbW2 = ";
          var_dump($val_tbW2);
          #echo "val_tbPrice = ";
          var_dump($val_tbPrice);
          #echo "val_tbMaxWeight = ";
          var_dump($val_tbMaxWeight);
          #echo "val_tbMaxLength = ";
          var_dump($val_tbMaxLength);
          #echo "val_tbMaxPrice = ";
          var_dump($val_tbMaxPrice);
          #echo "val_tbLooseLength = ";
          var_dump($val_tbLooseLength);
          #echo "val_tbLoosePrice = ";
          var_dump($val_tbLoosePrice);
          #echo "val_tbCuttingCharges = ";
          var_dump($val_tbCuttingCharges);
          #echo "</strong></pre>"; */
        //end check NULL or not
        //Variable Explanation
        //$weight = unit weight in oop
        //This is Type A Calculation

        if (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLooseLength && $val_tbLoosePrice)) {
            #echo "Found Type A <br>";
            #implement calculation A here;
            if ($obLength < $tbMaxLength) {
                $unitPrice = ($tbLoosePrice / $tbLooseLength);
                $weightPrice = $unitPrice * $obLength;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxPrice;
            } else {
                $weightPrice = 0; //cannot find price
            }
        }
        //Type B Calculation
        elseif (($val_tbW1 && $val_tbW2) && ($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice)) {
            #echo "Found Type B <br>";
            if ($val_tbLooseLength) {
                #implement calculation B here
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif (!$val_tbLooseLength && $val_tbMaxWeight) {
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
                    $weightPrice = $unitPrice * $obLength;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            }
        }
        //Type C Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ///    if($tbCuttingCharges){
            ///        //Type K Calculation
            ///        #echo "Found Type K <br>";
            ///        #implement calculation K here
            ///    }else{
            //Type C Calculation
            #echo "Line 2189, Found Type C <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
            ///    }
        }
        //Type D Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice) && ($val_tbLoosePrice && $val_tbLooseLength)) {
            #echo "Found Type D <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type E Calculation
        elseif ($val_tbWidth && (!$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            #echo "Found Type E <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
        }
        //Type F Calculation
        elseif (($val_tbWidth && $val_tbLooseLength && $val_tbLoosePrice) && (!$val_tbMaxPrice && !$val_tbMaxLength)) {
            #echo "Found Type F <br>";
        }
        //Type G Calculation
        elseif (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
            #echo "Found Type G <br>";
            if ($obLength < $tbMaxLength) {
                $unitPrice = $tbLoosePrice;
                $weightPrice = $weight * $unitPrice;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxWeight;
            } else {
                $weightPrice = 0; //error
            }
        }
        //Type I Calculation
        elseif (($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbWidth) {
            ///    if($val_tbCuttingCharges){
            ///        //TypeJ Calculation
            ///        #echo "Found Type J <br>";
            ///        #implement calcualtion J here
            ///        #same as type I but with cutting charges/
            ///    }else{
            //Type I Calculation$
            #echo "Found Type I <br>";
            #echo "\$obLength = $obLength<br>";
            #echo "\$tbMaxLength = $tbMaxLength<br>";
            //if ($val_tbLooseLength) {// mean the data structure of type I can be found looseLength field
            if ($tbLooseLength > 0) {// mean the value in the field  >  0
                if ($obLength < $tbMaxLength) {
                    #echo "\$tbLoosePrice = $tbLoosePrice, \$tbLooseLength = $tbLooseLength <br>";
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $weight;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif ($tbLooseLength == 0) {// mean the value in the field is equal to 0
                // which is the same case in many TYPE I tables the looseLength field value = 0.00
                if ($obLength < $tbMaxLength) {
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $weight * $unitPrice;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = $tbMaxPrice;
                    $weightPrice = $weight * $unitPrice;
                } else {
                    $weightPrice = 0; //error
                }
            }
            //price = (Looseprice / looselength * length) [if length < maxlength]
            //price = maxPrice [if length == maxLength]
            // } elseif (!$val_tbLooseLength) {//
            //!val_tbLooseLength mean the field in the data structure can't be found
            //the data structure do not have this field, which can't be happened
            //in type I table, this concept no need to implement any things.
//                if ($obLength < $tbMaxLength) {
//                    $unitPrice = $tbLoosePrice;
//                    $weightPrice = $weight * $unitPrice;
//                } elseif ($obLength == $tbMaxLength) {
//                    $unitPrice = $tbMaxPrice;
//                    $weightPrice = $weight * $unitPrice;
//                } else {
//                    $weightPrice = 0; //error
//                }
            //}
            ///    }
        } else {
            #echo "No table type has been found <br>";
        }
//        //Type L Calculation
//        elseif (($val_tbW1 && $val_tbW2 && $val_tbMaxLength && $val_tbMaxWeight && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
//            #echo "Found Type L <br>";
//            if ($obLength < $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
//                $weightPrice = $unitPrice * $obLength;
//            } elseif ($obLength == $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
//                $weightPrice = $unitPrice * $obLength;
//            } else {
//                $weightPrice = 0; //cannot find price
//            }
//        } else {
//            #echo "No table type has been found <br>";
//        }

        /**
          if (($tbWidth&&$tbMaxPrice&&$tbMaxLength&&$tbLoosePrice&&$tbLooseLength&&$tbCuttingCharges)==0){
          //If all value is 0, then this is normal process
          }elseif($tbWidth){
          //If there's tbWidth <> 0, then inputWidth must same as tbWidth;
          }
          //if
          elseif($a){

          }
         * */
        return $weightPrice;
    }

    /**
      public function extractDimension(){
      $dimension = $this->myDimension;

      $ID = $dimension['0'];
      $OD = $dimension['1'];
      $L = $dimension['2'];

      $this->ID = $ID;
      $this->OD = $OD;
      $this->L = $L;

      $newDimension = array('ID' => $ID, 'OD' => $OD, 'L' => $L);

      return $newDimension;
      }
     * */
    public function getCrossSectionArea() {
        return $this->CrossSectionArea;
    }

    public function setCrossSectionArea($input) {
        $this->CrossSectionArea = $input;
    }

    public function getShape_Code() {
        return $this->Shape_Code;
    }

    public function setShape_Code($input) {
        $this->Shape_Code = $input;
    }

    public function getID() {
        return $this->ID;
    }

    public function setID($input) {
        $this->ID = $input;
    }

    public function getOD() {
        return $this->OD;
    }

    public function setOD($input) {
        $this->OD = $input;
    }

    public function getL() {
        return $this->L;
    }

    public function setL($input) {
        $this->L = $input;
    }

    protected function calVolume() {

        $ID = floatval($this->ID);
        $OD = floatval($this->OD);
        $L = floatval($this->L);

        $oRadius = $OD / 2;
        $iRadius = $ID / 2;

        $oArea = pi() * pow($oRadius, 2);
        $iArea = pi() * pow($iRadius, 2);

        $vol = ($oArea - $iArea) * $L;

        ##echo "in line 267, \$vol = $vol <br>";
        $this->setVolume($vol);

        return $vol;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function calWeight() {
        $density = $this->density;
        $volume = $this->getVolume();

        #    #echo "<br>=================================================<br>";
        #    #echo "\$density = $density ,  \$volume = $volume <br> ";

        $weight = $volume * $density;
        $this->setWeight($weight);

        return $weight;
    }

}

Class HS extends TUBE {

    protected $Shape_Code;
    protected $T; //Thickness
    protected $W1; //1st Inner Width
    protected $W2; //2nd Inner Width
    protected $L; //Length
    protected $materialcode;
    protected $dimension;
    protected $CrossSectionArea;

    //protected $myDimension;

    public function __construct($materialcode, $T, $W1, $W2, $L) {
        parent::__construct();
        $this->Shape_Code = "HS"; // shape code O, circular type O cross section area; Shaft
        $this->T = $T;
        $this->W1 = $W1;
        $this->W2 = $W2;
        $this->L = $L;
        $this->materialcode = $materialcode;
        $dimension = $this->formDimension();
        $this->dimension = $dimension;
        // Explanation for CrossSectionArea :
        // https://www.evernote.com/shard/s436/sh/168b3c51-93ef-134c-ed65-967caa5347cf/b67112c794e8b5af820f63494b754b25
        $this->CrossSectionArea = 2 * $T * ($W1 + $W2 - (2 * $T));
        $this->mainProcess();
    }

    public function mainProcess() {
        $materialcode = $this->getMaterialCode();
        $T = $this->getT();
        $W1 = $this->getW1();
        $W2 = $this->getW2();
        $L = $this->getL();
        $Shape_Code = $this->getShape_Code();
        $isShapeCodeMatch = $this->isShapeCodeMatch($materialcode);
        if ($isShapeCodeMatch !== 'yes') {
            #echo "Material with code : $materialcode have different Shape_Code than current process";
        } else {
            //Shape_Code matches, go to next step
            $materialType = $this->getMaterialType();
            $density = $this->grabDensitybyMType($materialType);
            $this->setDensity($density);
            $volume = $this->calVolume();
            $this->setVolume($volume);
            $weight = $this->calWeight($materialcode);
            #echo "\$volume = $volume<br>";
            #echo "\$weight = $weight<br>";
            $this->setWeight($weight);
        }
    }

    public function formDimension() {
        $T = $this->T;
        $W1 = $this->W1;
        $W2 = $this->W2;
        $L = $this->L;
        $Dimension = array('T' => $T, 'W1' => $W1, 'W2' => $W2, 'L' => $L);
        return $Dimension;
    }

    public function extractDimension() {
        $dimension = $this->myDimension;

        $T = $dimension['0'];
        $W1 = $dimension['1'];
        $W2 = $dimension['2'];
        $L = $dimension['3'];

        $this->T = $T;
        $this->W1 = $W1;
        $this->W2 = $W2;
        $this->L = $L;

        $newDimension = array('T' => $T, 'W1' => $W1, 'W2' => $W2, 'L' => $L);

        return $newDimension;
    }

    public function isShapeCodeMatch($materialcode) {

        $sql = "SELECT Shape_Code FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
        // print_r($result);
        $myShape_Code = $result['Shape_Code'];
        $Shape_Code = $this->Shape_Code;
//        $this->setShape_Code($Shape_Code);

        if ($myShape_Code == $Shape_Code) {
            $answer = 'yes';
        } else {
            $answer = 'no';
        }

        return $answer;
    }

    public function fetchPrice($mat, $cid, $com, $iWeight, $iT, $iW1, $iW2, $iL) { //needs cid and company code
        $T = floatval($iT);
        $W1 = floatval($iW1);     //Thickness Input Value
        $W2 = floatval($iW2);   //W1 Input Value
        $L = floatval($iL);     //Length Input Value
        $weight = $iWeight;
        $materialcode = $this->materialcode; //get material code
        $specialPriceTbl = $materialcode . "_" . strtolower(trim($com)) . "_" . $cid; //special price table
        $allWidth = $iW1 . " x " . $iW2;

        //Old Data Table
        $priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}' AND width = '{$allWidth}'";
        //New Data Table, Still Commented
        #$priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}' AND W1 = $W1 AND W2 = $W2";

        $priceSQL = new SQL($priceQuery0);

        $result = $priceSQL->getResultOneRowArray();
        if (!empty($result)) {
            #echo "found table {$specialPriceTbl}<br>";
            #echo "<br>*************************start of calPrice************************************<br>";
            $weightPrice = $this->calPrice($result, $weight, $L);
        } else {
            #echo "cannot find table {$specialPriceTbl}, Using general table.<br>";
            //old Data Table
            $priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T} AND width = '{$allWidth}'";
            //new Data Table; Still Commented
            #$priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T}";
            unset($priceSQL);
            unset($result);
            $priceSQL = new SQL($priceQuery1);
            $result = $priceSQL->getResultOneRowArray();
            if (!empty($result)) {
                #echo "found table {$materialcode}";
                #echo "<br>*************************start of calPrice************************************<br>";
                $weightPrice = $this->calPrice($result, $weight, $L);
            } else {
                #echo "<span style='background-color:red;color:white'>Line 288/ Cannot find price using {$materialcode} and thickness = {$T}<br>";
                #echo "Check Material Price has been created or not for {$materialcode}</span><br>";
            }
        }

        #echo "\$weightPrice = $weightPrice<br>";
        return $weightPrice;
    }

    public function calPrice($arr_matprice, $weight, $L) {
        $objMatPrice = new MaterialPrice($arr_matprice);
        $obLength = $L;
        #echo "<br>*****************************HS********************************<br>";
        #echo "in function calPrice of Class HS <br> ";
        //fetch values from OOP
        $tbWidth = $objMatPrice->getWidth();
        $tbW1 = $objMatPrice->getW1();
        $tbW2 = $objMatPrice->getW2();
        $tbPrice = $objMatPrice->getPrice();
        $tbMaxWeight = $objMatPrice->getMaxweight();
        $tbMaxLength = $objMatPrice->getMaxlength();
        $tbMaxPrice = $objMatPrice->getMaxprice();
        $tbLooseLength = $objMatPrice->getLooselength();
        $tbLoosePrice = $objMatPrice->getLooseprice();
        $tbCuttingCharges = $objMatPrice->getCuttingcharges();
        /* #echo "<pre><strong>";
          #echo "tbWidth = $tbWidth;";
          #echo "tbW1 = $tbW1;";
          #echo "tbW2 = $tbW2;";
          #echo "tbPrice = $tbPrice;";
          #echo "tbMaxWeight = $tbMaxWeight";
          #echo "tbMaxLength = $tbMaxLength;";
          #echo "tbMaxPrice = $tbMaxPrice;";
          #echo "tbLooseLength = $tbLooseLength;";
          #echo "tbLoosePrice = $tbLoosePrice;";
          #echo "tbCuttingCharges = $tbCuttingCharges;";
          #echo "</strong></pre>";
          #echo "\$obLength = $obLength<br>"; */
        //end fetch values from OOP
        //check NULL or not
        $val_tbWidth = checkIsSet($tbWidth);
        $val_tbW1 = checkIsSet($tbW1);
        $val_tbW2 = checkIsSet($tbW2);
        $val_tbPrice = checkIsSet($tbPrice);
        $val_tbMaxWeight = checkIsSet($tbMaxWeight);
        $val_tbMaxLength = checkIsSet($tbMaxLength);
        $val_tbMaxPrice = checkIsSet($tbMaxPrice);
        $val_tbLooseLength = checkIsSet($tbLooseLength);
        $val_tbLoosePrice = checkIsSet($tbLoosePrice);
        $val_tbCuttingCharges = checkIsSet($tbCuttingCharges);
        /*   #echo "<pre><strong>";
          #echo "val_tbWidth = ";
          var_dump($val_tbWidth);
          #echo "val_tbW1 = ";
          var_dump($val_tbW1);
          #echo "val_tbW2 = ";
          var_dump($val_tbW2);
          #echo "val_tbPrice = ";
          var_dump($val_tbPrice);
          #echo "val_tbMaxWeight = ";
          var_dump($val_tbMaxWeight);
          #echo "val_tbMaxLength = ";
          var_dump($val_tbMaxLength);
          #echo "val_tbMaxPrice = ";
          var_dump($val_tbMaxPrice);
          #echo "val_tbLooseLength = ";
          var_dump($val_tbLooseLength);
          #echo "val_tbLoosePrice = ";
          var_dump($val_tbLoosePrice);
          #echo "val_tbCuttingCharges = ";
          var_dump($val_tbCuttingCharges);
          #echo "</strong></pre>"; */
        //end check NULL or not
        //Variable Explanation
        //$weight = unit weight in oop
        //This is Type A Calculation

        if (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLooseLength && $val_tbLoosePrice)) {
            #echo "Found Type A <br>";
            #implement calculation A here;
            if ($obLength < $tbMaxLength) {
                $unitPrice = ($tbLoosePrice / $tbLooseLength);
                $weightPrice = $unitPrice * $obLength;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxPrice;
            } else {
                $weightPrice = 0; //cannot find price
            }
        }
        //Type B Calculation
        elseif (($val_tbW1 && $val_tbW2) && ($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice)) {
            #echo "Found Type B <br>";
            if ($val_tbLooseLength) {
                #implement calculation B here
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif (!$val_tbLooseLength && $val_tbMaxWeight) {
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
                    $weightPrice = $unitPrice * $obLength;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            }
        }
        //Type C Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ///    if($tbCuttingCharges){
            ///        //Type K Calculation
            ///        #echo "Found Type K <br>";
            ///        #implement calculation K here
            ///    }else{
            //Type C Calculation
            #echo "Line 2658, Found Type C <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
            ///    }
        }
        //Type D Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice) && ($val_tbLoosePrice && $val_tbLooseLength)) {
            #echo "Found Type D <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type E Calculation
        elseif ($val_tbWidth && (!$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            #echo "Found Type E <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
        }
        //Type F Calculation
        elseif (($val_tbWidth && $val_tbLooseLength && $val_tbLoosePrice) && (!$val_tbMaxPrice && !$val_tbMaxLength)) {
            #echo "Found Type F <br>";
        }
        //Type G Calculation
        elseif (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
            #echo "Found Type G <br>";
            if ($obLength < $tbMaxLength) {
                $unitPrice = $tbLoosePrice;
                $weightPrice = $weight * $unitPrice;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxWeight;
            } else {
                $weightPrice = 0; //error
            }
        }
        //Type I Calculation
        elseif (($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbWidth) {
            ///    if($val_tbCuttingCharges){
            ///        //TypeJ Calculation
            ///        #echo "Found Type J <br>";
            ///        #implement calcualtion J here
            ///        #same as type I but with cutting charges/
            ///    }else{
            //Type I Calculation$
            #echo "Found Type I <br>";
            #echo "\$obLength = $obLength<br>";
            #echo "\$tbMaxLength = $tbMaxLength<br>";
            //if ($val_tbLooseLength) {// mean the data structure of type I can be found looseLength field
            if ($tbLooseLength > 0) {// mean the value in the field  >  0
                if ($obLength < $tbMaxLength) {
                    #echo "\$tbLoosePrice = $tbLoosePrice, \$tbLooseLength = $tbLooseLength <br>";
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $weight;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif ($tbLooseLength == 0) {// mean the value in the field is equal to 0
                // which is the same case in many TYPE I tables the looseLength field value = 0.00
                if ($obLength < $tbMaxLength) {
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $weight * $unitPrice;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = $tbMaxPrice;
                    $weightPrice = $weight * $unitPrice;
                } else {
                    $weightPrice = 0; //error
                }
            }
            //price = (Looseprice / looselength * length) [if length < maxlength]
            //price = maxPrice [if length == maxLength]
            // } elseif (!$val_tbLooseLength) {//
            //!val_tbLooseLength mean the field in the data structure can't be found
            //the data structure do not have this field, which can't be happened
            //in type I table, this concept no need to implement any things.
//                if ($obLength < $tbMaxLength) {
//                    $unitPrice = $tbLoosePrice;
//                    $weightPrice = $weight * $unitPrice;
//                } elseif ($obLength == $tbMaxLength) {
//                    $unitPrice = $tbMaxPrice;
//                    $weightPrice = $weight * $unitPrice;
//                } else {
//                    $weightPrice = 0; //error
//                }
            //}
            ///    }
        } else {
            #echo "No table type has been found <br>";
        }
//        //Type L Calculation
//        elseif (($val_tbW1 && $val_tbW2 && $val_tbMaxLength && $val_tbMaxWeight && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
//            #echo "Found Type L <br>";
//            if ($obLength < $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
//                $weightPrice = $unitPrice * $obLength;
//            } elseif ($obLength == $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
//                $weightPrice = $unitPrice * $obLength;
//            } else {
//                $weightPrice = 0; //cannot find price
//            }
//        } else {
//            #echo "No table type has been found <br>";
//        }

        /**
          if (($tbWidth&&$tbMaxPrice&&$tbMaxLength&&$tbLoosePrice&&$tbLooseLength&&$tbCuttingCharges)==0){
          //If all value is 0, then this is normal process
          }elseif($tbWidth){
          //If there's tbWidth <> 0, then inputWidth must same as tbWidth;
          }
          //if
          elseif($a){

          }
         * */
        return $weightPrice;
    }

    public function getCrossSectionArea() {
        return $this->CrossSectionArea;
    }

    public function setCrossSectionArea($input) {
        $this->CrossSectionArea = $input;
    }

    public function getShape_Code() {
        return $this->Shape_Code;
    }

    public function setShape_Code($input) {
        $this->Shape_Code = $input;
    }

    public function getT() {
        return $this->T;
    }

    public function setT($input) {
        $this->T = $input;
    }

    public function getW1() {
        return $this->W1;
    }

    public function setW1($input) {
        $this->W1 = $input;
    }

    public function getW2() {
        return $this->W2;
    }

    public function setW2($input) {
        $this->W2 = $input;
    }

    public function getL() {
        return $this->L;
    }

    public function setL($input) {
        $this->L = $input;
    }

    protected function calVolume() {
        $T = floatval($this->T);
        $W1 = floatval($this->W1);
        $W2 = floatval($this->W2);
        $L = floatval($this->L);

        $sect1 = $W1 - 2 * $T;
        $sect2 = $W2 - 2 * $T;

        $area = ($W1 * $W2) - ($sect1 * $sect2);

        $vol = $area * $L;

        ##echo "in line 267, \$vol = $vol <br>";
        $this->setVolume($vol);

        return $vol;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function calWeight() {
        $density = $this->density;
        $volume = $this->getVolume();

        #    #echo "<br>=================================================<br>";
        #    #echo "\$density = $density ,  \$volume = $volume <br> ";

        $weight = $volume * $density;
        $this->setWeight($weight);

        return $weight;
    }

}

abstract Class PLATE extends WORKPCS2 {

    protected $category;

    public function __construct() {
        parent::__construct();

        $this->category = 'plate';
    }

}

Class PLATEN extends PLATE {

    protected $T; // THICK
    protected $W; //WIDTH
    protected $L; //length
    protected $Shape_Code;
//    protected $myDimension;
    protected $materialcode;
    protected $dimension;
    protected $unitPrice;
    protected $CrossSectionArea;

    public function __construct($materialcode, $T, $W, $L) {
        #echo "<b>Line 2904 , Enter the constructor of PLATEN </b><br>";
//        parent::__construct();
        parent::__construct();
        $this->T = $T;
        $this->W = $W;
        $this->L = $L;
        #echo "on line 3115, \$T = $T, \$W = $W, \$L = $L<br>";
        $dimension = PLATEN::formDimension();
        $this->dimension = $dimension;
        #echo "line 2912, Lit down \$dimension  <br>";
        #print_r($dimension);
        #echo"<br>";
        // $this->myDimension = $dimension;
        //    $this->grabDimension();
        $this->materialcode = $materialcode;
        $Shape_Code = "PLATEN"; // shape code is Plate , normal
        $this->setShape_Code($Shape_Code);
        $this->Shape_Code = "PLATEN"; // shape code is Plate , normal
        #echo "Line 2921, \$this->Shape_Code = $this->Shape_Code <br>";
        $this->CrossSectionArea = $T * $W;
        $this->mainProcess();
        #echo "end constructor Class PLATEN<br>";
    }

    public function mainProcess() {
        #echo "Line 2927, in mainProcess <br>";
        $materialcode = $this->getMaterialCode();
        $T = $this->getT();
        $W = $this->getW();
        $L = $this->getL();
        $Shape_Code = $this->getShape_Code();
        $isShapeCodeMatch = $this->isShapeCodeMatch($materialcode);
        #echo "Line 2933, \$isShapeCodeMatch =  $isShapeCodeMatch =<br>";
        if ($isShapeCodeMatch !== 'yes') {
            #echo "Material with code : $materialcode have different Shape_Code than current process";
        } else {
            //Shape_Code matches, go to next step
            $materialtype = $this->calMaterialType($materialcode);
            $this->setMaterialType($materialtype);
            $density = $this->grabDensityByMaterialcode2($materialcode);
            $this->setDensity($density);
            #echo "Line 2943, \$this->Shape_Code = $this->Shape_Code <br>";
            $volume = $this->calVolume();
            $this->setVolume($volume);
            $weight = $this->calWeight($materialcode);
            #echo "\$volume = $volume<br>";
            #echo "\$weight = $weight<br>";
            $this->setWeight($weight);
        }
        #echo "end mainProcess<br>";
    }

    public function formDimension() {
        $T = $this->T;
        $W = $this->W;
        $L = $this->L;
        $Dimension = array('T' => $T, 'W' => $W, 'L' => $L);
        return $Dimension;
    }

//    public function grabDimension() {
//        $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
//        $T = $dimension['0']; // extracts Thickness
//        $W = $dimension['1']; // extracts Width
//        $L = $dimension['2']; // extracts Length
//
//        $this->T = $T; //set Class attribute $T
//        $this->W = $W; //set Class attribute $W
//        $this->L = $L; ///set Class attribute $L
//        //create new array, with keys
//        $newDimension = array('T' => $T, 'W' => $W, 'L' => $L);
//
//        return $newDimension;
//    }

    /**    public function extractDimension() {
      // for import the dimension = [$T, $W, $L] format
      $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
      $T = $dimension['0']; // extracts Thickness
      $W = $dimension['1']; // extracts Width
      $L = $dimension['2']; // extracts Length

      $this->T = $T; //set Class attribute $T
      $this->W = $W; //set Class attribute $W
      $this->L = $L; ///set Class attribute $L
      //create new array, with keys
      $newDimension = array('T' => $T, 'W' => $W, 'L' => $L);

      return $newDimension;
      }
     * */
    protected function calVolume() {
        #echo "======= cal Volume on PLATEN========<br>";
        $T = floatval($this->T);
        $W = floatval($this->W);
        $L = floatval($this->L);

        #echo "Line 3025 \$T = $T, \$W = $W, \$L = $L <br>";
        #volume = Thick * width * Length
        $vol = $T * $W * $L;
        #echo "in line 267, \$vol = $vol <br>";
        $this->setVolume($vol);

        return $vol;
    }

    public function calVolumeIfPLATEC() {
        $T = floatval($this->T);
        $W = floatval($this->W);
        $L = floatval($this->W);

        #volume = Thick * width * length
        $vol = $T * $W * $L;
        #echo "in line 3040, \$vol = $vol <br>";
        $this->setVolume($vol);
        return $vol;
    }

    public function calVolumeIfPLATECO() {
        $T = floatval($this->T);
        $W = floatval($this->W);
        $L = floatval($this->W);

        #volume = Thick * width * length
        $vol = $T * $W * $L;
        #echo "in line 3040, \$vol = $vol <br>";
        $this->setVolume($vol);
        #echo "======= end cal Volume on PLATEN =======<br>";
        return $vol;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function grabDensityByMaterialcode2($materialcode) {

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        #echo "Line 2994, \$sql = $sql <br>";
        $result = $objSql->getResultOneRowArray();
//        #echo "Line 2996 , List the \$result array<br>";
//        print_r($result);
//        #echo "<br>";
        $materialType = $result['materialtype'];
        $this->setMaterialType($materialType);
//        #echo "Line 3000 , \$materialType = $materialType <br>";
        $density = $this->grabDensitybyMType($materialType);
        #echo "line 3002, \$density = $density <br>";
        $this->setDensity($density);
        return $density;
    }

    public function isShapeCodeMatch($materialcode) {

        $sql = "SELECT Shape_Code FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
        //print_r($result);
        $myShape_Code = $result['Shape_Code'];
        $Shape_Code = $this->Shape_Code;
//        $this->setShape_Code($Shape_Code);

        if ($myShape_Code == $Shape_Code) {
            $answer = 'yes';
        } else {
            $answer = 'no';
        }

        return $answer;
    }

    public function fetchPrice($mat, $cid, $com, $iWeight, $iT, $iW, $iL) { //needs cid and company code
        $T = floatval($iT);     //Thickness Input Value
        $W = floatval($iW);     //Width Input Value
        $L = floatval($iL);     //Length Input Value
        $weight = $iWeight;
        $materialcode = $this->materialcode; //get material code
        $specialPriceTbl = $materialcode . "_" . strtolower(trim($com)) . "_" . $cid; //special price table

        $priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}'";
        $priceSQL = new SQL($priceQuery0);

        $result = $priceSQL->getResultRowArray();
        if (!empty($result)) {
            #echo "found table {$specialPriceTbl}<br>";
            foreach ($result as $arr_price) {
                if (isset($arr_price['width'])) {
                    if ($W == trim($arr_price['width'])) {
                        $weightPrice = $this->calPrice($arr_price, $weight, $L);
                    }
                } else {
                    $weightPrice = $this->calPrice($arr_price, $weight, $L);
                }
            }
        } else {
            #echo "cannot find table {$specialPriceTbl}, Using general table.<br>";
            $priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T}";
            unset($priceSQL);
            unset($result);
            $priceSQL = new SQL($priceQuery1);
            $result = $priceSQL->getResultRowArray();
            if (!empty($result)) {
                #echo "found table {$materialcode}";
                foreach ($result as $arr_price) {
                    if (isset($arr_price['width'])) {
                        if ($W == trim($arr_price['width'])) {
                            $weightPrice = $this->calPrice($arr_price, $weight, $L);
                        }
                    } else {
                        $weightPrice = $this->calPrice($arr_price, $weight, $L);
                    }
                }
            } else {
                #echo "<span style='background-color: red;color: white'>Line 288/ Cannot find price using {$materialcode} and T = {$T}<br>";
                #echo "Check Material Price has been created or not for {$materialcode}</span><br>";
                $weightPrice = 0;
            }
        }

        #echo "\$weightPrice = $weightPrice<br>";
        return $weightPrice;
    }

    public function calPrice($arr_matprice, $weight, $L) {
        $objMatPrice = new MaterialPrice($arr_matprice);
        $obLength = $L;
        #echo "<br>*****************************PLATEN********************************<br>";
        #echo "in function calPrice of Class PLATEN <br> ";
        //fetch values from OOP
        $tbWidth = $objMatPrice->getWidth();
        $tbW1 = $objMatPrice->getW1();
        $tbW2 = $objMatPrice->getW2();
        $tbPrice = $objMatPrice->getPrice();
        $tbMaxWeight = $objMatPrice->getMaxweight();
        $tbMaxLength = $objMatPrice->getMaxlength();
        $tbMaxPrice = $objMatPrice->getMaxprice();
        $tbLooseLength = $objMatPrice->getLooselength();
        $tbLoosePrice = $objMatPrice->getLooseprice();
        $tbCuttingCharges = $objMatPrice->getCuttingcharges();
        /* #echo "<pre><strong>";
          #echo "tbWidth = $tbWidth;";
          #echo "tbW1 = $tbW1;";
          #echo "tbW2 = $tbW2;";
          #echo "tbPrice = $tbPrice;";
          #echo "tbMaxWeight = $tbMaxWeight";
          #echo "tbMaxLength = $tbMaxLength;";
          #echo "tbMaxPrice = $tbMaxPrice;";
          #echo "tbLooseLength = $tbLooseLength;";
          #echo "tbLoosePrice = $tbLoosePrice;";
          #echo "tbCuttingCharges = $tbCuttingCharges;";
          #echo "</strong></pre>";
          #echo "\$obLength = $obLength<br>"; */
        //end fetch values from OOP
        //check NULL or not
        $val_tbWidth = checkIsSet($tbWidth);
        $val_tbW1 = checkIsSet($tbW1);
        $val_tbW2 = checkIsSet($tbW2);
        $val_tbPrice = checkIsSet($tbPrice);
        $val_tbMaxWeight = checkIsSet($tbMaxWeight);
        $val_tbMaxLength = checkIsSet($tbMaxLength);
        $val_tbMaxPrice = checkIsSet($tbMaxPrice);
        $val_tbLooseLength = checkIsSet($tbLooseLength);
        $val_tbLoosePrice = checkIsSet($tbLoosePrice);
        $val_tbCuttingCharges = checkIsSet($tbCuttingCharges);
        /* #echo "<pre><strong>";
          #echo "val_tbWidth = ";
          var_dump($val_tbWidth);
          #echo "val_tbW1 = ";
          var_dump($val_tbW1);
          #echo "val_tbW2 = ";
          var_dump($val_tbW2);
          #echo "val_tbPrice = ";
          var_dump($val_tbPrice);
          #echo "val_tbMaxWeight = ";
          var_dump($val_tbMaxWeight);
          #echo "val_tbMaxLength = ";
          var_dump($val_tbMaxLength);
          #echo "val_tbMaxPrice = ";
          var_dump($val_tbMaxPrice);
          #echo "val_tbLooseLength = ";
          var_dump($val_tbLooseLength);
          #echo "val_tbLoosePrice = ";
          var_dump($val_tbLoosePrice);
          #echo "val_tbCuttingCharges = ";
          var_dump($val_tbCuttingCharges);
          #echo "</strong></pre>"; */
        //end check NULL or not
        //Variable Explanation
        //$weight = unit weight in oop
        //This is Type A Calculation

        if (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLooseLength && $val_tbLoosePrice)) {
            #echo "Found Type A <br>";
            #implement calculation A here;
            if ($obLength < $tbMaxLength) {
                $unitPrice = ($tbLoosePrice / $tbLooseLength);
                $weightPrice = $unitPrice * $obLength;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxPrice;
                $unitPrice = $weightPrice / $weight;
            } else {
                $weightPrice = 0; //cannot find price
            }
        }
        //Type B Calculation
        elseif (($val_tbW1 && $val_tbW2) && ($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice)) {
            #echo "Found Type B <br>";
            if ($val_tbLooseLength) {
                #implement calculation B here
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                    $unitPrice = $weightPrice / $weight;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif (!$val_tbLooseLength && $val_tbMaxWeight) {
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
                    $weightPrice = $unitPrice * $obLength;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            }
        }
        //Type C Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ///    if($tbCuttingCharges){
            ///        //Type K Calculation
            ///        #echo "Found Type K <br>";
            ///        #implement calculation K here
            ///    }else{
            //Type C Calculation
            #echo "Line 3192, Found Type C <br>";
            $unitPrice = $tbPrice;
            #echo "Line 3189, \$unitPrice =  $unitPrice <br> ";
            $weightPrice = $weight * $unitPrice;
            #echo "Line 3191, \$weight = $weight , \$weightPrice =  $weightPrice <br> ";
            ///    }
        }
        //Type D Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice) && ($val_tbLoosePrice && $val_tbLooseLength)) {
            #echo "Found Type D <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type E Calculation
        elseif ($val_tbWidth && (!$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            #echo "Found Type E <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
        }
        //Type F Calculation
        elseif (($val_tbWidth && $val_tbLooseLength && $val_tbLoosePrice) && (!$val_tbMaxPrice && !$val_tbMaxLength)) {
            #echo "Found Type F <br>";
        }
        //Type G Calculation
        elseif (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
            #echo "Found Type G <br>";
            if ($obLength < $tbMaxLength) {
                $unitPrice = $tbLoosePrice;
                $weightPrice = $weight * $unitPrice;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxWeight;
                $unitPrice = $weightPrice / $weight;
            } else {
                $weightPrice = 0; //error
            }
        }
        //Type I Calculation
        elseif (($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbWidth) {
            ///    if($val_tbCuttingCharges){
            ///        //TypeJ Calculation
            ///        #echo "Found Type J <br>";
            ///        #implement calcualtion J here
            ///        #same as type I but with cutting charges/
            ///    }else{
            //Type I Calculation$
            #echo "Found Type I <br>";
            #echo "\$obLength = $obLength<br>";
            #echo "\$tbMaxLength = $tbMaxLength<br>";
            //if ($val_tbLooseLength) {// mean the data structure of type I can be found looseLength field
            if ($tbLooseLength > 0) {// mean the value in the field  >  0
                if ($obLength < $tbMaxLength) {
                    #echo "\$tbLoosePrice = $tbLoosePrice, \$tbLooseLength = $tbLooseLength <br>";
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $weight;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif ($tbLooseLength == 0) {// mean the value in the field is equal to 0
                // which is the same case in many TYPE I tables the looseLength field value = 0.00
                if ($obLength < $tbMaxLength) {
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $weight * $unitPrice;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = $tbMaxPrice;
                    $weightPrice = $weight * $unitPrice;
                } else {
                    $weightPrice = 0; //error
                }
            }
            //price = (Looseprice / looselength * length) [if length < maxlength]
            //price = maxPrice [if length == maxLength]
            // } elseif (!$val_tbLooseLength) {//
            //!val_tbLooseLength mean the field in the data structure can't be found
            //the data structure do not have this field, which can't be happened
            //in type I table, this concept no need to implement any things.
//                if ($obLength < $tbMaxLength) {
//                    $unitPrice = $tbLoosePrice;
//                    $weightPrice = $weight * $unitPrice;
//                } elseif ($obLength == $tbMaxLength) {
//                    $unitPrice = $tbMaxPrice;
//                    $weightPrice = $weight * $unitPrice;
//                } else {
//                    $weightPrice = 0; //error
//                }
            //}
            ///    }
        } else {
            #echo "No table type has been found <br>";
        }
//        //Type L Calculation
//        elseif (($val_tbW1 && $val_tbW2 && $val_tbMaxLength && $val_tbMaxWeight && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
//            #echo "Found Type L <br>";
//            if ($obLength < $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
//                $weightPrice = $unitPrice * $obLength;
//            } elseif ($obLength == $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
//                $weightPrice = $unitPrice * $obLength;
//            } else {
//                $weightPrice = 0; //cannot find price
//            }
//        } else {
//            #echo "No table type has been found <br>";
//        }

        /**
          if (($tbWidth&&$tbMaxPrice&&$tbMaxLength&&$tbLoosePrice&&$tbLooseLength&&$tbCuttingCharges)==0){
          //If all value is 0, then this is normal process
          }elseif($tbWidth){
          //If there's tbWidth <> 0, then inputWidth must same as tbWidth;
          }
          //if
          elseif($a){

          }
         * */
        if (isset($unitPrice)) {
            $this->setUnitPrice($unitPrice);
        }
        return $weightPrice;
    }

    protected function calWeight($materialcode) {
        #echo "======= cal Weight on PLATEN =======<br>";
        $density = (float) $this->grabDensityByMaterialcode2($materialcode);
        #echo "Line 3391 , \$density = $density <br>";
        #echo "Line 3392 , \$this->Shape_Code = $this->Shape_Code<br>";

        $volume = (float) $this->calVolume();

        #echo "<br>=============Line 3361=====calWeight===============================<br>";
        #echo "\$density = $density ,  \$volume = $volume <br> ";

        $weight = $volume * $density;
        $this->setWeight($weight);
        #echo "======= end Cal Weight on PLATEN =======<br>";
        return $weight;
    }

    public function getCrossSectionArea() {
        return $this->CrossSectionArea;
    }

    public function setCrossSectionArea($input) {
        $this->CrossSectionArea = $input;
    }

    public function getShape_Code() {
        return $this->Shape_Code;
    }

    public function setUnitPrice($input) {
        $this->unitPrice = $input;
    }

    public function getUnitPrice() {
        return $this->unitPrice;
    }

    public function setShape_Code($input) {
        $this->Shape_Code = $input;
    }

    public function getT() {
        return $this->T;
    }

    public function setT($input) {
        $this->T = $input;
    }

    public function getW() {
        return $this->W;
    }

    public function setW($input) {
        $this->W = $input;
    }

    public function getL() {
        return $this->L;
    }

    public function setL($input) {
        $this->L = $input;
    }

}

Class PLATEC extends PLATEN {

    protected $T; // THICK
    protected $DIA; //DIAMETER
    protected $DIAInput;
    protected $LocalShape_Code;
//    protected $myDimension;
    protected $materialcode;
    protected $dimension;
    protected $unitPrice;
    protected $W;
    protected $L;

    public function __construct($materialcode, $T, $W, $L, $DIA) {
        #echo "3611 on constructor of PLATEC<br>";
        $this->T = $T;
        $this->DIA = $DIA;
        //during quotation price process, W = L = DIA+10
        //REMEMBER TO ADD ABOVE VALUE IN INPUT
        parent::__construct($materialcode, $T, $W, $L);
        ## implement cross check with $DIAinpt vs $DIA
        ## if $DIAinpt != $DIA #echo message to alert user
        $dimension = $this->formDimension();
        $this->dimension = $dimension;
        // $this->myDimension = $dimension;
        //    $this->grabDimension();
        $this->materialcode = $materialcode;
        $Shape_Code = "PLATEC"; // shape code is PLATEC
        //$this->setShape_Code($Shape_Code);
        $this->LocalShape_Code = "PLATEC"; // shape code is PlateC , normal
        $this->mainProcess();
//        $this->W = ($DIA + 20);
//        $this->L = $this->W;
        #echo "end Constructor of PLATEC<br>";
    }

    public function mainProcess() {
        $materialcode = $this->getMaterialCode();
        $T = $this->getT();
        $DIA = $this->getDIA();
        #echo "\$T = $T, \$DIA = $DIA <br>";
        parent::setT($T);
        parent::setW($DIA);
        parent::setL($DIA);
        //$Shape_Code = $this->getShape_Code();
        $Shape_Code = parent::getShape_Code();
        #echo "parent Shape_Code = $Shape_Code <br>";
        $isShapeCodeMatch = $this->isShapeCodeMatch($materialcode);
        if ($isShapeCodeMatch !== 'yes') {
            #echo "Material with code : $materialcode have different Shape_Code than current process";
        } else {
            #echo "Line 3435 in PLATEC , CALCULATION of Volume, weight loop<br>";
            //Shape_Code matches, go to next step
            $materialType = $this->getMaterialType();
            $density = $this->grabDensitybyMType($materialType);
            $this->setDensity($density);
            $volume = parent::calVolumeIfPLATECO();
            $this->setVolume($volume);
            $weight = parent::calWeight($materialcode);
            $this->volume = $volume;
            #echo "Line 3470 \$volume = $volume<br>";
            #echo "Line 3471 \$weight = $weight<br>";
            $this->setWeight($weight);
        }
    }

    public function formDimension() {
        $T = $this->T;
        $DIA = $this->DIA;
        $Dimension = array('T' => $T, 'DIA' => $DIA);
        return $Dimension;
    }

    public function setLocalShape_Code($input) {
        $this->LocalShape_Code = $input;
    }

    public function getLocalShape_Code() {
        return $this->LocalShape_Code;
    }

    public function setUnitPrice($input) {
        $this->unitPrice = $input;
    }

    public function getUnitPrice() {
        return $this->unitPrice;
    }

//    public function grabDimension() {
//        $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
//        $T = $dimension['0']; // extracts Thickness
//        $W = $dimension['1']; // extracts Width
//        $L = $dimension['2']; // extracts Length
//
//        $this->T = $T; //set Class attribute $T
//        $this->W = $W; //set Class attribute $W
//        $this->L = $L; ///set Class attribute $L
//        //create new array, with keys
//        $newDimension = array('T' => $T, 'W' => $W, 'L' => $L);
//
//        return $newDimension;
//    }

    /**    public function extractDimension() {
      // for import the dimension = [$T, $W, $L] format
      $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
      $T = $dimension['0']; // extracts Thickness
      $W = $dimension['1']; // extracts Width
      $L = $dimension['2']; // extracts Length

      $this->T = $T; //set Class attribute $T
      $this->W = $W; //set Class attribute $W
      $this->L = $L; ///set Class attribute $L
      //create new array, with keys
      $newDimension = array('T' => $T, 'W' => $W, 'L' => $L);

      return $newDimension;
      }
     * */
    protected function calVolume() {
        ## calculate follow PLATEN size
        #echo "in PLATEC calVolume <br>";
        $T = floatval($this->T);
        $DIA = floatval($this->DIA);

        #volume = area*T
        $radius = $DIA / 2;
        $area = pi() * pow($radius, 2);
        $vol = $area * $T;
        ##echo "in line 267, \$vol = $vol <br>";
        $this->setVolume($vol);

        return $vol;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function isShapeCodeMatch($materialcode) {

        $sql = "SELECT Shape_Code FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
        // print_r($result);
        $myShape_Code = $result['Shape_Code'];
        $Shape_Code = parent::getShape_Code(); // get from parent shape Code, not from local
//        $Shape_Code = $this->Shape_Code;
//        $this->setShape_Code($Shape_Code);

        if ($myShape_Code == $Shape_Code) {
            $answer = 'yes';
        } else {
            $answer = 'no';
        }

        return $answer;
    }

    public function fetchPrice($mat, $cid, $com, $iWeight, $iT, $iW, $iL) { //needs cid and company code
        $T = floatval($iT);     //Thickness Input Value
        $W = floatval($iW);   //W1 Input Value
        $L = floatval($iL);     //Length Input Value
        $weight = $iWeight;
        $materialcode = $this->materialcode; //get material code
        $specialPriceTbl = $materialcode . "_" . strtolower(trim($com)) . "_" . $cid; //special price table
        //Old Data Table
        $priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}'";
        //New Data Table, Still Commented
        #$priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}'";

        $priceSQL = new SQL($priceQuery0);

        $result = $priceSQL->getResultRowArray();
        if (!empty($result)) {
            #echo "found table {$specialPriceTbl}<br>";
            foreach ($result as $arr_price) {
                if (isset($arr_price['width'])) {
                    if ($W == trim($arr_price['width'])) {
                        $weightPrice = $this->calPrice($arr_price, $weight, $L);
                    }
                } else {
                    $weightPrice = $this->calPrice($arr_price, $weight, $L);
                }
            };
        } else {
            #echo "cannot find table {$specialPriceTbl}, Using general table.<br>";
            //old Data Table
            $priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T}";
            //new Data Table; Still Commented
            #$priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T}";
            unset($priceSQL);
            unset($result);
            $priceSQL = new SQL($priceQuery1);
            $result = $priceSQL->getResultRowArray();
            if (!empty($result)) {
                #echo "found table {$materialcode}";
                foreach ($result as $arr_price) {
                    if (isset($arr_price['width'])) {
                        if ($W == trim($arr_price['width'])) {
                            $weightPriceSet = $this->calPrice($arr_price, $weight, $L);
                            $weightPrice = $weightPriceSet['$weightPrice'];
                            $unitPrice = $weightPriceSet['$unitPrice'];
                        }
                    } else {
                        $weightPriceSet = $this->calPrice($arr_price, $weight, $L);
                        $weightPrice = $weightPriceSet['$weightPrice'];
                        $unitPrice = $weightPriceSet['$unitPrice'];
                    }
                }
            } else {
                #echo "<span style='background-color:red;color:white'>Line 288/ Cannot find price using {$materialcode} and thickness = {$T}<br>";
                #echo "Check Material Price has been created or not for {$materialcode}</span><br>";
            }
        }

        #echo "\$weightPrice = $weightPrice<br>";
        return $weightPrice;
    }

    public function calPrice($arr_matprice, $weight, $L) {
        $objMatPrice = new MaterialPrice($arr_matprice);
        $obLength = $L;
        #echo "<br>**************************PLATEC***********************************<br>";
        #echo "in function calPrice of Class PLATEC <br> ";
        //fetch values from OOP
        $tbWidth = $objMatPrice->getWidth();
        $tbW1 = $objMatPrice->getW1();
        $tbW2 = $objMatPrice->getW2();
        $tbPrice = $objMatPrice->getPrice();
        $tbMaxWeight = $objMatPrice->getMaxweight();
        $tbMaxLength = $objMatPrice->getMaxlength();
        $tbMaxPrice = $objMatPrice->getMaxprice();
        $tbLooseLength = $objMatPrice->getLooselength();
        $tbLoosePrice = $objMatPrice->getLooseprice();
        $tbCuttingCharges = $objMatPrice->getCuttingcharges();
        /* #echo "<pre><strong>";
          #echo "tbWidth = $tbWidth;";
          #echo "tbW1 = $tbW1;";
          #echo "tbW2 = $tbW2;";
          #echo "tbPrice = $tbPrice;";
          #echo "tbMaxWeight = $tbMaxWeight";
          #echo "tbMaxLength = $tbMaxLength;";
          #echo "tbMaxPrice = $tbMaxPrice;";
          #echo "tbLooseLength = $tbLooseLength;";
          #echo "tbLoosePrice = $tbLoosePrice;";
          #echo "tbCuttingCharges = $tbCuttingCharges;";
          #echo "</strong></pre>";
          #echo "\$obLength = $obLength<br>"; */
        //end fetch values from OOP
        //check NULL or not
        $val_tbWidth = checkIsSet($tbWidth);
        $val_tbW1 = checkIsSet($tbW1);
        $val_tbW2 = checkIsSet($tbW2);
        $val_tbPrice = checkIsSet($tbPrice);
        $val_tbMaxWeight = checkIsSet($tbMaxWeight);
        $val_tbMaxLength = checkIsSet($tbMaxLength);
        $val_tbMaxPrice = checkIsSet($tbMaxPrice);
        $val_tbLooseLength = checkIsSet($tbLooseLength);
        $val_tbLoosePrice = checkIsSet($tbLoosePrice);
        $val_tbCuttingCharges = checkIsSet($tbCuttingCharges);
        /* #echo "<pre><strong>";
          #echo "val_tbWidth = ";
          var_dump($val_tbWidth);
          #echo "val_tbW1 = ";
          var_dump($val_tbW1);
          #echo "val_tbW2 = ";
          var_dump($val_tbW2);
          #echo "val_tbPrice = ";
          var_dump($val_tbPrice);
          #echo "val_tbMaxWeight = ";
          var_dump($val_tbMaxWeight);
          #echo "val_tbMaxLength = ";
          var_dump($val_tbMaxLength);
          #echo "val_tbMaxPrice = ";
          var_dump($val_tbMaxPrice);
          #echo "val_tbLooseLength = ";
          var_dump($val_tbLooseLength);
          #echo "val_tbLoosePrice = ";
          var_dump($val_tbLoosePrice);
          #echo "val_tbCuttingCharges = ";
          var_dump($val_tbCuttingCharges);
          #echo "</strong></pre>"; */
        //end check NULL or not
        //Variable Explanation
        //$weight = unit weight in oop
        //This is Type A Calculation

        if (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLooseLength && $val_tbLoosePrice)) {
            #echo "Found Type A <br>";
            #implement calculation A here;
            if ($obLength < $tbMaxLength) {
                $unitPrice = ($tbLoosePrice / $tbLooseLength);
                $weightPrice = $unitPrice * $obLength;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxPrice;
            } else {
                $weightPrice = 0; //cannot find price
            }
        }
        //Type B Calculation
        elseif (($val_tbW1 && $val_tbW2) && ($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice)) {
            #echo "Found Type B <br>";
            if ($val_tbLooseLength) {
                #implement calculation B here
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif (!$val_tbLooseLength && $val_tbMaxWeight) {
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
                    $weightPrice = $unitPrice * $obLength;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            }
        }
        //Type C Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ///    if($tbCuttingCharges){
            ///        //Type K Calculation
            ///        #echo "Found Type K <br>";
            ///        #implement calculation K here
            ///    }else{
            //Type C Calculation
            #echo "Line 3702, Found Type C <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
            ///    }
        }
        //Type D Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice) && ($val_tbLoosePrice && $val_tbLooseLength)) {
            #echo "Found Type D <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type E Calculation
        elseif ($val_tbWidth && (!$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            #echo "Found Type E <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
        }
        //Type F Calculation
        elseif (($val_tbWidth && $val_tbLooseLength && $val_tbLoosePrice) && (!$val_tbMaxPrice && !$val_tbMaxLength)) {
            #echo "Found Type F <br>";
        }
        //Type G Calculation
        elseif (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
            #echo "Found Type G <br>";
            if ($obLength < $tbMaxLength) {
                $unitPrice = $tbLoosePrice;
                $weightPrice = $weight * $unitPrice;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxWeight;
            } else {
                $weightPrice = 0; //error
            }
        }
        //Type I Calculation
        elseif (($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbWidth) {
            ///    if($val_tbCuttingCharges){
            ///        //TypeJ Calculation
            ///        #echo "Found Type J <br>";
            ///        #implement calcualtion J here
            ///        #same as type I but with cutting charges/
            ///    }else{
            //Type I Calculation$
            #echo "Found Type I <br>";
            #echo "\$obLength = $obLength<br>";
            #echo "\$tbMaxLength = $tbMaxLength<br>";
            //if ($val_tbLooseLength) {// mean the data structure of type I can be found looseLength field
            if ($tbLooseLength > 0) {// mean the value in the field  >  0
                if ($obLength < $tbMaxLength) {
                    #echo "\$tbLoosePrice = $tbLoosePrice, \$tbLooseLength = $tbLooseLength <br>";
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $weight;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif ($tbLooseLength == 0) {// mean the value in the field is equal to 0
                // which is the same case in many TYPE I tables the looseLength field value = 0.00
                if ($obLength < $tbMaxLength) {
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $weight * $unitPrice;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = $tbMaxPrice;
                    $weightPrice = $weight * $unitPrice;
                } else {
                    $weightPrice = 0; //error
                }
            }
            //price = (Looseprice / looselength * length) [if length < maxlength]
            //price = maxPrice [if length == maxLength]
            // } elseif (!$val_tbLooseLength) {//
            //!val_tbLooseLength mean the field in the data structure can't be found
            //the data structure do not have this field, which can't be happened
            //in type I table, this concept no need to implement any things.
//                if ($obLength < $tbMaxLength) {
//                    $unitPrice = $tbLoosePrice;
//                    $weightPrice = $weight * $unitPrice;
//                } elseif ($obLength == $tbMaxLength) {
//                    $unitPrice = $tbMaxPrice;
//                    $weightPrice = $weight * $unitPrice;
//                } else {
//                    $weightPrice = 0; //error
//                }
            //}
            ///    }
        } else {
            #echo "No table type has been found <br>";
        }

//        //Type L Calculation
//        elseif (($val_tbW1 && $val_tbW2 && $val_tbMaxLength && $val_tbMaxWeight && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
//            #echo "Found Type L <br>";
//            if ($obLength < $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
//                $weightPrice = $unitPrice * $obLength;
//            } elseif ($obLength == $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
//                $weightPrice = $unitPrice * $obLength;
//            } else {
//                $weightPrice = 0; //cannot find price
//            }
//        } else {
//            #echo "No table type has been found <br>";
//        }

        /**
          if (($tbWidth&&$tbMaxPrice&&$tbMaxLength&&$tbLoosePrice&&$tbLooseLength&&$tbCuttingCharges)==0){
          //If all value is 0, then this is normal process
          }elseif($tbWidth){
          //If there's tbWidth <> 0, then inputWidth must same as tbWidth;
          }
          //if
          elseif($a){

          }
         * */
//        $resultArray = array('unitPrice' => $unitPrice, 'weightPrice' => $weightPrice);
        if (isset($unitPrice)) {
            $this->setUnitPrice($unitPrice);
        }
        return $weightPrice;
        //return $resultArray;
    }

    public function calWeight($materialcode) {
        $density = (float) $this->grabDensityByMaterialcode2($materialcode);
        $volume = (float) $this->getVolume();

        #    #echo "<br>=================================================<br>";
        #    #echo "\$density = $density ,  \$volume = $volume <br> ";

        $weight = $volume * $density;
        $this->setWeight($weight);

        return $weight;
    }

    public function getShape_Code() {
        return $this->Shape_Code;
    }

    public function setShape_Code($input) {
        $this->Shape_Code = $input;
    }

    public function getT() {
        return $this->T;
    }

    public function setT($input) {
        $this->T = $input;
    }

    public function getDIA() {
        return $this->DIA;
    }

    public function setDIA($input) {
        $this->DIA = $input;
    }

}

Class PLATECO extends PLATEN {

    protected $T; // THICK
    protected $ID; //Inner Diameter
    protected $DIA; //Outer Diameter
    protected $LocalShape_Code;
//    protected $myDimension;
    protected $materialcode;
    protected $dimension;
    protected $W;
    protected $L;

//  PLATECO($materialcode, $T, $W, $L, $DIA, $ID)
    public function __construct($materialcode, $T, $W, $L, $DIA, $ID) {
        #echo "Line 3942 , enter the constructor of PLATECO <br>";
        parent::__construct($materialcode, $T, $W, $L);
        #echo "Line 3941\$T = $T, \$W = $W , \$L = $W, \$DIA = $DIA, \$ID = $ID";
//        $DIA = substr($DIA, 2);
        #echo "Line 3943 \$DIA = $DIA <br>";
//        $ID = substr($ID, 2);
        #echo "Line 3945 \$ID = $ID <br>";
        $W = preg_replace('/[^0-9.]/', '', $DIA); // remove-non-numeric-characters-except-periods-from-a-string
        $L = preg_replace('/[^0-9.]/', '', $ID);
        $this->T = $T;
        $this->ID = $ID;
        $this->DIA = $DIA;
        $this->setW($W);
        $this->setL($L);
        $dimension = $this->formDimension();
        $this->dimension = $dimension;
        // $this->myDimension = $dimension;
        //    $this->grabDimension();
        $this->materialcode = $materialcode;
        $Shape_Code = "PLATECO"; // shape code is Plate , normal
        $this->setLocalShape_Code($Shape_Code);
        $this->LocalShape_Code = $Shape_Code; // shape code is PlateCO
        $this->mainProcess();

        #$this->W = ($DIA + 20);
        #$this->L = $this->W;
    }

    public function mainProcess() {
        $materialcode = $this->getMaterialCode();
        $T = $this->getT();
        $DIA = $this->DIA;
        $ID = $this->ID;
//        $ID = parent::getL();
//        $DIA = parent::getW();
//        $ID = substr($ID, 2);
        #echo "\$ID = $ID <br>";
//        $DIA = substr($DIA, 2);
        #echo "\$DIA = $DIA <br>";
        parent::setW($DIA);
        parent::setL($DIA);
        $W = $DIA;
        $L = $DIA;

        #echo "Line 4264 , in mainprocess of PLATEC, \$W = $W, \$L = $L<br>";
//        $Shape_Code = $this->getShape_Code();
        $Shape_Code = parent::getShape_Code();
        $isShapeCodeMatch = $this->isShapeCodeMatch($materialcode);
        if ($isShapeCodeMatch !== 'yes') {
            #echo "Material with code : $materialcode have different Shape_Code than current process";
        } else {
            #echo "Line 3986 in PLATECO , <b>CALCULATION of Volume, weight loop</b><br>";
            //Shape_Code matches, go to next step
            $materialType = $this->getMaterialType();
            $density = $this->grabDensitybyMType($materialType);
            $this->setDensity($density);
            //$volume = parent::calVolume();
            $volume = $this->calVolume();

            #$volume = $this->calVolume();
            $this->setVolume($volume);
            $weight = parent::calWeight($materialcode);
            #$weight = $this->calWeight();
            #echo "\$volume = $volume<br>";
            #echo "\$weight = $weight<br>";
            $this->setWeight($weight);
        }
    }

    public function setLocalShape_Code($input) {
        $this->LocalShape_Code = $input;
    }

    public function getLocalShape_Code() {
        return $this->LocalShape_Code;
    }

    public function formDimension() {
        $T = $this->T;
        $ID = $this->ID;
        $DIA = $this->DIA;
        $Dimension = array('T' => $T, 'ID' => $ID, 'DIA' => $DIA);
        return $Dimension;
    }

//    public function grabDimension() {
//        $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
//        $T = $dimension['0']; // extracts Thickness
//        $W = $dimension['1']; // extracts Width
//        $L = $dimension['2']; // extracts Length
//
//        $this->T = $T; //set Class attribute $T
//        $this->W = $W; //set Class attribute $W
//        $this->L = $L; ///set Class attribute $L
//        //create new array, with keys
//        $newDimension = array('T' => $T, 'W' => $W, 'L' => $L);
//
//        return $newDimension;
//    }

    /**    public function extractDimension() {
      // for import the dimension = [$T, $W, $L] format
      $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
      $T = $dimension['0']; // extracts Thickness
      $W = $dimension['1']; // extracts Width
      $L = $dimension['2']; // extracts Length

      $this->T = $T; //set Class attribute $T
      $this->W = $W; //set Class attribute $W
      $this->L = $L; ///set Class attribute $L
      //create new array, with keys
      $newDimension = array('T' => $T, 'W' => $W, 'L' => $L);

      return $newDimension;
      }
     * */
    protected function calVolume() {

        $T = floatval($this->T);
        $ID = floatval($this->ID);
        $DIA = floatval($this->DIA);

        #volume = area * T
//        $iRadius = $ID / 2;
//        $oRadius = $DIA / 2;
//        $area = (pi() * pow($oRadius, 2)) - (pi() * pow($iRadius, 2));
        $vol = ($DIA + 5) * ($DIA + 5) * $T;
        #echo "in line 4349, PLATECO \$vol = $vol <br>";
        $this->setVolume($vol);

        return $vol;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function isShapeCodeMatch($materialcode) {

        $sql = "SELECT Shape_Code FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
        // print_r($result);
        $myShape_Code = $result['Shape_Code'];
        $Shape_Code = $this->Shape_Code;
//        $this->setShape_Code($Shape_Code);

        if ($myShape_Code == $Shape_Code) {
            $answer = 'yes';
        } else {
            $answer = 'no';
        }

        return $answer;
    }

    public function fetchPrice($mat, $cid, $com, $iWeight, $iT, $iW, $iL) { //needs cid and company code
        $T = floatval($iT);     //Thickness Input Value
        $W = floatval($iW);   //W1 Input Value
        $L = floatval($iL);     //Length Input Value
        $weight = $iWeight;
        $materialcode = $this->materialcode; //get material code
        $specialPriceTbl = $materialcode . "_" . strtolower(trim($com)) . "_" . $cid; //special price table
        //Old Data Table
        $priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}'";
        //New Data Table, Still Commented
        #$priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}'";

        $priceSQL = new SQL($priceQuery0);

        $result = $priceSQL->getResultRowArray();
        if (!empty($result)) {
            #echo "found table {$specialPriceTbl}<br>";
            foreach ($result as $arr_price) {
                if (isset($arr_price['width'])) {
                    if ($W == trim($arr_price['width'])) {
                        $weightPrice = $this->calPrice($arr_price, $weight, $L);
                    }
                } else {
                    $weightPrice = $this->calPrice($arr_price, $weight, $L);
                }
            }
        } else {
            #echo "cannot find table {$specialPriceTbl}, Using general table.<br>";
            //old Data Table
            $priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T}";
            //new Data Table; Still Commented
            #$priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T}";
            unset($priceSQL);
            unset($result);
            $priceSQL = new SQL($priceQuery1);
            $result = $priceSQL->getResultRowArray();
            if (!empty($result)) {
                #echo "found table {$materialcode}";
                foreach ($result as $arr_price) {
                    if (isset($arr_price['width'])) {
                        if ($W == trim($arr_price['width'])) {
                            $weightPrice = $this->calPrice($arr_price, $weight, $L);
                        }
                    } else {
                        $weightPrice = $this->calPrice($arr_price, $weight, $L);
                    }
                }
            } else {
                #echo "<span style='background-color:red;color:white'>Line 288/ Cannot find price using {$materialcode} and thickness = {$T}<br>";
                #echo "Check Material Price has been created or not for {$materialcode}</span><br>";
            }
        }

        #echo "\$weightPrice = $weightPrice<br>";
        return $weightPrice;
    }

    public function calPrice($arr_matprice, $weight, $L) {
        $objMatPrice = new MaterialPrice($arr_matprice);
        $obLength = $L;
        #echo "<br>****************************PLATECO*********************************<br>";
        #echo "in function calPrice of Class PLATECO <br> ";
        //fetch values from OOP
        $tbWidth = $objMatPrice->getWidth();
        $tbW1 = $objMatPrice->getW1();
        $tbW2 = $objMatPrice->getW2();
        $tbPrice = $objMatPrice->getPrice();
        $tbMaxWeight = $objMatPrice->getMaxweight();
        $tbMaxLength = $objMatPrice->getMaxlength();
        $tbMaxPrice = $objMatPrice->getMaxprice();
        $tbLooseLength = $objMatPrice->getLooselength();
        $tbLoosePrice = $objMatPrice->getLooseprice();
        $tbCuttingCharges = $objMatPrice->getCuttingcharges();
        /* #echo "<pre><strong>";
          #echo "tbWidth = $tbWidth;";
          #echo "tbW1 = $tbW1;";
          #echo "tbW2 = $tbW2;";
          #echo "tbPrice = $tbPrice;";
          #echo "tbMaxWeight = $tbMaxWeight";
          #echo "tbMaxLength = $tbMaxLength;";
          #echo "tbMaxPrice = $tbMaxPrice;";
          #echo "tbLooseLength = $tbLooseLength;";
          #echo "tbLoosePrice = $tbLoosePrice;";
          #echo "tbCuttingCharges = $tbCuttingCharges;";
          #echo "</strong></pre>";
          #echo "\$obLength = $obLength<br>"; */
        //end fetch values from OOP
        //check NULL or not
        $val_tbWidth = checkIsSet($tbWidth);
        $val_tbW1 = checkIsSet($tbW1);
        $val_tbW2 = checkIsSet($tbW2);
        $val_tbPrice = checkIsSet($tbPrice);
        $val_tbMaxWeight = checkIsSet($tbMaxWeight);
        $val_tbMaxLength = checkIsSet($tbMaxLength);
        $val_tbMaxPrice = checkIsSet($tbMaxPrice);
        $val_tbLooseLength = checkIsSet($tbLooseLength);
        $val_tbLoosePrice = checkIsSet($tbLoosePrice);
        $val_tbCuttingCharges = checkIsSet($tbCuttingCharges);
        /* #echo "<pre><strong>";
          #echo "val_tbWidth = ";
          var_dump($val_tbWidth);
          #echo "val_tbW1 = ";
          var_dump($val_tbW1);
          #echo "val_tbW2 = ";
          var_dump($val_tbW2);
          #echo "val_tbPrice = ";
          var_dump($val_tbPrice);
          #echo "val_tbMaxWeight = ";
          var_dump($val_tbMaxWeight);
          #echo "val_tbMaxLength = ";
          var_dump($val_tbMaxLength);
          #echo "val_tbMaxPrice = ";
          var_dump($val_tbMaxPrice);
          #echo "val_tbLooseLength = ";
          var_dump($val_tbLooseLength);
          #echo "val_tbLoosePrice = ";
          var_dump($val_tbLoosePrice);
          #echo "val_tbCuttingCharges = ";
          var_dump($val_tbCuttingCharges);
          #echo "</strong></pre>"; */
        //end check NULL or not
        //Variable Explanation
        //$weight = unit weight in oop
        //This is Type A Calculation

        if (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLooseLength && $val_tbLoosePrice)) {
            #echo "Found Type A <br>";
            #implement calculation A here;
            if ($obLength < $tbMaxLength) {
                $unitPrice = ($tbLoosePrice / $tbLooseLength);
                $weightPrice = $unitPrice * $obLength;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxPrice;
            } else {
                $weightPrice = 0; //cannot find price
            }
        }
        //Type B Calculation
        elseif (($val_tbW1 && $val_tbW2) && ($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice)) {
            #echo "Found Type B <br>";
            if ($val_tbLooseLength) {
                #implement calculation B here
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif (!$val_tbLooseLength && $val_tbMaxWeight) {
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
                    $weightPrice = $unitPrice * $obLength;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            }
        }
        //Type C Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ///    if($tbCuttingCharges){
            ///        //Type K Calculation
            ///        #echo "Found Type K <br>";
            ///        #implement calculation K here
            ///    }else{
            //Type C Calculation
            #echo "Line 4187, Found Type C <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
            ///    }
        }
        //Type D Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice) && ($val_tbLoosePrice && $val_tbLooseLength)) {
            #echo "Found Type D <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type E Calculation
        elseif ($val_tbWidth && (!$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            #echo "Found Type E <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
        }
        //Type F Calculation
        elseif (($val_tbWidth && $val_tbLooseLength && $val_tbLoosePrice) && (!$val_tbMaxPrice && !$val_tbMaxLength)) {
            #echo "Found Type F <br>";
        }
        //Type G Calculation
        elseif (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
            #echo "Found Type G <br>";
            if ($obLength < $tbMaxLength) {
                $unitPrice = $tbLoosePrice;
                $weightPrice = $weight * $unitPrice;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxWeight;
            } else {
                $weightPrice = 0; //error
            }
        }
        //Type I Calculation
        elseif (($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbWidth) {
            ///    if($val_tbCuttingCharges){
            ///        //TypeJ Calculation
            ///        #echo "Found Type J <br>";
            ///        #implement calcualtion J here
            ///        #same as type I but with cutting charges/
            ///    }else{
            //Type I Calculation$
            #echo "Found Type I <br>";
            #echo "\$obLength = $obLength<br>";
            #echo "\$tbMaxLength = $tbMaxLength<br>";
            //if ($val_tbLooseLength) {// mean the data structure of type I can be found looseLength field
            if ($tbLooseLength > 0) {// mean the value in the field  >  0
                if ($obLength < $tbMaxLength) {
                    #echo "\$tbLoosePrice = $tbLoosePrice, \$tbLooseLength = $tbLooseLength <br>";
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $weight;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif ($tbLooseLength == 0) {// mean the value in the field is equal to 0
                // which is the same case in many TYPE I tables the looseLength field value = 0.00
                if ($obLength < $tbMaxLength) {
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $weight * $unitPrice;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = $tbMaxPrice;
                    $weightPrice = $weight * $unitPrice;
                } else {
                    $weightPrice = 0; //error
                }
            }
            //price = (Looseprice / looselength * length) [if length < maxlength]
            //price = maxPrice [if length == maxLength]
            // } elseif (!$val_tbLooseLength) {//
            //!val_tbLooseLength mean the field in the data structure can't be found
            //the data structure do not have this field, which can't be happened
            //in type I table, this concept no need to implement any things.
//                if ($obLength < $tbMaxLength) {
//                    $unitPrice = $tbLoosePrice;
//                    $weightPrice = $weight * $unitPrice;
//                } elseif ($obLength == $tbMaxLength) {
//                    $unitPrice = $tbMaxPrice;
//                    $weightPrice = $weight * $unitPrice;
//                } else {
//                    $weightPrice = 0; //error
//                }
            //}
            ///    }
        } else {
            #echo "No table type has been found <br>";
        }
//        //Type L Calculation
//        elseif (($val_tbW1 && $val_tbW2 && $val_tbMaxLength && $val_tbMaxWeight && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
//            #echo "Found Type L <br>";
//            if ($obLength < $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
//                $weightPrice = $unitPrice * $obLength;
//            } elseif ($obLength == $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
//                $weightPrice = $unitPrice * $obLength;
//            } else {
//                $weightPrice = 0; //cannot find price
//            }
//        } else {
//            #echo "No table type has been found <br>";
//        }

        /**
          if (($tbWidth&&$tbMaxPrice&&$tbMaxLength&&$tbLoosePrice&&$tbLooseLength&&$tbCuttingCharges)==0){
          //If all value is 0, then this is normal process
          }elseif($tbWidth){
          //If there's tbWidth <> 0, then inputWidth must same as tbWidth;
          }
          //if
          elseif($a){

          }
         * */
        return $weightPrice;
    }

    public function calWeight($materialcode) {
        $density = (float) $this->getDensity();
        $volume = (float) $this->calVolume();

        #    #echo "<br>=================================================<br>";
        #    #echo "\$density = $density ,  \$volume = $volume <br> ";

        $weight = $volume * $density;
        $this->setWeight($weight);

        return $weight;
    }

    public function getShape_Code() {
        return $this->Shape_Code;
    }

    public function setShape_Code($input) {
        $this->Shape_Code = $input;
    }

    public function getT() {
        return $this->T;
    }

    public function setT($input) {
        $this->T = $input;
    }

    public function getID() {
        return $this->ID;
    }

    public function setID($input) {
        $this->ID = $input;
    }

    public function getDIA() {
        return $this->DIA;
    }

    public function setDIA($input) {
        $this->DIA = $input;
    }

}

Class FLAT extends PLATE {

    protected $T; // THICK
    protected $W; //WIDTH
    protected $L; //length
    protected $Shape_Code;
//    protected $myDimension;
    protected $materialcode;
    protected $dimension;
    protected $CrossSectionArea;

    public function __construct($materialcode, $T, $W, $L) {
        parent::__construct();
        $this->T = $T;
        $this->W = $W;
        $this->L = $L;
        $dimension = $this->formDimension();
        $this->dimension = $dimension;
        // $this->myDimension = $dimension;
        //    $this->grabDimension();
        $this->materialcode = $materialcode;
        $Shape_Code = "FLAT"; // shape code is Plate , normal
        $this->setShape_Code($Shape_Code);
        $this->Shape_Code = "FLAT"; // shape code is Plate , normal
        $this->CrossSectionArea = $T * $W;
        $this->mainProcess();
    }

    public function mainProcess() {
        $materialcode = $this->getMaterialCode();
        $T = $this->getT();
        $W = $this->getW();
        $L = $this->getL();
        $Shape_Code = $this->getShape_Code();
        $isShapeCodeMatch = $this->isShapeCodeMatch($materialcode);
        if ($isShapeCodeMatch !== 'yes') {
            #echo "Material with code : $materialcode have different Shape_Code than current process";
        } else {
            //Shape_Code matches, go to next step
            $materialType = $this->getMaterialType();
            $density = $this->grabDensitybyMType($materialType);
            $this->setDensity($density);
            $volume = $this->calVolume();
            $this->setVolume($volume);
            $weight = $this->calWeight($materialcode);
            #echo "\$volume = $volume<br>";
            #echo "\$weight = $weight<br>";
            $this->setWeight($weight);
        }
    }

    public function formDimension() {
        $T = $this->T;
        $W = $this->W;
        $L = $this->L;
        $Dimension = array('T' => $T, 'W' => $W, 'L' => $L);
        return $Dimension;
    }

//    public function grabDimension() {
//        $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
//        $T = $dimension['0']; // extracts Thickness
//        $W = $dimension['1']; // extracts Width
//        $L = $dimension['2']; // extracts Length
//
//        $this->T = $T; //set Class attribute $T
//        $this->W = $W; //set Class attribute $W
//        $this->L = $L; ///set Class attribute $L
//        //create new array, with keys
//        $newDimension = array('T' => $T, 'W' => $W, 'L' => $L);
//
//        return $newDimension;
//    }

    /**    public function extractDimension() {
      // for import the dimension = [$T, $W, $L] format
      $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
      $T = $dimension['0']; // extracts Thickness
      $W = $dimension['1']; // extracts Width
      $L = $dimension['2']; // extracts Length

      $this->T = $T; //set Class attribute $T
      $this->W = $W; //set Class attribute $W
      $this->L = $L; ///set Class attribute $L
      //create new array, with keys
      $newDimension = array('T' => $T, 'W' => $W, 'L' => $L);

      return $newDimension;
      }
     * */
    protected function calVolume() {

        $T = floatval($this->T);
        $W = floatval($this->W);
        $L = floatval($this->L);

        #volume = Thick * width * Length
        $vol = $T * $W * $L;
        ##echo "in line 267, \$vol = $vol <br>";
        $this->setVolume($vol);

        return $vol;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function isShapeCodeMatch($materialcode) {

        $sql = "SELECT Shape_Code FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
        // print_r($result);
        $myShape_Code = $result['Shape_Code'];
        $Shape_Code = $this->Shape_Code;
//        $this->setShape_Code($Shape_Code);

        if ($myShape_Code == $Shape_Code) {
            $answer = 'yes';
        } else {
            $answer = 'no';
        }

        return $answer;
    }

    public function fetchPrice($mat, $cid, $com, $iWeight, $iT, $iW, $iL) { //needs cid and company code
        $T = floatval($iT);     //Thickness Input Value
        $W = floatval($iW);     //Width Input Value
        $L = floatval($iL);     //Length Input Value
        $weight = $iWeight;
        $materialcode = $this->materialcode; //get material code
        $specialPriceTbl = $materialcode . "_" . strtolower(trim($com)) . "_" . $cid; //special price table

        $priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}'";
        $priceSQL = new SQL($priceQuery0);

        $result = $priceSQL->getResultRowArray();
        if (!empty($result)) {
            #echo "found table {$specialPriceTbl}<br>";
            foreach ($result as $arr_price) {
                if (isset($arr_price['width'])) {
                    if ($W == trim($arr_price['width'])) {
                        $weightPrice = $this->calPrice($arr_price, $weight, $L);
                    }
                } else {
                    $weightPrice = $this->calPrice($arr_price, $weight, $L);
                }
            }
        } else {
            #echo "cannot find table {$specialPriceTbl}, Using general table.<br>";
            $priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T}";
            unset($priceSQL);
            unset($result);
            $priceSQL = new SQL($priceQuery1);
            $result = $priceSQL->getResultRowArray();
            if (!empty($result)) {
                #echo "found table {$materialcode}<br>";
                foreach ($result as $arr_price) {
                    if (isset($arr_price['width'])) {
                        if ($W == trim($arr_price['width'])) {
                            $weightPrice = $this->calPrice($arr_price, $weight, $L);
                        } else {
                            #echo "<span style='background-color:red;color:white'>Line 288/ Cannot find price using {$materialcode} and Width = {$W}<br>";
                            #echo "Check if Thickness and Width exists for {$materialcode}</span><br>";
                        }
                    } else {
                        $weightPrice = $this->calPrice($arr_price, $weight, $L);
                    }
                }
            } else {
                #echo "<span style='background-color:red;color:white'>Line 288/ Cannot find price using {$materialcode} and T = {$T}<br>";
                #echo "Check Material Price has been created or not for {$materialcode}</span><br>";
            }
        }

        #echo "\$weightPrice = $weightPrice<br>";
        return $weightPrice;
    }

    public function calPrice($arr_matprice, $weight, $L) {
        $objMatPrice = new MaterialPrice($arr_matprice);
        $obLength = $L;
        #echo "<br>*****************************FLAT********************************<br>";
        #echo "in function calPrice of Class FLAT <br> ";
        //fetch values from OOP
        $tbWidth = $objMatPrice->getWidth();
        $tbW1 = $objMatPrice->getW1();
        $tbW2 = $objMatPrice->getW2();
        $tbPrice = $objMatPrice->getPrice();
        $tbMaxWeight = $objMatPrice->getMaxweight();
        $tbMaxLength = $objMatPrice->getMaxlength();
        $tbMaxPrice = $objMatPrice->getMaxprice();
        $tbLooseLength = $objMatPrice->getLooselength();
        $tbLoosePrice = $objMatPrice->getLooseprice();
        $tbCuttingCharges = $objMatPrice->getCuttingcharges();
        /* #echo "<pre><strong>";
          #echo "tbWidth = $tbWidth;";
          #echo "tbW1 = $tbW1;";
          #echo "tbW2 = $tbW2;";
          #echo "tbPrice = $tbPrice;";
          #echo "tbMaxWeight = $tbMaxWeight";
          #echo "tbMaxLength = $tbMaxLength;";
          #echo "tbMaxPrice = $tbMaxPrice;";
          #echo "tbLooseLength = $tbLooseLength;";
          #echo "tbLoosePrice = $tbLoosePrice;";
          #echo "tbCuttingCharges = $tbCuttingCharges;";
          #echo "</strong></pre>";
          #echo "\$obLength = $obLength<br>"; */
        //end fetch values from OOP
        //check NULL or not
        $val_tbWidth = checkIsSet($tbWidth);
        $val_tbW1 = checkIsSet($tbW1);
        $val_tbW2 = checkIsSet($tbW2);
        $val_tbPrice = checkIsSet($tbPrice);
        $val_tbMaxWeight = checkIsSet($tbMaxWeight);
        $val_tbMaxLength = checkIsSet($tbMaxLength);
        $val_tbMaxPrice = checkIsSet($tbMaxPrice);
        $val_tbLooseLength = checkIsSet($tbLooseLength);
        $val_tbLoosePrice = checkIsSet($tbLoosePrice);
        $val_tbCuttingCharges = checkIsSet($tbCuttingCharges);
        /* #echo "<pre><strong>";
          #echo "val_tbWidth = ";
          var_dump($val_tbWidth);
          #echo "val_tbW1 = ";
          var_dump($val_tbW1);
          #echo "val_tbW2 = ";
          var_dump($val_tbW2);
          #echo "val_tbPrice = ";
          var_dump($val_tbPrice);
          #echo "val_tbMaxWeight = ";
          var_dump($val_tbMaxWeight);
          #echo "val_tbMaxLength = ";
          var_dump($val_tbMaxLength);
          #echo "val_tbMaxPrice = ";
          var_dump($val_tbMaxPrice);
          #echo "val_tbLooseLength = ";
          var_dump($val_tbLooseLength);
          #echo "val_tbLoosePrice = ";
          var_dump($val_tbLoosePrice);
          #echo "val_tbCuttingCharges = ";
          var_dump($val_tbCuttingCharges);
          #echo "</strong></pre>"; */
        //end check NULL or not
        //Variable Explanation
        //$weight = unit weight in oop
        //This is Type A Calculation

        if (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLooseLength && $val_tbLoosePrice)) {
            #echo "Found Type A <br>";
            #implement calculation A here;
            if ($obLength < $tbMaxLength) {
                $unitPrice = ($tbLoosePrice / $tbLooseLength);
                $weightPrice = $unitPrice * $obLength;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxPrice;
            } else {
                $weightPrice = 0; //cannot find price
            }
        }
        //Type B Calculation
        elseif (($val_tbW1 && $val_tbW2) && ($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice)) {
            #echo "Found Type B <br>";
            if ($val_tbLooseLength) {
                #implement calculation B here
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif (!$val_tbLooseLength && $val_tbMaxWeight) {
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
                    $weightPrice = $unitPrice * $obLength;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            }
        }
        //Type C Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ///    if($tbCuttingCharges){
            ///        //Type K Calculation
            ///        #echo "Found Type K <br>";
            ///        #implement calculation K here
            ///    }else{
            //Type C Calculation
            #echo "Line 4653, Found Type C <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
            ///    }
        }
        //Type D Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice) && ($val_tbLoosePrice && $val_tbLooseLength)) {
            #echo "Found Type D <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type E Calculation
        elseif ($val_tbWidth && (!$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            #echo "Found Type E <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
        }
        //Type F Calculation
        elseif (($val_tbWidth && $val_tbLooseLength && $val_tbLoosePrice) && (!$val_tbMaxPrice && !$val_tbMaxLength)) {
            #echo "Found Type F <br>";
        }
        //Type G Calculation
        elseif (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
            #echo "Found Type G <br>";
            if ($obLength < $tbMaxLength) {
                $unitPrice = $tbLoosePrice;
                $weightPrice = $weight * $unitPrice;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxWeight;
            } else {
                $weightPrice = 0; //error
            }
        }
        //Type I Calculation
        elseif (($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbWidth) {
            ///    if($val_tbCuttingCharges){
            ///        //TypeJ Calculation
            ///        #echo "Found Type J <br>";
            ///        #implement calcualtion J here
            ///        #same as type I but with cutting charges/
            ///    }else{
            //Type I Calculation$
            #echo "Found Type I <br>";
            #echo "\$obLength = $obLength<br>";
            #echo "\$tbMaxLength = $tbMaxLength<br>";
            //if ($val_tbLooseLength) {// mean the data structure of type I can be found looseLength field
            if ($tbLooseLength > 0) {// mean the value in the field  >  0
                if ($obLength < $tbMaxLength) {
                    #echo "\$tbLoosePrice = $tbLoosePrice, \$tbLooseLength = $tbLooseLength <br>";
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $weight;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif ($tbLooseLength == 0) {// mean the value in the field is equal to 0
                // which is the same case in many TYPE I tables the looseLength field value = 0.00
                if ($obLength < $tbMaxLength) {
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $weight * $unitPrice;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = $tbMaxPrice;
                    $weightPrice = $weight * $unitPrice;
                } else {
                    $weightPrice = 0; //error
                }
            }
            //price = (Looseprice / looselength * length) [if length < maxlength]
            //price = maxPrice [if length == maxLength]
            // } elseif (!$val_tbLooseLength) {//
            //!val_tbLooseLength mean the field in the data structure can't be found
            //the data structure do not have this field, which can't be happened
            //in type I table, this concept no need to implement any things.
//                if ($obLength < $tbMaxLength) {
//                    $unitPrice = $tbLoosePrice;
//                    $weightPrice = $weight * $unitPrice;
//                } elseif ($obLength == $tbMaxLength) {
//                    $unitPrice = $tbMaxPrice;
//                    $weightPrice = $weight * $unitPrice;
//                } else {
//                    $weightPrice = 0; //error
//                }
            //}
            ///    }
        } else {
            #echo "No table type has been found <br>";
        }
//        //Type L Calculation
//        elseif (($val_tbW1 && $val_tbW2 && $val_tbMaxLength && $val_tbMaxWeight && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
//            #echo "Found Type L <br>";
//            if ($obLength < $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
//                $weightPrice = $unitPrice * $obLength;
//            } elseif ($obLength == $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
//                $weightPrice = $unitPrice * $obLength;
//            } else {
//                $weightPrice = 0; //cannot find price
//            }
//        } else {
//            #echo "No table type has been found <br>";
//        }

        /**
          if (($tbWidth&&$tbMaxPrice&&$tbMaxLength&&$tbLoosePrice&&$tbLooseLength&&$tbCuttingCharges)==0){
          //If all value is 0, then this is normal process
          }elseif($tbWidth){
          //If there's tbWidth <> 0, then inputWidth must same as tbWidth;
          }
          //if
          elseif($a){

          }
         * */
        return $weightPrice;
    }

    public function calWeight() {
        $density = (float) $this->getDensity();
        $volume = (float) $this->getVolume();

        #    #echo "<br>=================================================<br>";
        #    #echo "\$density = $density ,  \$volume = $volume <br> ";

        $weight = $volume * $density;
        $this->setWeight($weight);

        return $weight;
    }

    public function getCrossSectionArea() {
        return $this->CrossSectionArea;
    }

    public Function setCrossSectionArea($input) {
        $this->CrossSectionArea = $input;
    }

    public function getShape_Code() {
        return $this->Shape_Code;
    }

    public function setShape_Code($input) {
        $this->Shape_Code = $input;
    }

    public function getT() {
        return $this->T;
    }

    public function setT($input) {
        $this->T = $input;
    }

    public function getW() {
        return $this->W;
    }

    public function setW($input) {
        $this->W = $input;
    }

    public function getL() {
        return $this->L;
    }

    public function setL($input) {
        $this->L = $input;
    }

}

Class LIP extends ROD {

    protected $H; // Long Side
    protected $A; // Short Side
    protected $C; // Lip Side
    protected $t; // thickness
    protected $L; // Length
    protected $Shape_Code;
//    protected $myDimension;
    protected $materialcode;
    protected $dimension;

    public function __construct($materialcode, $H, $A, $C, $t, $L) {
        parent::__construct();
        $this->H = $H;
        $this->A = $A;
        $this->C = $C;
        $this->t = $t;
        $this->L = $L;
        $dimension = $this->formDimension();
        $this->dimension = $dimension;
        // $this->myDimension = $dimension;
        //    $this->grabDimension();
        $this->materialcode = $materialcode;
        $Shape_Code = "LIP"; // shape code is Plate , normal
        $this->setShape_Code($Shape_Code);
        $this->Shape_Code = "LIP"; // shape code is Plate , normal
        $this->mainProcess();
    }

    public function mainProcess() {
        $materialcode = $this->getMaterialCode();
        $H = $this->getH();
        $A = $this->getA();
        $C = $this->getC();
        $t = $this->getT();
        $L = $this->getL();
        $Shape_Code = $this->getShape_Code();
        $isShapeCodeMatch = $this->isShapeCodeMatch($materialcode);
        if ($isShapeCodeMatch !== 'yes') {
            #echo "Material with code : $materialcode have different Shape_Code than current process";
        } else {
            //Shape_Code matches, go to next step
            $materialType = $this->getMaterialType();
            $density = $this->grabDensitybyMType($materialType);
            $this->setDensity($density);
            $volume = $this->calVolume();
            $this->setVolume($volume);
            $weight = $this->calWeight($materialcode);
            #echo "\$volume = $volume<br>";
            #echo "\$weight = $weight<br>";
            $this->setWeight($weight);
        }
    }

    public function formDimension() {
        $H = $this->H;
        $A = $this->A;
        $C = $this->C;
        $t = $this->t;
        $L = $this->L;
        $Dimension = array('H' => $H, 'A' => $A, 'C' => $C, 't' => $t, 'L' => $L);
        return $Dimension;
    }

//    public function grabDimension() {
//        $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
//        $T = $dimension['0']; // extracts Thickness
//        $W = $dimension['1']; // extracts Width
//        $L = $dimension['2']; // extracts Length
//
//        $this->T = $T; //set Class attribute $T
//        $this->W = $W; //set Class attribute $W
//        $this->L = $L; ///set Class attribute $L
//        //create new array, with keys
//        $newDimension = array('T' => $T, 'W' => $W, 'L' => $L);
//
//        return $newDimension;
//    }

    /**    public function extractDimension() {
      // for import the dimension = [$T, $W, $L] format
      $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
      $T = $dimension['0']; // extracts Thickness
      $W = $dimension['1']; // extracts Width
      $L = $dimension['2']; // extracts Length

      $this->T = $T; //set Class attribute $T
      $this->W = $W; //set Class attribute $W
      $this->L = $L; ///set Class attribute $L
      //create new array, with keys
      $newDimension = array('T' => $T, 'W' => $W, 'L' => $L);

      return $newDimension;
      }
     * */
    protected function calVolume() {

        $H = floatval($this->H);
        $A = floatval($this->A);
        $C = floatval($this->C);
        $t = floatval($this->t);
        $L = floatval($this->L);
        ##echo "\$H = $H<br>";
        ###echo "\$A = $A<br>";
        ##echo "\$C = $C<br>";
        ##echo "\$t = $t<br>";
        ##echo "\$L = $L<br>";
        $area1 = 2 * ($A * $t);
        $area2 = $t * ($H - (2 * $t));
        $area3 = 2 * $t * ($C - $t);

        ##echo "\$area1 = $area1<br>";
        ##echo "\$area2 = $area2<br>";
        ##echo "\$area3 = $area3<br>";

        $vol = ($area1 + $area2 + $area3) * $L;

        ##echo "in line 267, \$vol = $vol <br>";
        $this->setVolume($vol);

        return $vol;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function isShapeCodeMatch($materialcode) {

        $sql = "SELECT Shape_Code FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
        // print_r($result);
        $myShape_Code = $result['Shape_Code'];
        $Shape_Code = $this->Shape_Code;
//        $this->setShape_Code($Shape_Code);

        if ($myShape_Code == $Shape_Code) {
            $answer = 'yes';
        } else {
            $answer = 'no';
        }

        return $answer;
    }

    public function fetchPrice($mat, $cid, $com, $iWeight, $iH, $iA, $iC, $it, $iL) { //needs cid and company code
        $H = floatval($iH);
        $A = floatval($iA);
        $C = floatval($iC);
        $t = floatval($it);
        $L = floatval($iL);     //Length Input Value
        $weight = $iWeight;
        $materialcode = $this->materialcode; //get material code
        $specialPriceTbl = $materialcode . "_" . strtolower(trim($com)) . "_" . $cid; //special price table
        //Old Data Table
        $priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}'";
        //New Data Table, Still Commented
        #$priceQuery0 = "SELECT * FROM {$specialPriceTbl} WHERE thickness = '{$T}'";

        $priceSQL = new SQL($priceQuery0);

        $result = $priceSQL->getResultOneRowArray();
        if (!empty($result)) {
            #echo "found table {$specialPriceTbl}<br>";
            //LIP extends ROD
            #echo "<br>********ROD-LIP*****************start of calPrice************************************<br>";
            $weightPrice = $this->calPrice($result, $weight, $L);
        } else {
            #echo "cannot find table {$specialPriceTbl}, Using general table.<br>";
            //old Data Table
            $priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T}";
            //new Data Table; Still Commented
            #$priceQuery1 = "SELECT * FROM {$materialcode} WHERE thickness = {$T}";
            unset($priceSQL);
            unset($result);
            $priceSQL = new SQL($priceQuery1);
            $result = $priceSQL->getResultOneRowArray();
            if (!empty($result)) {
                #echo "found table {$materialcode}";
                #echo "<br>*************************start of calPrice************************************<br>";
                $weightPrice = $this->calPrice($result, $weight, $L);
            } else {
                #echo "<span style='background-color:red;color:white'>Line 288/ Cannot find price using {$materialcode} and thickness = {$T}<br>";
                #echo "Check Material Price has been created or not for {$materialcode}</span><br>";
                #echo "Or use Free Page creation<br>";
            }
        }

        #echo "\$weightPrice = $weightPrice<br>";
        return $weightPrice;
    }

    public function calPrice($arr_matprice, $weight, $L) {
        $objMatPrice = new MaterialPrice($arr_matprice);
        $obLength = $L;

        #echo "<br>****************************LIP*********************************<br>";
        #echo "in function calPrice of Class LIP <br> ";
        //fetch values from OOP
        $tbWidth = $objMatPrice->getWidth();
        $tbW1 = $objMatPrice->getW1();
        $tbW2 = $objMatPrice->getW2();
        $tbPrice = $objMatPrice->getPrice();
        $tbMaxWeight = $objMatPrice->getMaxweight();
        $tbMaxLength = $objMatPrice->getMaxlength();
        $tbMaxPrice = $objMatPrice->getMaxprice();
        $tbLooseLength = $objMatPrice->getLooselength();
        $tbLoosePrice = $objMatPrice->getLooseprice();
        $tbCuttingCharges = $objMatPrice->getCuttingcharges();
        /* #echo "<pre><strong>";
          #echo "tbWidth = $tbWidth;";
          #echo "tbW1 = $tbW1;";
          #echo "tbW2 = $tbW2;";
          #echo "tbPrice = $tbPrice;";
          #echo "tbMaxWeight = $tbMaxWeight";
          #echo "tbMaxLength = $tbMaxLength;";
          #echo "tbMaxPrice = $tbMaxPrice;";
          #echo "tbLooseLength = $tbLooseLength;";
          #echo "tbLoosePrice = $tbLoosePrice;";
          #echo "tbCuttingCharges = $tbCuttingCharges;";
          #echo "</strong></pre>";
          #echo "\$obLength = $obLength<br>"; */
        //end fetch values from OOP
        //check NULL or not
        $val_tbWidth = checkIsSet($tbWidth);
        $val_tbW1 = checkIsSet($tbW1);
        $val_tbW2 = checkIsSet($tbW2);
        $val_tbPrice = checkIsSet($tbPrice);
        $val_tbMaxWeight = checkIsSet($tbMaxWeight);
        $val_tbMaxLength = checkIsSet($tbMaxLength);
        $val_tbMaxPrice = checkIsSet($tbMaxPrice);
        $val_tbLooseLength = checkIsSet($tbLooseLength);
        $val_tbLoosePrice = checkIsSet($tbLoosePrice);
        $val_tbCuttingCharges = checkIsSet($tbCuttingCharges);
//        #echo "<pre><strong>";
//        #echo "val_tbWidth = ";
        /* var_dump($val_tbWidth);
          #echo "val_tbW1 = ";
          var_dump($val_tbW1);
          #echo "val_tbW2 = ";
          var_dump($val_tbW2);
          #echo "val_tbPrice = ";
          var_dump($val_tbPrice);
          #echo "val_tbMaxWeight = ";
          var_dump($val_tbMaxWeight);
          #echo "val_tbMaxLength = ";
          var_dump($val_tbMaxLength);
          #echo "val_tbMaxPrice = ";
          var_dump($val_tbMaxPrice);
          #echo "val_tbLooseLength = ";
          var_dump($val_tbLooseLength);
          #echo "val_tbLoosePrice = ";
          var_dump($val_tbLoosePrice);
          #echo "val_tbCuttingCharges = ";
          var_dump($val_tbCuttingCharges);
          #echo "</strong></pre>"; */
        //end check NULL or not
        //Variable Explanation
        //$weight = unit weight in oop
        //This is Type A Calculation

        if (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLooseLength && $val_tbLoosePrice)) {
            #echo "Found Type A <br>";
            #implement calculation A here;
            if ($obLength < $tbMaxLength) {
                $unitPrice = ($tbLoosePrice / $tbLooseLength);
                $weightPrice = $unitPrice * $obLength;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxPrice;
            } else {
                $weightPrice = 0; //cannot find price
            }
        }
        //Type B Calculation
        elseif (($val_tbW1 && $val_tbW2) && ($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice)) {
            #echo "Found Type B <br>";
            if ($val_tbLooseLength) {
                #implement calculation B here
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif (!$val_tbLooseLength && $val_tbMaxWeight) {
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
                    $weightPrice = $unitPrice * $obLength;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            }
        }
        //Type C Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ///    if($tbCuttingCharges){
            ///        //Type K Calculation
            ///        #echo "Found Type K <br>";
            ///        #implement calculation K here
            ///    }else{
            //Type C Calculation
            #echo "Line 5135, Found Type C <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
            ///    }
        }
        //Type D Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice) && ($val_tbLoosePrice && $val_tbLooseLength)) {
            #echo "Found Type D <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type E Calculation
        elseif ($val_tbWidth && (!$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            #echo "Found Type E <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
        }
        //Type F Calculation
        elseif (($val_tbWidth && $val_tbLooseLength && $val_tbLoosePrice) && (!$val_tbMaxPrice && !$val_tbMaxLength)) {
            #echo "Found Type F <br>";
        }
        //Type G Calculation
        elseif (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
            #echo "Found Type G <br>";
            if ($obLength < $tbMaxLength) {
                $unitPrice = $tbLoosePrice;
                $weightPrice = $weight * $unitPrice;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxWeight;
            } else {
                $weightPrice = 0; //error
            }
        }
        //Type I Calculation
        elseif (($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbWidth) {
            ///    if($val_tbCuttingCharges){
            ///        //TypeJ Calculation
            ///        #echo "Found Type J <br>";
            ///        #implement calcualtion J here
            ///        #same as type I but with cutting charges/
            ///    }else{
            //Type I Calculation$
            #echo "Found Type I <br>";
            #echo "\$obLength = $obLength<br>";
            #echo "\$tbMaxLength = $tbMaxLength<br>";
            //if ($val_tbLooseLength) {// mean the data structure of type I can be found looseLength field
            if ($tbLooseLength > 0) {// mean the value in the field  >  0
                if ($obLength < $tbMaxLength) {
                    #echo "\$tbLoosePrice = $tbLoosePrice, \$tbLooseLength = $tbLooseLength <br>";
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $weight;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif ($tbLooseLength == 0) {// mean the value in the field is equal to 0
                // which is the same case in many TYPE I tables the looseLength field value = 0.00
                if ($obLength < $tbMaxLength) {
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $weight * $unitPrice;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = $tbMaxPrice;
                    $weightPrice = $weight * $unitPrice;
                } else {
                    $weightPrice = 0; //error
                }
            }
            //price = (Looseprice / looselength * length) [if length < maxlength]
            //price = maxPrice [if length == maxLength]
            // } elseif (!$val_tbLooseLength) {//
            //!val_tbLooseLength mean the field in the data structure can't be found
            //the data structure do not have this field, which can't be happened
            //in type I table, this concept no need to implement any things.
//                if ($obLength < $tbMaxLength) {
//                    $unitPrice = $tbLoosePrice;
//                    $weightPrice = $weight * $unitPrice;
//                } elseif ($obLength == $tbMaxLength) {
//                    $unitPrice = $tbMaxPrice;
//                    $weightPrice = $weight * $unitPrice;
//                } else {
//                    $weightPrice = 0; //error
//                }
            //}
            ///    }
        } else {
            #echo "No table type has been found <br>";
        }
//        //Type L Calculation
//        elseif (($val_tbW1 && $val_tbW2 && $val_tbMaxLength && $val_tbMaxWeight && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
//            #echo "Found Type L <br>";
//            if ($obLength < $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
//                $weightPrice = $unitPrice * $obLength;
//            } elseif ($obLength == $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
//                $weightPrice = $unitPrice * $obLength;
//            } else {
//                $weightPrice = 0; //cannot find price
//            }
//        } else {
//            #echo "No table type has been found <br>";
//        }

        /**
          if (($tbWidth&&$tbMaxPrice&&$tbMaxLength&&$tbLoosePrice&&$tbLooseLength&&$tbCuttingCharges)==0){
          //If all value is 0, then this is normal process
          }elseif($tbWidth){
          //If there's tbWidth <> 0, then inputWidth must same as tbWidth;
          }
          //if
          elseif($a){

          }
         * */
        return $weightPrice;
    }

    public function calWeight() {
        $density = (float) $this->getDensity();
        $volume = (float) $this->getVolume();

        #    #echo "<br>=================================================<br>";
        #    #echo "\$density = $density ,  \$volume = $volume <br> ";

        $weight = $volume * $density;
        $this->setWeight($weight);

        return $weight;
    }

    public function getShape_Code() {
        return $this->Shape_Code;
    }

    public function setShape_Code($input) {
        $this->Shape_Code = $input;
    }

    public function getT() {
        return $this->t;
    }

    public function setT($input) {
        $this->t = $input;
    }

    public function getH() {
        return $this->H;
    }

    public function setH($input) {
        $this->H = $input;
    }

    public function getC() {
        return $this->C;
    }

    public function setC($input) {
        $this->C = $input;
    }

    public function getA() {
        return $this->A;
    }

    public function setA($input) {
        $this->A = $input;
    }

    public function getL() {
        return $this->L;
    }

    public function setL($input) {
        $this->L = $input;
    }

}

Class C extends ROD { //C-Channel

    protected $H; // Vertical Rib
    protected $A; // Horizontal Rib (Flange)
    protected $T; // Flange Thickness
    protected $ribT; // Rib Thickness
    protected $L; // Length
    protected $Shape_Code;
//    protected $myDimension;
    protected $materialcode;
    protected $dimension;

    public function __construct($materialcode, $H, $A, $T, $ribT, $L) {
        parent::__construct();
        $this->H = $H;
        $this->A = $A;
        $this->T = $T;
        $this->ribT = $ribT;
        $this->L = $L;
        $dimension = $this->formDimension();
        $this->dimension = $dimension;
        // $this->myDimension = $dimension;
        //    $this->grabDimension();
        $this->materialcode = $materialcode;
        $Shape_Code = "C"; // shape code is Plate , normal
        $this->setShape_Code($Shape_Code);
        $this->Shape_Code = "C"; // shape code is Plate , normal
        $this->mainProcess();
    }

    public function mainProcess() {
        $materialcode = $this->getMaterialCode();
        $H = $this->getH();
        $A = $this->getA();
        $T = $this->getT();
        $ribT = $this->getribT();
        $L = $this->getL();
        $Shape_Code = $this->getShape_Code();
        $isShapeCodeMatch = $this->isShapeCodeMatch($materialcode);
        if ($isShapeCodeMatch !== 'yes') {
            #echo "Material with code : $materialcode have different Shape_Code than current process";
        } else {
            //Shape_Code matches, go to next step
            $materialType = $this->getMaterialType();
            $density = $this->grabDensitybyMType($materialType);
            $this->setDensity($density);
            $volume = $this->calVolume();
            $this->setVolume($volume);
//            $weight = $this->calWeight($materialcode);
//            #echo "\$volume = $volume<br>";
//            #echo "\$weight = $weight<br>";
            $this->setWeight($weight);
        }
    }

    public function formDimension() {
        $H = $this->H;
        $A = $this->A;
        $T = $this->T;
        $ribT = $this->ribT;
        $L = $this->L;
        $Dimension = array('H' => $H, 'A' => $A, 'T' => $T, 'ribT' => $ribT, 'L' => $L);
        return $Dimension;
    }

//    public function grabDimension() {
//        $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
//        $T = $dimension['0']; // extracts Thickness
//        $W = $dimension['1']; // extracts Width
//        $L = $dimension['2']; // extracts Length
//
//        $this->T = $T; //set Class attribute $T
//        $this->W = $W; //set Class attribute $W
//        $this->L = $L; ///set Class attribute $L
//        //create new array, with keys
//        $newDimension = array('T' => $T, 'W' => $W, 'L' => $L);
//
//        return $newDimension;
//    }

    /**    public function extractDimension() {
      // for import the dimension = [$T, $W, $L] format
      $dimension = $this->myDimension; //pointing local variable $dimension to assign scope variable  $this->myDimension into it.
      $T = $dimension['0']; // extracts Thickness
      $W = $dimension['1']; // extracts Width
      $L = $dimension['2']; // extracts Length

      $this->T = $T; //set Class attribute $T
      $this->W = $W; //set Class attribute $W
      $this->L = $L; ///set Class attribute $L
      //create new array, with keys
      $newDimension = array('T' => $T, 'W' => $W, 'L' => $L);

      return $newDimension;
      }
     * */
    protected function calVolume() {

        $H = floatval($this->H);
        $A = floatval($this->A);
        $T = floatval($this->T);
        $ribT = floatval($this->ribT);
        $L = floatval($this->L);

        //volume = 2*(A*T) + t*(H-(2T))   * L
        $area1 = 2 * ($A * $T);
        $area2 = $ribT * ($H - (2 * $T));

        $vol = ($area1 + $area2) * $L;

        ##echo "in line 267, \$vol = $vol <br>";
        $this->setVolume($vol);

        return $vol;
    }

    public function grabDensityByMaterialcode() {

        $materialcode = $this->materialcode;
        $materialtype = $this->materialType;

        $sql = "SELECT * FROM material2020 WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $materialType = $result['materialtype'];
        $density = $this->grabDensitybyMType($materialType);
        $this->setDensity($density);
    }

    public function isShapeCodeMatch($materialcode) {

        $sql = "SELECT Shape_Code FROM material2020 WHERE materialcode = '{$materialcode}'";
        $obj = new SQL($sql);
        $result = $obj->getResultOneRowArray();
        // print_r($result);
        $myShape_Code = $result['Shape_Code'];
        $Shape_Code = $this->Shape_Code;
//        $this->setShape_Code($Shape_Code);

        if ($myShape_Code == $Shape_Code) {
            $answer = 'yes';
        } else {
            $answer = 'no';
        }

        return $answer;
    }

    public function calPrice($arr_matprice, $weight, $L) {
        $objMatPrice = new MaterialPrice($arr_matprice);
        $obLength = $L;
        #echo "<br>****************************Class C*********************************<br>";
        #echo "in function calPrice of Class C <br> ";
        //fetch values from OOP
        $tbWidth = $objMatPrice->getWidth();
        $tbW1 = $objMatPrice->getW1();
        $tbW2 = $objMatPrice->getW2();
        $tbPrice = $objMatPrice->getPrice();
        $tbMaxWeight = $objMatPrice->getMaxweight();
        $tbMaxLength = $objMatPrice->getMaxlength();
        $tbMaxPrice = $objMatPrice->getMaxprice();
        $tbLooseLength = $objMatPrice->getLooselength();
        $tbLoosePrice = $objMatPrice->getLooseprice();
        $tbCuttingCharges = $objMatPrice->getCuttingcharges();
        /* #echo "<pre><strong>";
          #echo "tbWidth = $tbWidth;";
          #echo "tbW1 = $tbW1;";
          #echo "tbW2 = $tbW2;";
          #echo "tbPrice = $tbPrice;";
          #echo "tbMaxWeight = $tbMaxWeight";
          #echo "tbMaxLength = $tbMaxLength;";
          #echo "tbMaxPrice = $tbMaxPrice;";
          #echo "tbLooseLength = $tbLooseLength;";
          #echo "tbLoosePrice = $tbLoosePrice;";
          #echo "tbCuttingCharges = $tbCuttingCharges;";
          #echo "</strong></pre>";
          #echo "\$obLength = $obLength<br>"; */
        //end fetch values from OOP
        //check NULL or not
        $val_tbWidth = checkIsSet($tbWidth);
        $val_tbW1 = checkIsSet($tbW1);
        $val_tbW2 = checkIsSet($tbW2);
        $val_tbPrice = checkIsSet($tbPrice);
        $val_tbMaxWeight = checkIsSet($tbMaxWeight);
        $val_tbMaxLength = checkIsSet($tbMaxLength);
        $val_tbMaxPrice = checkIsSet($tbMaxPrice);
        $val_tbLooseLength = checkIsSet($tbLooseLength);
        $val_tbLoosePrice = checkIsSet($tbLoosePrice);
        $val_tbCuttingCharges = checkIsSet($tbCuttingCharges);
        /* #echo "<pre><strong>";
          #echo "val_tbWidth = ";
          var_dump($val_tbWidth);
          #echo "val_tbW1 = ";
          var_dump($val_tbW1);
          #echo "val_tbW2 = ";
          var_dump($val_tbW2);
          #echo "val_tbPrice = ";
          var_dump($val_tbPrice);
          #echo "val_tbMaxWeight = ";
          var_dump($val_tbMaxWeight);
          #echo "val_tbMaxLength = ";
          var_dump($val_tbMaxLength);
          #echo "val_tbMaxPrice = ";
          var_dump($val_tbMaxPrice);
          #echo "val_tbLooseLength = ";
          var_dump($val_tbLooseLength);
          #echo "val_tbLoosePrice = ";
          var_dump($val_tbLoosePrice);
          #echo "val_tbCuttingCharges = ";
          var_dump($val_tbCuttingCharges);
          #echo "</strong></pre>"; */
        //end check NULL or not
        //Variable Explanation
        //$weight = unit weight in oop
        //This is Type A Calculation

        if (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLooseLength && $val_tbLoosePrice)) {
            #echo "Found Type A <br>";
            #implement calculation A here;
            if ($obLength < $tbMaxLength) {
                $unitPrice = ($tbLoosePrice / $tbLooseLength);
                $weightPrice = $unitPrice * $obLength;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxPrice;
            } else {
                $weightPrice = 0; //cannot find price
            }
        }
        //Type B Calculation
        elseif (($val_tbW1 && $val_tbW2) && ($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice)) {
            #echo "Found Type B <br>";
            if ($val_tbLooseLength) {
                #implement calculation B here
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif (!$val_tbLooseLength && $val_tbMaxWeight) {
                if ($obLength < $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
                    $weightPrice = $unitPrice * $obLength;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
                    $weightPrice = $unitPrice * $obLength;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            }
        }
        //Type C Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            ///    if($tbCuttingCharges){
            ///        //Type K Calculation
            ///        #echo "Found Type K <br>";
            ///        #implement calculation K here
            ///    }else{
            //Type C Calculation
            #echo "Line 5577, Found Type C <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
            ///    }
        }
        //Type D Calculation
        elseif ((!$val_tbWidth && !$val_tbMaxLength && !$val_tbMaxPrice) && ($val_tbLoosePrice && $val_tbLooseLength)) {
            #echo "Found Type D <br>";
            $unitPrice = $tbLoosePrice / $tbLooseLength;
            $weightPrice = $unitPrice * $obLength;
        }
        //Type E Calculation
        elseif ($val_tbWidth && (!$val_tbMaxLength && !$val_tbMaxPrice && !$val_tbLooseLength && !$val_tbLoosePrice)) {
            #echo "Found Type E <br>";
            $unitPrice = $tbPrice;
            $weightPrice = $weight * $unitPrice;
        }
        //Type F Calculation
        elseif (($val_tbWidth && $val_tbLooseLength && $val_tbLoosePrice) && (!$val_tbMaxPrice && !$val_tbMaxLength)) {
            #echo "Found Type F <br>";
        }
        //Type G Calculation
        elseif (($val_tbWidth && $val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
            #echo "Found Type G <br>";
            if ($obLength < $tbMaxLength) {
                $unitPrice = $tbLoosePrice;
                $weightPrice = $weight * $unitPrice;
            } elseif ($obLength == $tbMaxLength) {
                $weightPrice = $tbMaxWeight;
            } else {
                $weightPrice = 0; //error
            }
        }
        //Type I Calculation
        elseif (($val_tbMaxLength && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbWidth) {
            ///    if($val_tbCuttingCharges){
            ///        //TypeJ Calculation
            ///        #echo "Found Type J <br>";
            ///        #implement calcualtion J here
            ///        #same as type I but with cutting charges/
            ///    }else{
            //Type I Calculation$
            #echo "Found Type I <br>";
            #echo "\$obLength = $obLength<br>";
            #echo "\$tbMaxLength = $tbMaxLength<br>";
            //if ($val_tbLooseLength) {// mean the data structure of type I can be found looseLength field
            if ($tbLooseLength > 0) {// mean the value in the field  >  0
                if ($obLength < $tbMaxLength) {
                    #echo "\$tbLoosePrice = $tbLoosePrice, \$tbLooseLength = $tbLooseLength <br>";
                    $unitPrice = ($tbLoosePrice / $tbLooseLength);
                    $weightPrice = $unitPrice * $weight;
                } elseif ($obLength == $tbMaxLength) {
                    $weightPrice = $tbMaxPrice;
                } else {
                    $weightPrice = 0; //cannot find price
                }
            } elseif ($tbLooseLength == 0) {// mean the value in the field is equal to 0
                // which is the same case in many TYPE I tables the looseLength field value = 0.00
                if ($obLength < $tbMaxLength) {
                    $unitPrice = $tbLoosePrice;
                    $weightPrice = $weight * $unitPrice;
                } elseif ($obLength == $tbMaxLength) {
                    $unitPrice = $tbMaxPrice;
                    $weightPrice = $weight * $unitPrice;
                } else {
                    $weightPrice = 0; //error
                }
            }
            //price = (Looseprice / looselength * length) [if length < maxlength]
            //price = maxPrice [if length == maxLength]
            // } elseif (!$val_tbLooseLength) {//
            //!val_tbLooseLength mean the field in the data structure can't be found
            //the data structure do not have this field, which can't be happened
            //in type I table, this concept no need to implement any things.
//                if ($obLength < $tbMaxLength) {
//                    $unitPrice = $tbLoosePrice;
//                    $weightPrice = $weight * $unitPrice;
//                } elseif ($obLength == $tbMaxLength) {
//                    $unitPrice = $tbMaxPrice;
//                    $weightPrice = $weight * $unitPrice;
//                } else {
//                    $weightPrice = 0; //error
//                }
            //}
            ///    }
        } else {
            #echo "No table type has been found <br>";
        }
//        //Type L Calculation
//        elseif (($val_tbW1 && $val_tbW2 && $val_tbMaxLength && $val_tbMaxWeight && $val_tbMaxPrice && $val_tbLoosePrice) && !$val_tbLooseLength) {
//            #echo "Found Type L <br>";
//            if ($obLength < $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbLoosePrice;
//                $weightPrice = $unitPrice * $obLength;
//            } elseif ($obLength == $tbMaxLength) {
//                $unitPrice = ($tbMaxWeight / $tbMaxLength) * $tbMaxPrice;
//                $weightPrice = $unitPrice * $obLength;
//            } else {
//                $weightPrice = 0; //cannot find price
//            }
//        } else {
//            #echo "No table type has been found <br>";
//        }

        /**
          if (($tbWidth&&$tbMaxPrice&&$tbMaxLength&&$tbLoosePrice&&$tbLooseLength&&$tbCuttingCharges)==0){
          //If all value is 0, then this is normal process
          }elseif($tbWidth){
          //If there's tbWidth <> 0, then inputWidth must same as tbWidth;
          }
          //if
          elseif($a){

          }
         * */
        return $weightPrice;
    }

    public function calWeight() {
        $density = (float) $this->getDensity();
        $volume = (float) $this->getVolume();

        #    #echo "<br>=================================================<br>";
        #    #echo "\$density = $density ,  \$volume = $volume <br> ";

        $weight = $volume * $density;
        $this->setWeight($weight);

        return $weight;
    }

    public function getShape_Code() {
        return $this->Shape_Code;
    }

    public function setShape_Code($input) {
        $this->Shape_Code = $input;
    }

    public function getT() {
        return $this->T;
    }

    public function setT($input) {
        $this->T = $input;
    }

    public function getribT() {
        return $this->ribT;
    }

    public function setribT($input) {
        $this->ribT = $input;
    }

    public function getH() {
        return $this->H;
    }

    public function setH($input) {
        $this->H = $input;
    }

    public function getA() {
        return $this->A;
    }

    public function setA($input) {
        $this->A = $input;
    }

    public function getL() {
        return $this->L;
    }

    public function setL($input) {
        $this->L = $input;
    }

}

abstract class GAS {

    protected $price;
    protected $transport;
    protected $rental;
    protected $category;

    public function __construct() {
        $this->category = "GAS";
    }

    public function setPrice($input) {

        $this->price = $input;
    }

    public function getPrice() {

        return $this->price;
    }

    public function setTransport($input) {

        $this->transport = $input;
    }

    public function getTransport() {

        return $this->transport;
    }

    public function setRental($input) {

        $this->rental = $input;
    }

    public function getRental() {

        return $this->rental;
    }

}

Class O2 extends GAS {

    protected $price;
    protected $transport;
    protected $rental;
    protected $materialcode;
    protected $category;
    protected $Shape_Code;

    public function __construct() {

        parent::__construct();
        $this->Shape_Code = "O2";
        $this->materialcode = "oxygen";

        $this->category = parent::category;
        $sql = "SELECT * FROM $materialcode";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $this->price = $result['price'];
        $this->transport = $result['transport'];
        $this->rental = $result['rental'];
    }

    public function getGasPrice() {
        return $this->price;
    }

    public function getGasTransport() {
        return $this->transport;
    }

    public function getGasRental() {
        return $this->rental;
    }

}

Class PurifiedArgon extends GAS {

    protected $price;
    protected $transport;
    protected $rental;
    protected $materialcode;
    protected $category;
    protected $Shape_Code;

    public function __construct() {

        parent::__construct($material);
        $this->Shape_Code = "PurifiedArgon";
        $this->materialcode = "purifiedargon";
        $this->materialcode = $materialcode;
        $this->category = parent::category;
        $sql = "SELECT * FROM $materialcode";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $this->price = $result['price'];
        $this->transport = $result['transport'];
        $this->rental = $result['rental'];
    }

    public function getGasPrice() {
        return $this->price;
    }

    public function getGasTransport() {
        return $this->transport;
    }

    public function getGasRental() {
        return $this->rental;
    }

}

Class Argon extends GAS {

    protected $price;
    protected $transport;
    protected $rental;
    protected $materialcode;
    protected $category;
    protected $Shape_Code;

    public function __construct() {

        parent::__construct();
        $this->Shape_Code = "Argon";
        $this->materialcode = "argon";
        $this->category = parent::category;
        $sql = "SELECT * FROM $materialcode";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $this->price = $result['price'];
        $this->transport = $result['transport'];
        $this->rental = $result['rental'];
    }

    public function getGasPrice() {
        return $this->price;
    }

    public function getGasTransport() {
        return $this->transport;
    }

    public function getGasRental() {
        return $this->rental;
    }

}

Class CO2 extends GAS {

    protected $price;
    protected $transport;
    protected $rental;
    protected $materialcode;
    protected $category;
    protected $Shape_Code;

    public function __construct() {

        parent::__construct();
        $this->Shape_Code = "CO2";
        $this->materialcode = "carbondioxide";
        $this->category = parent::category;
        $sql = "SELECT * FROM $materialcode";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $this->price = $result['price'];
        $this->transport = $result['transport'];
        $this->rental = $result['rental'];
    }

    public function getGasPrice() {
        return $this->price;
    }

    public function getGasTransport() {
        return $this->transport;
    }

    public function getGasRental() {
        return $this->rental;
    }

}

Class HCCH extends GAS {//Acetylene

    protected $price;
    protected $transport;
    protected $rental;
    protected $materialcode;
    protected $category;
    protected $Shape_Code;

    public function __construct() {

        parent::__construct();
        $this->Shape_Code = "HCCH";
        $this->materialcode = "acetylene";
        $this->category = parent::category;
        $sql = "SELECT * FROM $materialcode";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $this->price = $result['price'];
        $this->transport = $result['transport'];
        $this->rental = $result['rental'];
    }

    public function getGasPrice() {
        return $this->price;
    }

    public function getGasTransport() {
        return $this->transport;
    }

    public function getGasRental() {
        return $this->rental;
    }

}
