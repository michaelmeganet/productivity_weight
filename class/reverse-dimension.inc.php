<?php


function listArray2($array) {
    #echo "<br> List down array \$array :- <br>";
    #print_r($array);
    #echo "<br>";
}

function checkIsPLATECO($dimension_array_legacy) {

    $mdt = $dimension_array_legacy['mdt'];
    $mdt = preg_replace('/\s+/', '', $mdt);
    $mdw = $dimension_array_legacy['mdw'];
    $mdw = preg_replace('/\s+/', '', $mdw);
    $mdl = $dimension_array_legacy['mdl'];
    $mdl = preg_replace('/\s+/', '', $mdl);

    $TCheck = strtolower(preg_replace('/[0-9.]/', '', $mdt)); //remove numeric and period

    $DiaCheck = strtolower(preg_replace('/[0-9.]/', '', $mdw)); //remove numeric and period
    $IDCheck = strtolower(preg_replace('/[0-9.]/', '', $mdl)); //remove numeric and period
    #echo "\$DiaCheck = $DiaCheck, \$TCheck = $TCheck, \$IDCheck = $IDCheck <br>";

    switch ($DiaCheck) {

        case 'od':
            if ($IDCheck == 'id') {
                # code... Shape_Code = 'PLATECO'
                #echo "Shape_Code = 'PLATECO' <br>";
                return "PLATECO";
            }
            # code... Shape_Code = 'PLATEC'
            #echo "Shape_Code = 'PLATEC' <br>";
            return "PLATEC";
            break;
        case 'id':
            if ($TCheck == 'od') {
                # code... Shape_Code = 'PLATECO'
                #echo "in case = id, Tcheck = od <br>";
                #echo "Shape_Code = 'PLATECO' <br>";
                return "PLATECO";
            } elseif ($TCheck == '') {
                $thickness = (float) $mdt;
                if ($thickness > 0) {// a number
                    # code... Shape_Code = 'PLATEC'
                    #echo "Shape_Code = 'PLATEC' <br>";
                    return "PLATEC";
                }
            }
            # code... Shape_Code = 'PLATEC'
            break;

        default:
            # code...
            return "not found";
            break;
    }
}

function checkIsPLATEC($dimension_array_legacy) {

    $mdt = $dimension_array_legacy['mdt'];
    $mdt = preg_replace('/\s+/', '', $mdt);
    $mdw = $dimension_array_legacy['mdw'];
    $mdw = preg_replace('/\s+/', '', $mdw);
    $mdl = $dimension_array_legacy['mdl'];
    $mdl = preg_replace('/\s+/', '', $mdl);

    $TCheck = strtolower(preg_replace('/[0-9.]/', '', $mdt)); //remove numeric and period

    $DiaCheck = strtolower(preg_replace('/[0-9.]/', '', $mdw)); //remove numeric and period
    $IDCheck = strtolower(preg_replace('/[0-9.]/', '', $mdl)); //remove numeric and period
    #echo "\$DiaCheck = $DiaCheck, \$TCheck = $TCheck, \$IDCheck = $IDCheck <br>";

    switch ($IDCheck) {
        case "dia":
            #echo "in case \$IDCheck = $IDCheck <br>";
            $checkvalue = (float) $mdt * 1;
            echo "\$checkvalue = $checkvalue <br>";
            if ($checkvalue > 0) {
                $checkvalue2 = (float) $DiaCheck * 1;
                if ($DiaCheck == 0) {// mdw = null
                    #echo "Shape_Code = 'PLATEC' with mdl = Dia <br>";
                    return "PLATEC";
                }
            }

            break;
        case "id":
            #echo "in case id  <br>";


            if ($DiaCheck == 'od') {

                #echo "Shape_Code = 'PLATECO' with mdl = Dia <br>";
                return "PLATECO";
            }


            break;
        default:
            return "not found";
            break;
    }
}

function checkPLATE($dimension_array_legacy) {
    $result2 = checkIsPLATEC($dimension_array_legacy);
    #echo "result2 in checkPlate = $result2";
    if ($result2 == "not found") {

        $result = checkIsPLATECO($dimension_array_legacy);

        $returnresult = $result;
    } else {
        $returnresult = $result2;
    }
    return $returnresult;
}

function checkLengthAddon($thickness) {
    if ($thickness > 100) {

        $lengthaddon = 10;
    } else {

        $lengthaddon = 5;
    }
    return $lengthaddon;
}

function checkWidthAddon($thickness) {
    if ($thickness > 100) {

        $widthaddon = 10;
    } else {

        $widthaddon = 5;
    }
    return $widthaddon;
}

class MATERIAL_PRICE_TABLE {

    protected $thickness;
    protected $pricePerKG;
    protected $pricePerPcs;
    protected $cuttingCharges;
    protected $materialtype;
    protected $materialcode;
    protected $Shape_Code;
    protected $tabletype;
    protected $density;
    protected $dimension_array_legacy;
    protected $dimension_array_new;
    protected $mdt;
    protected $mdw;
    protected $mdl;
    protected $qty;
    protected $category;

    public function __construct($materialcode, $dimension_array_legacy) {
        echo "start instantiate class MATERIAL_PRICE_TABLE <br>";
        $this->materialcode = $materialcode;
        $this->dimension_array_legacy = $dimension_array_legacy;
        listArray2($dimension_array_legacy);



        ## 1. get Shape_Code, table type, materialtype, by materialcode in material2020 table

        $myResultArray = $this->grabParametersBymaterialcode($materialcode);
        listArray2($myResultArray);
        $this->Shape_Code = $myResultArray['Shape_Code'];       //Fetch Shape_Code
        $this->tabletype = $myResultArray['tabletype'];         //Fetch Table Type
        $this->materialtype = $myResultArray['materialtype'];   //Fetch Material Type
        $this->category = $myResultArray['category'];
//        $this->cuttingCharges = $myResultArray['cuttingCharges'];
        //$dimension_array_new = $this->translateToPLATEN_Dimension();
        if ($this->Shape_Code == 'PLATEN') {
            #echo "_Begin Check for PLATEN / PLATEC / PLATECO<br>";
            $isPLATECO = $this->checkPLATE($dimension_array_legacy);
            #echo "isPlateco = $isPLATECO<br>";
            switch ($isPLATECO) {
                case 'PLATECO':
                    #echo "<span style='background-color:green;'>Found the result as PLATECO</span><br>";
                    $this->Shape_Code = 'PLATECO';
                    break;
                case 'PLATEC':
                    #echo "<span style='background-color:green;'>Found the result as PLATEC</span><br>";
                    $this->Shape_Code = 'PLATEC';
                    break;
                case 'not found':

                    break;
                default:
                    break;
            }
            #echo "_End Check for PLATEN / PLATEC / PLATECO<br>";
        }
        #echo "end of instantiate class MATERIAL_PRICE_TABLE <br>";
    }

    public function grabParametersBymaterialcode($materialcode) {
        echo "start grabParametersBymaterialcode <br>";
        $sql = "SELECT * FROM material2020 "
                . "WHERE materialcode = '$materialcode'";
        $objSql = new SQL($sql);
        #echo "$sql = $sql <br>";
        $resultArray = $objSql->getResultOneRowArray();
        #echo "End grabParametersBymaterialcode <br>";
        return $resultArray;
    }

    public function setPricePerKG($input) {
        $this->pricePerKG = $input;
    }

    public function getPricePerKG() {
        return $this->pricePerKG;
    }

    public function setPricePerPcs($input) {
        $this->pricePerPcs = $input;
    }

    public function getPricePerPcs() {
        return $this->pricePerPcs;
    }

    public function setCuttingCharges($input) {
        $this->cuttingCharges = $input;
    }

    public function getCuttingCharges() {
        return $this->cuttingCharges;
    }

    public function setShapeCode($input) {
        $this->Shape_Code = $input;
    }

    public function getShapeCode() {
        return $this->Shape_Code;
    }

    public function setDensity($input) {
        $this->density = $input;
    }

    public function getDensity() {
        return $this->density;
    }

    function translateToPLATEN_Dimension() {
        #echo "start translateToPLATEN_Dimension() <br>";
        $dimension_array_legacy = $this->dimension_array_legacy;
        #echo "List down of \$dimension_array_legacy <br>";
        #print_r($dimension_array_legacy);
        #echo "<br>";
        $mdt = $dimension_array_legacy['mdt'];
        $mdw = $dimension_array_legacy['mdw'];
        $mdl = $dimension_array_legacy['mdl'];

        $this->thickness = $mdt; //assign to scope $thickness
        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;
//        echo "start translateToPLATEN_Dimension() <br>";
//        $dimension_array_legacy = $this->dimension_array_legacy;
//        echo "List down of \$dimension_array_legacy <br>";
//        print_r($dimension_array_legacy);
//        echo "<br>";
//        $mdt = strtolower($dimension_array_legacy['mdt']);
//        $mdw = strtolower($dimension_array_legacy['mdw']);
//        $mdl = strtolower($dimension_array_legacy['mdl']);
//        $this->thickness = $mdt; //assign to scope $thickness
//        $this->mdt = $mdt;
//        $this->mdw = $mdw;
//        $this->mdl = $mdl;
        $objDimension = new PLATEN_DIMENSION($mdt, $mdw, $mdl);
        $resultArray = $objDimension->getArrDimension();
        #echo "end translateToPLATEN_Dimension() <br>";
        return $resultArray;
    }

    function translateToPLATEC_Dimension() {
        #echo "start translateToPLATEC_Dimension() <br>";
        $dimension_array_legacy = $this->dimension_array_legacy;
        #echo "List down of \$dimension_array_legacy <br>";
        #print_r($dimension_array_legacy);
        #echo "<br>";
        $mdt = strtolower($dimension_array_legacy['mdt']);
        $mdw = strtolower($dimension_array_legacy['mdw']);
        $mdl = strtolower($dimension_array_legacy['mdl']);

        $this->thickness = $mdt; //assign to scope $thickness
        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;
        $objDimension = new PLATEC_DIMENSION($mdt, $mdw, $mdl);
        $resultArray = $objDimension->getArrDimension();
        #echo "end translateToPLATEC_Dimension() <br>";
        return $resultArray;
    }

    function translateToPLATECO_Dimension() {
        #echo "start translateToPLATECO_Dimension() <br>";
        $dimension_array_legacy = $this->dimension_array_legacy;
        #echo "List down of \$dimension_array_legacy <br>";
        #print_r($dimension_array_legacy);
        #echo "<br>";
        $mdt = strtolower($dimension_array_legacy['mdt']);
        $mdw = strtolower($dimension_array_legacy['mdw']);
        $mdl = strtolower($dimension_array_legacy['mdl']);
        #echo "Line 292, \$mdt = $mdt, \$mdw = $mdw, \$mdl = $mdl <br>";
        $this->thickness = $mdt; //assign to scope $thickness
        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;
        $objDimension = new PLATECO_DIMENSION($mdt, $mdw, $mdl);
        $resultArray = $objDimension->getArrDimension();
        #echo "end translateToPLATECO_Dimension() <br>";
        return $resultArray;
    }

    function translateToFLAT_Dimension() {
        #echo "start translateToFLAT_Dimension() <br>";
        $dimension_array_legacy = $this->dimension_array_legacy;
        #echo "List down of \$dimension_array_legacy <br>";
        #print_r($dimension_array_legacy);
        #echo "<br>";
        $mdt = $dimension_array_legacy['mdt'];
        $mdw = $dimension_array_legacy['mdw'];
        $mdl = $dimension_array_legacy['mdl'];

        $this->thickness = $mdt; //assign to scope $thickness
        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;
        $objDimension = new FLAT_DIMENSION($mdt, $mdw, $mdl);
        $resultArray = $objDimension->getArrDimension();
        #echo "end translateToFLAT_Dimension() <br>";
        return $resultArray;
    }

    function translatetoO_Dimension() {
        #echo "start translateToO_Dimension() <br>";
        $dimension_array_legacy = $this->dimension_array_legacy;
        #echo "List down of \$dimension_array_legacy<br>";
        #print_r($dimension_array_legacy);
        #echo "<br>";
        $mdt = $dimension_array_legacy['mdt'];
        $mdw = $dimension_array_legacy['mdw'];
        $mdl = $dimension_array_legacy['mdl'];
        $this->thickness = $mdt; //assign to scope $thickness
        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        $objDimension = new O_DIMENSION($mdt, $mdw, $mdl);
        $resultArray = $objDimension->getArrDimension();
        #echo "end translateToO_Dimension() <br>";
        return $resultArray;
    }

    function translateToHEX_Dimension() {
        #echo "start translateToHEX_Dimension() <br>";
        $dimension_array_legacy = $this->dimension_array_legacy;
        #echo "List down of \$dimension_array_legacy <br>";
        #print_r($dimension_array_legacy);
        #echo "<br>";
        $mdt = $dimension_array_legacy['mdt'];
        $mdw = $dimension_array_legacy['mdw'];
        $mdl = $dimension_array_legacy['mdl'];
        $this->thickness = $mdt; //assign to scope $thickness
        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        $objDimension = new HEX_DIMENSION($mdt, $mdw, $mdl);
        $resultArray = $objDimension->getArrDimension();
        #echo "end translateToHEX_Dimension() <br>";
        return $resultArray;
    }

    function translateToSS_Dimension() {
        #echo "start translateToSS_Dimension() <br>";
        $dimension_array_legacy = $this->dimension_array_legacy;
        #echo "List down of \$dimension_array_legacy <br>";
        #print_r($dimension_array_legacy);
        #echo "<br>";
        $mdt = $dimension_array_legacy['mdt'];
        $mdw = $dimension_array_legacy['mdw'];
        $mdl = $dimension_array_legacy['mdl'];
        $qty = $dimension_array_legacy['quantity'];
        $this->thickness = $mdt; //assign to scope $thickness
        $this->mdt = $mdt;
        $mdw = strtolower(preg_replace('/[^0-9.]/', '', $mdw)); //remove non number
        $mdl = strtolower(preg_replace('/[^0-9.]/', '', $mdl)); //remove non number
        $this->mdw = $mdw;
        $this->mdl = $mdl;
        $this->qty = $qty;
        #echo "line 265, <b>in translateToPLATECO_Dimension() </b>, \$mdt = $mdt, \$mdw = $mdw, \$mdl = $mdl<br> ";

        $objDimension = new SS_DIMENSION($mdt, $mdw, $mdl);
        $resultArray = $objDimension->getArrDimension();
        #echo "end translateToSS_Dimension() <br>";
        return $resultArray;
    }

    function translateToA_Dimension() {
        #echo "start translateToA_Dimension() <br>";
        $dimension_array_legacy = $this->dimension_array_legacy;
        #echo "List down of \$dimension_array_legacy <br>";
        #print_r($dimension_array_legacy);
        #echo "<br>";
        $mdt = $dimension_array_legacy['mdt'];
        $mdw = $dimension_array_legacy['mdw'];
        $mdl = $dimension_array_legacy['mdl'];
        $this->thickness = $mdt; //assign to scope $thickness
        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        $objDimension = new A_DIMENSION($mdt, $mdw, $mdl);
        $resultArray = $objDimension->getArrDimension();
        #echo "end translateToA_Dimension() <br>";
        return $resultArray;
    }

    function translateToHS_Dimension() {
        #echo "start translateToHS_Dimension() <br>";
        $dimension_array_legacy = $this->dimension_array_legacy;
        #echo "List down of \$dimension_array_legacy <br>";
        #print_r($dimension_array_legacy);
        #echo "<br>";
        $mdt = $dimension_array_legacy['mdt'];
        $mdw = $dimension_array_legacy['mdw'];
        $mdl = $dimension_array_legacy['mdl'];
        $this->thickness = $mdt; //assign to scope $thickness
        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        $objDimension = new HS_DIMENSION($mdt, $mdw, $mdl);
        $resultArray = $objDimension->getArrDimension();
        #echo "end translateToHS_Dimension() <br>";
        return $resultArray;
    }

    function translateToHP_Dimension() {
        #echo "start translateToHP_Dimension() <br>";
        $dimension_array_legacy = $this->dimension_array_legacy;
        #echo "List down of \$dimension_array_legacy <br>";
        #print_r($dimension_array_legacy);
        #echo "<br>";
        $mdt = $dimension_array_legacy['mdt'];
        $mdw = $dimension_array_legacy['mdw'];
        $mdl = $dimension_array_legacy['mdl'];
        $this->thickness = $mdt; //assign to scope $thickness
        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        $objDimension = new HP_DIMENSION($mdt, $mdw, $mdl);
        $resultArray = $objDimension->getArrDimension();
        #echo "end translateToHP_Dimension() <br>";
        return $resultArray;
    }

    public function checkPLATE($dimension_array_legacy) {
        $result2 = $this->checkIsPLATEC($dimension_array_legacy);
        if ($result2 == 'not found') {//If not PLATEC
            $result = $this->checkIsPLATECO($dimension_array_legacy);
            $returnResult = $result;
        } else {
            $returnResult = $result2;
        }
        return $returnResult;
    }

    private function checkIsPLATEC($dimension_array_legacy) {
        //extract data from $dimension_array_legacy
        $mdt = preg_replace('/s+/', '', $dimension_array_legacy['mdt']);
        $mdw = preg_replace('/s+/', '', $dimension_array_legacy['mdw']);
        $mdl = preg_replace('/s+/', '', $dimension_array_legacy['mdl']);

        $TCheck = trim(strtolower(preg_replace('/[0-9.]/', '', $mdt)));     //removes numeric and period from mdt
        $DiaCheck = trim(strtolower(preg_replace('/[0-9.]/', '', $mdw)));   //removes numeric and period from mdw
        $IDCheck = trim(strtolower(preg_replace('/[0-9.]/', '', $mdl)));    //removes numeric and period from mdl

        #echo "\$TCheck = $TCheck, \$DiaCheck = $DiaCheck, \$IDCheck = $IDCheck<br>";

        switch ($IDCheck) {
            case 'dia':
            case 'od':
                #echo "found dia... <br>";
                $checkvalue = (float) $mdt * 1;
                #echo "\$checkvalue = $checkvalue <br>";
                if ($checkvalue > 0) {
                    $checkvalue2 = (float) $DiaCheck * 1;
                    if ($DiaCheck == 0) {// mdw = null
                        #echo "Shape_Code = 'PLATEC' with mdl = Dia <br>";
                        $returnResult = "PLATEC";
                    }
                }
                break;
            case 'id':
                #echo "found id... <br>";
                if ($DiaCheck == 'od') {
                    #echo "Shape_Code = 'PLATECO', with mdl = Dia<br>";
                    $returnResult = 'PLATECO';
                }
                break;
            default:
                #echo "Cannot Found DIA / ID <br>";
                $returnResult = 'not found';
                break;
        }
        return $returnResult;
    }

    private function checkIsPLATECO($dimension_array_legacy) {
        //extract data from $dimension_array_legacy
        $mdt = preg_replace('/s+/', '', $dimension_array_legacy['mdt']);
        $mdw = preg_replace('/s+/', '', $dimension_array_legacy['mdw']);
        $mdl = preg_replace('/s+/', '', $dimension_array_legacy['mdl']);

        $TCheck = strtolower(preg_replace('/[0-9.]/', '', $mdt));     //removes numeric and period from mdt
        $DiaCheck = strtolower(preg_replace('/[0-9.]/', '', $mdw));   //removes numeric and period from mdw
        $IDCheck = strtolower(preg_replace('/[0-9.]/', '', $mdl));    //removes numeric and period from mdl

        #echo "\$TCheck = $TCheck, \$DiaCheck = $DiaCheck, \$IDCheck = $IDCheck<br>";

        switch ($DiaCheck) {
            case 'od':
                if ($IDCheck == 'id') {
                    # code... Shape_Code = 'PLATECO'
                    #echo "Shape_Code = 'PLATECO' <br>";
                    return 'PLATECO';
                }
                # code... Shape_Code = 'PLATEC'
                #echo "Shape_Code = 'PLATEC' <br>";
                $returnResult = 'PLATEC';
                break;
            case 'id':
                if ($TCheck == 'od') {
                    # code... Shape_Code = 'PLATECO'
                    #echo "in case = id, Tcheck = od <br>";
                    #echo "Shape_Code = 'PLATECO' <br>";
                    $returnResult = 'PLATECO';
                } elseif ($TCheck == '') {
                    $thickness = (float) $mdt;
                    if ($thickness > 0) {// a number
                        # code... Shape_Code = 'PLATEC'
                        #echo "Shape_Code = 'PLATEC' <br>";
                        $returnResult = 'PLATEC';
                    }
                }
                # code... Shape_Code = 'PLATEC'
                break;
            default:
                # code...
                #echo "Cannot Found OD / ID <br>";
                $returnResult = 'not found';
                break;
        }
        return $returnResult;
    }

}

class MATERIAL_SPECIAL_PRICE_CID extends MATERIAL_PRICE_TABLE {

    protected $cid;
    protected $com;
    protected $Shape_Code;
    protected $category;
    protected $tabletype;
    protected $materialcode;
    protected $materialtype;
    protected $dimension_array_legacy;
    protected $dimension_array_new;
    protected $materialTable;
    protected $thickness;
    protected $width;
    protected $length;
    protected $weight;
    protected $volume;

    public function __construct($cid, $com, $materialcode, $dimension_array_legacy) {
        echo "start instantiate class MATERIAL_SPECIAL_PRICE_CID <br>";
        $this->cid = $cid;
        $this->com = $com;
        parent::__construct($materialcode, $dimension_array_legacy);
        //parent::__construct($materialcode, $dimension_array_legacy);
        $this->dimension_array_legacy = $dimension_array_legacy;
        $this->materialcode = $materialcode;
        $category = $this->category;
        #echo "\$category = $category <br>";
//        $this->tabletype ;
//        $this->Shape_Code = parent::Shape_Code;
//        $this->thickness = parent::thickness;
        ### 2. check if there is any special price table for this customer of this materailcode
        $materialTable = $this->checkSpecialPriceTable($cid, $com, $materialcode);
        $this->materialTable = $materialTable;
        ## 3. use the elements in dimension_array_legacy, and the materailcode
        ##     to find the price of this particular material in material table,
        #      $thickness and other dimensional elements
        $tabletype = $this->tabletype;
        $thickness = $this->thickness;
        $Shape_Code = $this->getShapeCode();
        #echo "Shape_Code = $Shape_Code<br>";
//        sleep(0.3);
        switch ($Shape_Code) {
            case 'PLATEN':
                $dimension_array_new = $this->translateToPLATEN_Dimension();
                $T = $dimension_array_new['T'];
                $W = $dimension_array_new['W'];
                $L = $dimension_array_new['L'];
//                //check allowance
//                $widthAllowance = checkWidthAddon($T);
//                $W += $widthAllowance;
//                $lengthAllowance = checkLengthAddon($T);
//                $L += $lengthAllowance;
                // #echo "in case PLATEN, \$widthAllowance = $widthAllowance, \$lengthAllowance = $lengthAllowance<br>";
                //endcheck allowance
                $this->thickness = $T;
                $this->width = $W;
                $this->length = $L;
                $objSC = new PLATEN($materialcode, $T, $W, $L);
                $volume = $objSC->getVolume();
                $this->volume = $volume;
                $weight = $objSC->getWeight();
                $materialtype = $objSC->getMaterialType();
                $this->density = $objSC->getDensity();
                $this->weight = $weight;
                $this->materialtype = $materialtype;
                break;
            case 'PLATEC':
                $dimension_array_new = $this->translateToPLATEC_Dimension();
                #echo "\$dimension_array_new = ";
                #print_r($dimension_array_new);
                #echo "<br>";
                $T = (float) $dimension_array_new['T'];
                $DIA = (float) $dimension_array_new['DIA'];
                $W = $DIA + 10;
                $L = $DIA + 10;
                $this->thickness = $T;
                $this->width = $W;
                $this->length = $L;
                $objSC = new PLATEC($materialcode, $T, $W, $L, $DIA);
                $volume = $objSC->getVolume();
                $this->volume = $volume;
                $weight = $objSC->getWeight();
                $materialtype = $objSC->getMaterialType();
                $this->density = $objSC->getDensity();
                $this->weight = $weight;
                $this->materialtype = $materialtype;
                break;
            case 'PLATECO':
                $dimension_array_new = parent::translateToPLATECO_Dimension();
                $T = (float) $dimension_array_new['T'];
                $ID = (float) $dimension_array_new['ID'];
                $DIA = (float) $dimension_array_new['DIA'];
                $W = $DIA + 10;
                $L = $DIA + 10;
                $this->thickness = $T;
                $this->width = $W;
                $this->length = $L;
                $objSC = new PLATECO($materialcode, $T, $W, $L, $DIA, $ID);
                $volume = $objSC->getVolume();
                $this->volume = $volume;
                $weight = $objSC->getWeight();
                $materialtype = $objSC->getMaterialType();
                $this->density = $objSC->getDensity();
                $this->weight = $weight;
                $this->materialtype = $materialtype;
                break;
            case 'FLAT':
                $dimension_array_new = parent::translateToFLAT_Dimension();
                $T = $dimension_array_new['T'];
                $W = $dimension_array_new['W'];
                $L = $dimension_array_new['L'];
                //check allowance\
                $qrAllowance = "SELECT * FROM {$materialTable} WHERE thickness = $T and width = $W";
                #echo "\$qrAllowance = $qrAllowance<br>";
                $objAllowance = new SQL($qrAllowance);
                $resAllowance = $objAllowance->getResultOneRowArray();
                if (isset($resAllowance['maxlength'])) {
                    #echo "found maxlength from qrAllowance <br>";
                    $checkMaxLength = (float) $resAllowance['maxlength'];
                    #echo "\$checkMaxLength = $checkMaxLength<br>";
                    if ($L < $checkMaxLength) {
                        $lengthAllowance = checkLengthAddon($T);
                    } else {
                        $lengthAllowance = 0;
                    }
                } else {
                    #echo "not found maxlength from qrAllowance <br>";
                    $lengthAllowance = checkLengthAddon($T);
                }

                $L += $lengthAllowance;
                #echo "in case FLAT, \$lengthAllowance = $lengthAllowance<br>";
                //endcheck allowance
                $this->thickness = $T;
                $this->width = $W;
                $this->length = $L;
                $objSC = new FLAT($materialcode, $T, $W, $L);
                $volume = $objSC->getVolume();
                $this->volume = $volume;
                $weight = $objSC->getWeight();
                $materialtype = $objSC->getMaterialType();
                $this->density = $objSC->getDensity();
                $this->weight = $weight;
                $this->materialtype = $materialtype;
                break;
            case 'O':
                $dimension_array_new = parent::translatetoO_Dimension();
                $PHI = $dimension_array_new['PHI'];
                $L = $dimension_array_new['L'];
                //check allowance\
                $qrAllowance = "SELECT * FROM {$materialTable} WHERE thickness = $PHI";
                #echo "\$qrAllowance = $qrAllowance<br>";
                $objAllowance = new SQL($qrAllowance);
                $resAllowance = $objAllowance->getResultOneRowArray();
                if (isset($resAllowance['maxlength'])) {
                    #echo "found maxlength from qrAllowance <br>";
                    $checkMaxLength = (float) $resAllowance['maxlength'];
                    #echo "\$checkMaxLength = $checkMaxLength<br>";
                    if ($L < $checkMaxLength) {
                        $lengthAllowance = checkLengthAddon($PHI);
                    } else {
                        $lengthAllowance = 0;
                    }
                } else {
                    #echo "not found maxlength from qrAllowance <br>";
                    $lengthAllowance = checkLengthAddon($PHI);
                }
                $L += $lengthAllowance;
                #echo "in case O,\$lengthAllowance = $lengthAllowance<br>";
                //endcheck allowance
                $this->thickness = $PHI;
                $this->width = NULL;
                $this->length = $L;
                $objSC = new O($materialcode, $PHI, $L);
                $volume = $objSC->getVolume();
                $this->volume = $volume;
                $weight = $objSC->getWeight();
                $materialtype = $objSC->getMaterialType();
                $this->density = $objSC->getDensity();
                $this->weight = $weight;
                $this->materialtype = $materialtype;
                break;
            case 'HEX':
                $dimension_array_new = parent::translateToHEX_Dimension();
                $HEX = $dimension_array_new['HEX'];
                $L = $dimension_array_new['L'];
                //check allowance\
                $qrAllowance = "SELECT * FROM {$materialTable} WHERE thickness = $HEX";
                #echo "\$qrAllowance = $qrAllowance<br>";
                $objAllowance = new SQL($qrAllowance);
                $resAllowance = $objAllowance->getResultOneRowArray();
                if (isset($resAllowance['maxlength'])) {
                    #echo "found maxlength from qrAllowance <br>";
                    $checkMaxLength = (float) $resAllowance['maxlength'];
                    #echo "\$checkMaxLength = $checkMaxLength<br>";
                    if ($L < $checkMaxLength) {
                        $lengthAllowance = checkLengthAddon($HEX);
                    } else {
                        $lengthAllowance = 0;
                    }
                } else {
                    #echo "not found maxlength from qrAllowance <br>";
                    $lengthAllowance = checkLengthAddon($HEX);
                }
                $L += $lengthAllowance;
                #echo "in case HEX,\$lengthAllowance = $lengthAllowance<br>";
                //endcheck allowance
                $this->thickness = $HEX;
                $this->width = NULL;
                $this->length = $L;
                $objSC = new HEX($materialcode, $HEX, $L);
                $volume = $objSC->getVolume();
                $this->volume = $volume;
                $weight = $objSC->getWeight();
                $materialtype = $objSC->getMaterialType();
                $this->density = $objSC->getDensity();
                $this->weight = $weight;
                $this->materialtype = $materialtype;
                break;

            case 'SS':
                $dimension_array_new = parent::translateToSS_Dimension();
                $W1 = $dimension_array_new['W1'];
                $W2 = $dimension_array_new['W2'];
                $L = $dimension_array_new['L'];
                //check allowance\
                $qrAllowance = "SELECT * FROM {$materialTable} WHERE thickness = $W1 and width = $W2";
                #echo "\$qrAllowance = $qrAllowance<br>";
                $objAllowance = new SQL($qrAllowance);
                $resAllowance = $objAllowance->getResultOneRowArray();
                if (isset($resAllowance['maxlength'])) {
                    #echo "found maxlength from qrAllowance <br>";
                    $checkMaxLength = (float) $resAllowance['maxlength'];
                    #echo "\$checkMaxLength = $checkMaxLength<br>";
                    if ($L < $checkMaxLength) {
                        $lengthAllowance = checkLengthAddon($W1);
                    } else {
                        $lengthAllowance = 0;
                    }
                } else {
                    #echo "not found maxlength from qrAllowance <br>";
                    $checkMaxLength = 0;
                }

                #echo "\$checkMaxLength = $checkMaxLength<br>";
                if ($L < $checkMaxLength) {
                    $lengthAllowance = checkLengthAddon($W1);
                } else {
                    $lengthAllowance = 0;
                }
                $L += $lengthAllowance;
                #echo "in case SS,\$lengthAllowance = $lengthAllowance<br>";
                //endcheck allowance
                $this->thickness = $W1;
                $this->width = $W2;
                $this->length = $L;
                $objSC = new SS($materialcode, $W1, $W2, $L);
                $volume = $objSC->getVolume();
                $this->volume = $volume;
                $weight = $objSC->getWeight();
                $materialtype = $objSC->getMaterialType();
                $this->density = $objSC->getDensity();
                $this->weight = $weight;
                $this->materialtype = $materialtype;
                break;

            case 'A':
                $dimension_array_new = parent::translateToA_Dimension();
                $T = $dimension_array_new['T'];
                $W1 = $dimension_array_new['W1'];
                $W2 = $dimension_array_new['W2'];
                $L = $dimension_array_new['L'];
                //check allowance
                if ($L >= 6000) {
                    #do nothing
                    $lengthAllowance = 0;
                } else {
                    $lengthAllowance = checkLengthAddon($T);
                }
                $L += $lengthAllowance;
                #echo "in case A, \$lengthAllowance = $lengthAllowance<br>";
                //endcheck allowance
                $this->thickness = $T;
                $this->width = "$W1 x $W2";
                $this->length = $L;
                $objSC = new A($materialcode, $T, $W1, $W2, $L);
                $volume = $objSC->getVolume();
                $this->volume = $volume;
                $weight = $objSC->getWeight();
                $materialtype = $objSC->getMaterialType();
                $this->density = $objSC->getDensity();
                $this->weight = $weight;
                $this->materialtype = $materialtype;
                break;

            case 'HS':
                $dimension_array_new = parent::translateToHS_Dimension();
                $T = $dimension_array_new['T'];
                $W1 = $dimension_array_new['W1'];
                $W2 = $dimension_array_new['W2'];
                $L = $dimension_array_new['L'];
                //check allowance
                if ($L >= 6000) {
                    #do nothing
                    $lengthAllowance = 0;
                } else {
                    $lengthAllowance = checkLengthAddon($T);
                }
                $L += $lengthAllowance;
                #echo "in case HS, \$lengthAllowance = $lengthAllowance<br>";
                //endcheck allowance
                $this->thickness = $T;
                $this->width = "$W1 x $W2";
                $this->length = $L;
                $objSC = new HS($materialcode, $T, $W1, $W2, $L);
                $volume = $objSC->getVolume();
                $this->volume = $volume;
                $weight = $objSC->getWeight();
                $materialtype = $objSC->getMaterialType();
                $this->density = $objSC->getDensity();
                $this->weight = $weight;
                $this->materialtype = $materialtype;
                break;

            case 'HP':
                $dimension_array_new = parent::translateToHP_Dimension();
                $ID = $dimension_array_new['ID'];
                $OD = $dimension_array_new['OD'];
                $L = $dimension_array_new['L'];
                $this->thickness = $OD - $ID;
                $this->width = $OD;
                $this->length = $L;
                $objSC = new HP($materialcode, $ID, $OD, $L);
                $volume = $objSC->getVolume();
                $this->volume = $volume;
                $weight = $objSC->getWeight();
                $materialtype = $objSC->getMaterialType();
                $this->density = $objSC->getDensity();
                $this->weight = $weight;
                $this->materialtype = $materialtype;
                break;

            case 'GAS':
                #echo "in Line 837, case GAS, \$materialcode = $materialcode <br>";
                switch ($materialcode) {
                    case 'oxygen':
                        $objSC = new O2(oxygen);
                        $this->price = $objSC->getGasPrice();
                        $this->transport = $objSC->getGasTransport();
                        $this->rental = $objSC->getGasRental();


                        break;
                    case 'purifiedargon':
                        $objSC = new PurifiedArgon(purifiedargon);
                        $this->price = $objSC->getGasPrice();
                        $this->transport = $objSC->getGasTransport();
                        $this->rental = $objSC->getGasRental();

                        break;
                    case 'argon':
                        $objSC = new Argon(purifiedargon);
                        $this->price = $objSC->getGasPrice();
                        $this->transport = $objSC->getGasTransport();
                        $this->rental = $objSC->getGasRental();

                        break;
                    default:
                        break;
                }($materialcode);
                break;
            default:
                break;
        }
        if ($category == 'GAS') {
            $price = $this->getUnitPriceforGAS($materialTable);
        } else {


            #echo "\$weight = $this->weight <br>";
            $this->setWeight($weight);
            ## 4. build up the dimension_array_new as the return output
            $this->dimension_array_new = $dimension_array_new;
            ##########################################################
            #$price = $this->getUnitPricePerKG($tabletype, $materialTable, $dimension_array_new);
        }
        ## 4. build up the dimension_array_new as the return output
        $this->dimension_array_new = $dimension_array_new;
        ##########################################################
        #$price = $this->getUnitPricePerKG($tabletype, $materialTable, $dimension_array_new);
//        if ($tabletype == 'C') {
//            $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = '$thickness'";
//            echo "<b>\$sqlPrice = $sqlPrice </b><br>";
//            $objPrice = new SQL($sqlPrice);
//            $priceResult = $objPrice->getResultOneRowArray();
//            echo "list down \$priceResult from \$sqlPrice  <br>";
//            print_r($priceResult);
//            echo "<br>";
//            $price = $priceResult['price']; // price per KG
//            // $this->price = $price; //Assign scope varible value with local varicable $price
//            //parent::setPrice($price); // Assign Parent's scope $price with local value $price
//            $this->setPrice($price);
//        }
        ##

        #echo "End instantiate class MATERIAL_SPECIAL_PRICE_CID <br>";
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

    public function getUnitPriceforGAS($materialTable) {
        #echo "start getUnitPriceforGAS <br>";
        $materialcode = $this->materialcode;
        $sql = "SELECT * FROM $materialcode";
        $objSql = new SQL($sql);
        $result = $objSql->getResultOneRowArray();
        $price = $result['price'];
        $transport = $result['transport'];
        $rental = $result['rental'];
        #echo "in Line 897, \$price = $price <br>";
        $pricePerPcs = $price;
        $price = round($price, 2, PHP_ROUND_HALF_UP);
        $pricePerPcs = round($pricePerPcs, 2, PHP_ROUND_HALF_UP);
        $this->setPricePerKG(0);
        $this->setPricePerPcs($pricePerPcs);
        return $price; //pricePerKG
    }

    public function getUnitPricePerKG($tabletype, $materialTable, $dimension_array_new) {
        #echo "start getUnitPricePerKG <br>";
        $materialcode = $this->materialcode;
        $thickness = $this->thickness;
        $width = $this->width;
        $length = $this->length;
        $weight = $this->weight;
        $density = $this->density;
        #$thickness = $dimension_array_new['T'];
        #echo "\$tabletype = $tabletype<br>";
        #echo "\$weight = $weight<br>";
        switch ($tabletype) {
            case 'A': //thickness, width, density, maxlength, maxprice, looselength, looseprice
                #echo "in case A<br>";
                //ShapeCode in case A = PLATE|FLAT, ROD|SS, ROD|C, TUBE|HP
                $ShapeCode = $this->Shape_Code;
                switch ($ShapeCode) {
                    case 'FLAT'://thickness is T, width is W, length is L
                        $T = $thickness;
                        $W = $width;
                        $L = $length;
                        $objCSection = new FLAT($materialcode, $T, $W, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br>";
                        break;
                    case 'SS'://thickness = W1, width = W2, length = L
                        $W1 = $thickness;
                        $W2 = $width;
                        $L = $length;
                        $objCSection = new SS($materialcode, $W1, $W2, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br>";
                        break;
                    case 'C':
                        echo "<b>Type B, Shape_Code = C, not yet implemented</b><br>";
                        break;
                    case 'HP'://thickness is OD - ID, width is OD, length is L
                        $OD = $width;
                        $ID = $OD - $thickness;
                        $L = $length;
                        $objCSection = new HP($materialcode, $ID, $OD, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br>";
                        break;
                }
                $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = $thickness AND width = $width";
                #echo"<b>\$sqlPrice = $sqlPrice </b><br>";
                $objPrice = new SQL($sqlPrice);
                $priceResult = $objPrice->getResultOneRowArray();
                #echo "list down \$priceResult from \$sqlPrice  <br>";
                #print_r($priceResult);
                #echo "<br>";
                if (!empty($priceResult)) {
                    #echo "on line 663, \$CrossSectionArea = $CrossSectionArea<br>";
                    $maxlength = $priceResult['maxlength'];
                    $maxprice = $priceResult['maxprice'];
                    $looselength = $priceResult['looselength'];
                    $looseprice = $priceResult['looseprice'];
                    #$wastage = $this->checkWastage($materialcode, $thickness, $length, $maxlength);
                    #$length += $wastage;
                    #echo "<b>\$length = $length</b><br>";
                    if ($length < $maxlength) {
                        //PricePerMM = (float)$LoosePrice / (float)$LooseLength;
                        //PricePerPiece = PricePerMM * $length;
                        //PricePerKG = ??
                        $pricePerKG = $looseprice / ($looselength * $density * $CrossSectionArea);
                        #echo "\$pricePerKG = $pricePerKG<br>";
                        $weightPrice = $pricePerKG * $weight;
                        #echo "\$weightPrice = $weightPrice<br>";
                        #$unitPrice = $looseprice / $looselength;
                        #$weightPrice = $unitPrice * $length;
                        #pricePerKG = $weightPrice / $weight;
                    } elseif ($length == $maxlength) {
                        //PricePerPiece = $MaxPrice;
                        //PricePerKG = ??
                        $unitPrice = $maxprice;
                        $weightPrice = $maxprice;
                        $pricePerKG = $weightPrice / $weight;
                    } else {
                        //PricePerPiece = 0;
                        //PricePerKG = ??
                        echo "<span style='font-weight:bold; color:red'>"
                        . "Function getUnitPricePerKg,"
                        . " \$length : $length is larger than \$maxlength = $maxlength"
                        . "</span><br>";
                        $unitPrice = 0;
                        $weightPrice = 0;
                        $pricePerKG = $weightPrice / $weight;
                    }
                    $price = $pricePerKG;
                    $pricePerPcs = $weightPrice;
                } else {
                    echo "<span style='background-color:red; color:white; font-weight:bold'>"
                    . "function getUnitPricePerKG, Cannot Find Data Using thickness = $thickness, and width = $width</span><br>";
                    $price = 0;
                    $pricePerPcs = 0;
                }

                break;
            case 'B'://thickness, width, density, maxlength, maxprice, looselength, looseprice
                #echo "in case B<br>";
                //ShapeCode in case B = ROD|A, ROD|C, TUBE|HS
                $ShapeCode = $this->Shape_Code;
                switch ($ShapeCode) {
                    case 'A'://thickness is T, width is W1 x W2, length is L
                        $T = $thickness;
                        $arrWidth = preg_split("/( x )/", $width); //separates width into W1 and W2
                        $W1 = $arrWidth['0'];
                        $W2 = $arrWidth['1'];
                        $L = $length;
                        $objCSection = new A($materialcode, $T, $W1, $W2, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br>";
                        break;
                    case 'C':
                        #echo "<b>Type B, Shape_Code = C, not yet implemented</b><br>";
                        break;
                    case 'HS'://thickness = T, width = W1 x W2, length is L
                        $T = $thickness;
                        $arrWidth = preg_split("/( x )/", $width); //separates width into W1 and W2
                        $W1 = $arrWidth['0'];
                        $W2 = $arrWidth['1'];
                        $L = $length;
                        $objCSection = new HS($materialcode, $T, $W1, $W2, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br>";
                        break;
                }
                $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = '$thickness' AND width = '$width'";
                #echo "<b>\$sqlPrice = $sqlPrice </b><br>";
                $objPrice = new SQL($sqlPrice);
                $priceResult = $objPrice->getResultOneRowArray();
                #echo "list down \$priceResult from \$sqlPrice <br>";
                #print_r($priceResult);
                #echo "<br>";
                if (!empty($priceResult)) {
                    #echo "on Line 708, \$CrossSectionArea = $CrossSectionArea<br>";
                    $maxlength = $priceResult['maxlength'];
                    $maxprice = $priceResult['maxprice'];
                    $looselength = $priceResult['looselength'];
                    $looseprice = $priceResult['looseprice'];
                    #$wastage = $this->checkWastage($materialcode, $thickness, $length, $maxlength);
                    #$length += $wastage;
                    if ($length < $maxlength) {
                        $pricePerKG = $looseprice / ($looselength * $density * $CrossSectionArea);
                        $weightPrice = $pricePerKG * $weight;
                        #$unitPrice = $looseprice / $looselength;
                        #$weightPrice = $unitPrice * $length;
                        #$pricePerKG = $weightPrice / $weight;
                    } elseif ($length == $maxlength) {
                        $unitPrice = $maxprice;
                        $weightPrice = $maxprice;
                        $pricePerKG = $weightPrice / $weight;
                    } else {
                        echo "<span style='font-weight:bold; color:red'>"
                        . "Function getUnitPricePerKg,"
                        . " \$length : $length is larger than \$maxlength = $maxlength"
                        . "</span><br>";
                        $weightPrice = 0;
                        $pricePerKG = $weightPrice / $weight;
                    }
                    $price = $pricePerKG;
                    $pricePerPcs = $weightPrice;
                } else {
                    echo "<span style='background-color:red; color:white; font-weight:bold'>"
                    . "function getUnitPricePerKG, Cannot Find Data Using thickness = $thickness, and width = $width</span><br>";
                    $price = 0;
                    $pricePerPcs = 0;
                }
                break;
            case 'C'://thickness price density  [for Angle: thickness width density maxlength maxweight maxprice looseprice]
                // if (materialcode == kd11ma)
                $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = '$thickness'";
                #echo "<b>\$sqlPrice = $sqlPrice </b><br>";
                $objPrice = new SQL($sqlPrice);
                $priceResult = $objPrice->getResultOneRowArray();
                if (!empty($priceResult)) {
                    #echo "list down \$priceResult from \$sqlPrice  <br>";
                    #print_r($priceResult);
                    #echo "<br>";
                    $pricePerKG = $priceResult['price']; // price per KG
                    $weightPrice = $pricePerKG * $weight;
                    // $this->price = $price; //Assign scope varible value with local varicable $price
                    //parent::setPrice($price); // Assign Parent's scope $price with local value $price
                    $price = $pricePerKG;
                    $pricePerPcs = $weightPrice;
                } else {
                    echo "<span style='background-color:red; color:white; font-weight:bold'>"
                    . "function getUnitPricePerKG, Cannot Find Data Using thickness = $thickness</span><br>";
                    $price = 0;
                    $pricePerPcs = 0;
                }
                break;
            case 'D': //thickness, density, looselength, looseprice
                #echo "in case D<br>";
                //ShapeCode in case D = ROD|HEX
                $ShapeCode = $this->Shape_Code;
                switch ($ShapeCode) {
                    case 'HEX'://thickness is T, length is L
                        $HEX = $thickness;
                        $L = $length;
                        $objCSection = new HEX($materialcode, $HEX, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br>";
                        break;
                }
                $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = $thickness";
                #echo "<b> \$sqlPrice = $sqlPrice </b><br>";
                $objPrice = new SQL($sqlPrice);
                $priceResult = $objPrice->getResultOneRowArray();
                $looseprice = $priceResult['looseprice'];
                $looselength = $priceResult['looselength'];
                #$wastage = $this->checkWastage($materialcode, $thickness, $length);
                #$length += $wastage;
                #echo "list down \$priceResult from \$sqlPrice<br>";
                #print_r($priceResult);
                #echo "<br>";
                #echo "n line 756, \$CrossSectionArea = $CrossSectionArea<br>";
                $pricePerKG = $looseprice / ($looselength * $density * $CrossSectionArea);
                $weightPrice = $pricePerKG * $weight;
                #$unitPrice = $looseprice / $looselength;
                #$weightPrice = $unitPrice * $length;
                #pricePerKG = $weightPrice / $weight;

                $price = $pricePerKG;
                $pricePerPcs = $weightPrice;
                break;
            case 'E': //thickness width price density
                $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = $thickness AND width = $width";
                if ($materialcode == '12379p') {
                    if ($width <= 400) { //if 12379p and width <= 400, replace sql query
                        $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = $thickness";
                    } else {
                        echo "<span style='background-color:red; color:white; font-weight:bold'>"
                        . "function getUnitPricePerKG, \$materialcode = $materialcode, width is more than 400</span><br>";
                    }
                }
                #echo"<b>\$sqlPrice = $sqlPrice = </b><br>";
                $objPrice = new SQL($sqlPrice);
                $priceResult = $objPrice->getResultOneRowArray();
                if (!empty($priceResult)) {
                    #echo "list down \$priceResult from \$sqlPrice  <br>";
                    #print_r($priceResult);
                    #echo "<br>";
                    $unitPrice = $priceResult['price'];
                    $pricePerKG = $unitPrice;
                    $weightPrice = $pricePerKG * $weight;

                    $price = $pricePerKG;
                    $pricePerPcs = $weightPrice;
                } else {
                    echo "<span style='background-color:red; color:white; font-weight:bold'>"
                    . "function getUnitPricePerKG, Cannot Find Data Using thickness = $thickness, and width = $width</span><br>";
                    $price = 0;
                    $pricePerPcs = 0;
                }
                break;
            case 'F'://thickness width density looselength looseprice
                #echo "in case F<br>";
                //Shape_Code in case F = PLATE|FLAT, ROD|SS, ROD|FLAT
                $ShapeCode = $this->Shape_Code;
                switch ($ShapeCode) {
                    case 'FLAT'://T = thickness, W= width, L= length
                        $T = $thickness;
                        $W = $width;
                        $L = $length; //already added allowance
                        $objCSection = new FLAT($materialcode, $T, $W, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br>";
                        break;
                    case 'SS'://W1 = thickness, W2 = width, L = length
                        $W1 = $thickness;
                        $W2 = $width;
                        $L = $length;
                        $objCSection = new SS($materialcode, $W1, $W2, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br>";
                        break;
                }

                $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = $thickness AND width = $width";
                #echo"<b>\$sqlPrice = $sqlPrice </b><br>";
                $objPrice = new SQL($sqlPrice);
                $priceResult = $objPrice->getResultOneRowArray();
                if (!empty($priceResult)) {
                    #echo "on line 802, \$CrossSectionArea = $CrossSectionArea<br>";
                    $looselength = $priceResult['looselength'];
                    $looseprice = $priceResult['looseprice'];
                    #$wastage = $this->checkWastage($materialcode, $thickness, $length);
                    #$length += $wastage;
                    #echo "list down \$priceResult from \$sqlPrice  <br>";
                    #print_r($priceResult);
                    #echo "<br>";
                    //PricePerMM = $LoosePrice / $LooseLength;
                    //PricePerPiece = $PricePerMM * $length;
                    //PricePerKG = ??
                    $pricePerKG = $looseprice / ($looselength * $density * $CrossSectionArea);
                    $weightPrice = $pricePerKG * $weight;
                    #$unitPrice = $looseprice / $looselength;
                    #$weightPrice = $unitPrice * $length;
                    #$pricePerKG = $weightPrice / $weight;

                    $price = $pricePerKG;
                    $pricePerPcs = $weightPrice;
                } else {
                    echo "<span style='background-color:red; color:white; font-weight:bold'>"
                    . "function getUnitPricePerKG, Cannot Find Data Using thickness = $thickness, and width = $width</span><br>";
                    $price = 0;
                    $pricePerPcs = 0;
                }
                break;
            case 'G'://thickness width density maxlength maxprice looseprice
                $sqlWidth = "SELECT DISTINCT width FROM $materialTable WHERE thickness = $thickness";
                #echo "<b>\$sqlPrice = $sqlWidth</b><br>";
                $objWidth = new SQL($sqlWidth);
                $widthResult = $objWidth->getResultRowArray();
                foreach ($widthResult as $rowWidth) {
                    if (!isset($datWidth)) {
                        if ($width <= $rowWidth['width']) {
                            $datWidth = $rowWidth['width'];
                        }
                    }
                }
                $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = $thickness and width = $datWidth";
                #echo "<b>\$sqlPrice = $sqlPrice</b><br>";
                $objPrice = new SQL($sqlPrice);
                $priceResult = $objPrice->getResultOneRowArray();
                if (!empty($priceResult)) {
                    $maxlength = $priceResult['maxlength'];
                    $looseprice = $priceResult['looseprice'];
                    $maxprice = $priceResult['maxprice'];
                    $wastage = $this->checkWastage($materialcode, $thickness, $length, $maxlength);
                    $length += $wastage;
                    #echo "list down \$priceResult from \$sqlPrice <br>";
                    #print_r($priceResult);
                    #echo "<br>";
                    if ($length < $maxlength) {
                        $unitPrice = $looseprice;
                        $weightPrice = $unitPrice * $weight;
                        $pricePerKG = $weightPrice / $weight;
                    } elseif ($length == $maxlength) {
                        $unitPrice = $maxprice;
                        $weightPrice = $maxprice;
                        $pricePerKG = $weightPrice / $weight;
                    } else {
                        echo "<span style='font-weight:bold; color:red'>"
                        . "Function getUnitPricePerKg,"
                        . " \$length : $length is larger than \$maxlength = $maxlength"
                        . "</span><br>";
                        $weightPrice = 0;
                        $pricePerKG = $weightPrice / $weight;
                    }
                    $price = $pricePerKG;
                    $pricePerPcs = $weightPrice;
                } else {
                    echo "<span style='background-color:red; color:white; font-weight:bold'>"
                    . "function getUnitPricePerKG, Cannot Find Data Using thickness = $thickness and Width = $width</span><br>";
                    $price = 0;
                    $pricePerPcs = 0;
                }
                break;

            case 'H': //price transport rental
//                echo "<span style='background-color:red; color:white; font-weight:bold'>"
//                . "Calculation for Table Type 'H' is not yet implemented</span><br>";
                $sqlPrice = "SELECT * FROM $materialcode";
                #echo "<b>\$sqlPrice = $sqlPrice </b><br>";
                $objPrice = new SQL($sqlPrice);
                $priceResult = $objPrice->getResultOneRowArray();
                if (!empty($priceResult)) {
                    echo "list down \$priceResult from \$sqlPrice  <br>";
                    #print_r($priceResult);
                    #echo "<br>";
                    $price = $priceResult['price']; // price per KG
                    $transport = $priceResult['transport']; //
                    $rental = $priceResult['rental']; //
                    // $this->price = $price; //Assign scope varible value with local varicable $price
                    //parent::setPrice($price); // Assign Parent's scope $price with local value $price
                    $pricePerPcs = $price;
                } else {
                    echo "<span style='background-color:red; color:white; font-weight:bold'>"
                    . "function getUnitPricePerKG, Cannot Find Data Using thickness = $thickness</span><br>";
                    $price = 0;
                    $transport = 0;
                    $rental = 0;
                }
//                $price = 0;
//                $pricePerPcs = 0;
                break;

            case 'I'://thickness, density, maxlength, maxprice, looselength, looseprice
                #echo "in case I <br>";
                //Shape_Code in case I : ROD|O , ROD|HEX, TUBE|HP
                $ShapeCode = $this->Shape_Code;
                switch ($ShapeCode) { //get the value from $dimension_array_new
                    case 'O': //thickness is PHI, length is L
                        #$PHI = $dimension_array_new['PHI'];
                        #$L = $dimension_array_new['L']; //not yet added allowance
                        $PHI = $thickness;
                        $L = $length; //already added allowance
                        $objCSection = new O($materialcode, $PHI, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br>";
                        break;
                    case 'HEX'://thickness is T, length is L
                        $HEX = $thickness;
                        $L = $length;
                        $objCSection = new HEX($materialcode, $HEX, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br?";
                        break;
                    case 'HP'://thickness is OD - ID, width is OD, length is L
                        $OD = $width;
                        $ID = $OD - $thickness;
                        $L = $length;
                        $objCSection = new HP($materialcode, $ID, $OD, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br>";
                        break;
                }
                $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = $thickness";
                #echo "<b>\$sqlPrice = $sqlPrice</b><br>";
                $objPrice = new SQL($sqlPrice);
                $priceResult = $objPrice->getResultOneRowArray();
                if (!empty($priceResult)) {
                    $maxlength = $priceResult['maxlength'];
                    $maxprice = $priceResult['maxprice'];
                    $looselength = $priceResult['looselength'];
                    $looseprice = $priceResult['looseprice'];
                    //$wastage = $this->checkWastage($materialcode, $thickness, $length, $maxlength);
                    // $wastage = 0;
                    //$length += $wastage;
                    //#echo " \$wastage =  $wastage, \$length = $length <br>";
                    #echo "list down\$priceResult from \$sqlPrice <br>";
                    #print_r($priceResult);
                    #echo "<br>";
                    if ($looselength > 0) {
                        #echo "\$looselength > 0<br>";
                        if ($length < $maxlength) {
                            $radius = $thickness / 2;
                            #echo "Line 912 , \$CrossSectionArea = $CrossSectionArea<br>";
                            //$pricePerKG = $looseprice / ($looselength * pow($radius, 2) * $density * pi());
                            $pricePerKG = $looseprice / ($looselength * $density * $CrossSectionArea);
                            #echo "Line 915, \$pricePerKG  = $pricePerKG <br>";
                            $weightPrice = $pricePerKG * $weight;

                            #$pricePerKG = $this->convert_priceMM_to_priceKG($priceResult);
                            #$weightPrice = $weight * $pricePerKG;
                            #$unitPrice = $looseprice / $looselength;
                            #$unitPrice = $looseprice;
                            #$weightPrice = $unitPrice * $length;
                            #$weightPrice = $unitPrice * $weight;
                        } elseif ($length == $maxlength) {
                            $unitPrice = $maxprice;
                            $weightPrice = $maxprice;
                            $pricePerKG = $weightPrice / $weight;
                        } else {
                            echo "<span style='font-weight:bold; color:red'>"
                            . "Function getUnitPricePerKg,"
                            . " \$length : $length is larger than \$maxlength = $maxlength"
                            . "</span><br>";
                            $weightPrice = 0;
                            $pricePerKG = $weightPrice / $weight;
                        }
                    } elseif ($looselength == 0) {
                        #echo "\$looselength == 0<br>";
                        if ($length < $maxlength) {
                            $unitPrice = $looseprice;
                            $weightPrice = $unitPrice * $weight;
                            $pricePerKG = $weightPrice / $weight;
                        } elseif ($length == $maxlength) {
                            $unitPrice = $maxprice;
                            $weightPrice = $maxprice;
                            $pricePerKG = $weightPrice / $weight;
                        } else {
                            echo "<span style='font-weight:bold; color:red'>"
                            . "Function getUnitPricePerKg,"
                            . " \$length : $length is larger than \$maxlength = $maxlength"
                            . "</span><br>";
                            $weightPrice = 0;
                            $pricePerKG = $weightPrice / $weight;
                        }
                    }
                    $price = $pricePerKG;
                    $pricePerPcs = $weightPrice;
                } else {
                    echo "<span style='background-color:red; color:white; font-weight:bold'>"
                    . "function getUnitPricePerKG, Cannot Find Data Using thickness = $thickness</span><br>";
                    $price = 0;
                    $pricePerPcs = 0;
                }
                break;

            case 'J':// thickness density maxlength maxprice looselength looseprice cuttingcharges
                //In material2020 table, only for O Shape_Code
                #echo "in case J <br>";
                $ShapeCode = $this->Shape_Code;
                //ShapeCode in case J : ROD|O
                switch ($ShapeCode) {
                    case 'O':
                        $PHI = $thickness;
                        $L = $length; //already added allowance
                        $objCSection = new O($materialcode, $PHI, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br>";
                        break;
                }

                $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = $thickness";
                #echo "<b>\$sqlPrice = $sqlPrice</b><br>";
                $objPrice = new SQL($sqlPrice);
                $priceResult = $objPrice->getResultOneRowArray();
                if (!empty($priceResult)) {
                    $maxlength = $priceResult['maxlength'];
                    $maxprice = $priceResult['maxprice'];
                    $looselength = $priceResult['looselength'];
                    $looseprice = $priceResult['looseprice'];
                    $cuttingcharges = $priceResult['cuttingcharges'];
                    #$wastage = $this->checkWastage($materialcode, $thickness, $length, $maxlength);
                    #$length += $wastage;
                    #echo "list down\$priceResult from \$sqlPrice <br>";
                    #print_r($priceResult);
                    #echo "<br>";
                    if ($looselength > 1.00) {
                        #echo "\$looselength > 1<br>";
                        if ($length < $maxlength) {
                            ##echo "on line 999, \$CrossSectionArea = $CrossSectionArea<br>";
                            $pricePerKG = $looseprice / ($looselength * $density * $CrossSectionArea);
                            #echo "on line 1000, \$pricePerKG = $pricePerKG<br>";
                            $weightPrice = $pricePerKG * $weight;
                            #$unitPrice = $looseprice / $looselength;
                            #$weightPrice = $unitPrice * $weight;
                            #$pricePerKG = $weightPrice / $weight;
                        } elseif ($length == $maxlength) {
                            $unitPrice = $maxprice;
                            $weightPrice = $maxprice;
                            $pricePerKG = $weightPrice / $weight;
                        } else {
                            echo "<span style='font-weight:bold; color:red'>"
                            . "Function getUnitPricePerKg,"
                            . " \$length : $length is larger than \$maxlength = $maxlength"
                            . "</span><br>";
                            $weightPrice = 0;
                            $pricePerKG = $weightPrice / $weight;
                        }
                    } elseif ($looselength <= 1.00) {
                        #echo "\$looselength == 0<br>";
                        if ($length < $maxlength) {
                            $unitPrice = $looseprice;
                            $weightPrice = $unitPrice * $weight;
                            $pricePerKG = $weightPrice / $weight;
                        } elseif ($length == $maxlength) {
                            $unitPrice = $maxprice;
                            $weightPrice = $maxprice;
                            $pricePerKG = $weightPrice / $weight;
                        } else {
                            echo "<span style='font-weight:bold; color:red'>"
                            . "Function getUnitPricePerKg,"
                            . " \$length : $length is larger than \$maxlength = $maxlength"
                            . "</span><br>";
                            $weightPrice = 0;
                            $pricePerKG = $weightPrice / $weight;
                        }
                    }
                    $this->setCuttingCharges($cuttingcharges);
                    $price = $pricePerKG;
                    $pricePerPcs = $weightPrice;
                } else {
                    echo "<span style='background-color:red; color:white; font-weight:bold'>"
                    . "function getUnitPricePerKG, Cannot Find Data Using thickness = $thickness</span><br>";
                    $price = 0;
                    $pricePerPcs = 0;
                }
                break;

            case 'K'://thickness price density cuttingcharges
                #same as C, but with Cutting Charges.
                $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = '$thickness'";
                #echo "<b>\$sqlPrice = $sqlPrice </b><br>";
                $objPrice = new SQL($sqlPrice);
                $priceResult = $objPrice->getResultOneRowArray();
                if (!empty($priceResult)) {
                    #echo "list down \$priceResult from \$sqlPrice  <br>";
                    #print_r($priceResult);
                    #echo "<br>";
                    $pricePerKG = $priceResult['price']; // price per KG
                    $weightPrice = $pricePerKG * $weight;
                    $cuttingcharges = $priceResult['cuttingcharges'];

                    // $this->price = $price; //Assign scope varible value with local varicable $price
                    //parent::setPrice($price); // Assign Parent's scope $price with local value $price
                    $price = $pricePerKG;
                    $pricePerPcs = $weightPrice;
                    $this->setCuttingCharges($cuttingcharges);
                } else {
                    echo "<span style='background-color:red; color:white; font-weight:bold'>"
                    . "function getUnitPricePerKG, Cannot Find Data Using thickness = $thickness</span><br>";
                    $price = 0;
                    $pricePerPcs = 0;
                }
                break;
            case 'L'://thickness width density maxlength maxweight maxprice looseprice[
                #echo "in case L<br>";
                $ShapeCode = $this->Shape_Code;
                //ShapeCode in case J : ROD|O
                switch ($ShapeCode) {
                    case 'A'://thickness is T, width is W1 x W2, length is L
                        $T = $thickness;
                        $arrWidth = preg_split("/( x )/", $width); //separates width into W1 and W2
                        $W1 = $arrWidth['0'];
                        $W2 = $arrWidth['1'];
                        $L = $length;
                        $objCSection = new A($materialcode, $T, $W1, $W2, $L);
                        $CrossSectionArea = $objCSection->getCrossSectionArea();
                        #echo "\$ShapeCode = $ShapeCode<br>";
                        #echo "\$CrossSectionArea = $CrossSectionArea<br>";
                        break;
                }
                $sqlPrice = "SELECT * FROM $materialTable WHERE thickness = '$thickness' AND width = '$width'";
                #echo "<b>\$sqlPrice = $sqlPrice </b><br>";
                $objPrice = new SQL($sqlPrice);
                $priceResult = $objPrice->getResultOneRowArray();
                if (!empty($priceResult)) {
                    #echo "list down \$priceResult from \$sqlPrice  <br>";
                    #print_r($priceResult);
                    #echo "<br>";
                    $maxlength = $priceResult['maxlength'];
                    $maxweight = $priceResult['maxweight'];
                    $maxprice = $priceResult['maxprice'];
                    $looseprice = $priceResult['looseprice'];
                    if ($length < $maxlength) {
                        #echo "on line 1341, \$CrossSectionArea = $CrossSectionArea<br>";
                        $pricePerKG = ($maxweight * $looseprice) / ($maxlength * $CrossSectionArea * $density);
                        #echo "on line 1343, \$pricePerKG = $pricePerKG<br>";
                        $weightPrice = $pricePerKG * $weight;
                    } elseif ($length == $maxlength) {
                        #echo "on line 1341, \$CrossSectionArea = $CrossSectionArea<br>";
                        $pricePerKG = ($maxweight * $maxprice) / ($maxlength * $CrossSectionArea * $density);
                        #echo "on line 1343, \$pricePerKG = $pricePerKG<br>";
                        $weightPrice = $pricePerKG * $weight;
                    } else {
                        echo "<span style='font-weight:bold; color:red'>"
                        . "Function getUnitPricePerKg,"
                        . " \$length : $length is larger than \$maxlength = $maxlength"
                        . "</span><br>";
                        $weightPrice = 0;
                        $pricePerKG = $weightPrice * $weight;
                    }
                    $price = $pricePerKG;
                    $pricePerPcs = $weightPrice;
                }
                break;

            default:
                echo "<span style='background-color:red; color:white; font-weight:bold'>"
                . "Cannot Find Table Type, <br>Either It's not implemented yet, or there's no such Type</span><br>";
                $price = 0;
                $pricePerPcs = 0;
                break;
        }
        $price = round($price, 2, PHP_ROUND_HALF_UP);
        $pricePerPcs = round($pricePerPcs, 2, PHP_ROUND_HALF_UP);
        $this->setPricePerKG($price);
        $this->setPricePerPcs($pricePerPcs);
        return $price; //pricePerKG
        #return $pricePerPcs; //pricePerPcs
    }

    /*
      private function convert_priceMM_to_priceKG($priceResult = array() /*array from materialtable){
      //This function only works for Rod type if there is
      //thickness, density, maxlength, maxprice, looselength, looseprice
      echo "on Function convert_priceMM_to_priceKG<br>";
      $density = $this->density;
      echo "\$density = $density<br>";
      $thick = $priceResult['thickness'];
      echo "\$thick = $thick<br>";
      $looselength = $priceResult['looselength'] + 5;
      echo "\$looselength = $looselength<br>";
      $looseprice = $priceResult['looseprice'];
      echo "\$looseprice = $looseprice<br>";
      $volmat = pi() * (pow($thick/2,2)) * $looselength;
      echo "\$volmat = $volmat<br>";
      $weightmat = $volmat * $density;
      echo "\$weightmat = $weightmat<br>";
      $pricePerKG = $looseprice / $weightmat;
      echo "\$pricePerKG = $pricePerKG<br>";
      echo "end Function convert_priceMM_to_priceKG<br>";
      return $pricePerKG;
      }
     */

    public function checkSpecialPriceTable($cid, $com, $materialcode) {
        #echo "start checkSpecialPriceTable <br>";
        $cid = $this->cid;
        ### 2. check if there is any special price table for this customer of this materailcode
        $specialPriceTableCid = $materialcode . "_" . $com . "_" . $cid;
        $sqlcount = "SHOW TABLES LIKE '$specialPriceTableCid'";
        #echo "\$sqlcount = $sqlcount <br>";
        $objRowCount = new SQL($sqlcount);
        $row = $objRowCount->getResultOneRowArray();
        #print_r($row);
        #echo"<br>";
        if (!empty($row)) {

            return $specialPriceTableCid;
        } else {
            return $materialcode;
        }
    }

    public function checkWastage($materialcode, $thickness, $length, $maxlength = 0) {
        #echo "==+ line 538, on function checkWastage +==<br>";
        $qrWaste = "SELECT category FROM material2020 WHERE materialcode = '$materialcode'";
        $objWaste = new SQL($qrWaste);
        $category = $objWaste->getResultOneRowArray()['category'];
        $wastage = 0;
        #echo "\$category = $category<br>";
        switch ($category) {
            case 'PLATE':
                if ($thickness > 100) {
                    $wastage = 10;
                } elseif ($thickness <= 100) {
                    $wastage = 5;
                }

                if (isset($maxlength)) {
                    if ($length == $maxlength || $length == 6000) {
                        $wastage = 0;
                    }
                }
                break;
            case 'ROD':
                if ($thickness > 100) {
                    $wastage = 10;
                } elseif ($thickness <= 100) {
                    $wastage = 5;
                }

                if (isset($maxlength)) {
                    if ($length == $maxlength || $length == 6000) {
                        $wastage = 0;
                    }
                }
                break;
            case 'TUBE':
                if ($thickness > 100) {
                    $wastage = 10;
                } elseif ($thickness <= 100) {
                    $wastage = 5;
                }

                if (isset($maxlength)) {
                    if ($length == $maxlength || $length == 6000) {
                        $wastage = 0;
                    }
                }
                break;
        }
        #echo "Thickness is $thickness, Length is $length, Maxlength is $maxlength, added wastage : $wastage <br>";
        #echo "==+ End function checkWastage() +==<br>";
        return $wastage;
    }

    public function getMinPrice($pricePerPcs, $com, $cid) {
        #echo "== Line 518, function checkMinPrice() ==<br>";
        $materialcode = $this->materialcode;
        $materialtype = $this->materialtype;
        #echo "on function checkMinPrice, materialType = $materialtype, pricePerPcs = $pricePerPcs<br>";
        switch ($materialtype) {
            case 'ts':
                #echo "found 'ts'<br>";
                if ($pricePerPcs <= 5.00) {
                    $MinPrice = 5.00;
                } else {
                    $MinPrice = 0.00;
                }
                break;
            case 'aa':
                #echo "found 'aa'<br>";
                if ($materialcode == 'a5052ap' || $materialcode == 'ss303p' || $materialcode == 'yh52p' || $materialcode == 'aa6061t651p') {
                    if ($pricePerPcs <= 0.5) {
                        $MinPrice = 0.5;
                    } elseif ($pricePerPcs <= 3.0) {
                        $MinPrice = 5.00;
                    } else {
                        $MinPrice = 0.00;
                    }
                } else {
                    if ($pricePerPcs <= 3.0) {
                        $MinPrice = 3.00;
                    } else {
                        $MinPrice = 0.00;
                    }
                }
                break;
            case 'ms':
                #echo "found 'ms'<br>";
                if ($pricePerPcs <= 3.00) {
                    $MinPrice = 3.00;
                } else {
                    $MinPrice = 0.00;
                }
                break;
            case 'sus':
                #echo "found 'sus'<br>";
                if ($materialcode == 'ss303p') {
                    if ($pricePerPcs <= 5.00) {
                        $MinPrice = 5.00;
                    } else {
                        $MinPrice = 0.00;
                    }
                } else {
                    if ($pricePerPcs <= 3.0) {
                        $MinPrice = 3.00;
                    } else {
                        $MinPrice = 0.00;
                    }
                }
                break;
            default:
                if ($materialcode == 'mlcgphh' || $materialcode == 'mlcgpst') {
                    if (($com == 'PST' && $cid = 5171) || ($com == 'PST' && $cid == 21187) || ($com == 'PST' && $cid == 21188)) {
                        if ($pricePerPcs < 0.5) {
                            $MinPrice = 0.5;
                        } else {
                            $MinPrice = 0.00;
                        }
                    } else {
                        if ($pricePerPcs < 3) {
                            $MinPrice = 3.00;
                        } else {
                            $MinPrice = 0.00;
                        }
                    }
                } else {
                    if ($pricePerPcs < 3) {
                        $MinPrice = 3.00;
                    } else {
                        $MinPrice = 0.00;
                    }
                }
                break;
        }
        #echo "== End Function checkMinPrice ==<br>";
        return $MinPrice;
    }

    public function setQTY($input) {
        $this->quantity = $input;
    }

    public function getQTY() {
        return $this->quantity;
    }

//    public function setWeight($input) {
//        $this->weight = $input;
//    }
//    public function getWeight() {
//        return $this->weight;
//    }
}

class LEGACY_DIMENSION {

    protected $mdt;
    protected $mdw;
    protected $mdl;

    public function __construct($mdt, $mdw, $mdl) {
        #echo "start instantiate class LEGACY_DIMENSION <br>";
        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;
        #echo "end instantiate class LEGACY_DIMENSION <br>";
    }

    public function setMdt($input) {
        $this->mdt = $input;
    }

    public function getMdt() {
        return $this->mdt;
    }

    public function setMdw($input) {
        $this->mdw = $input;
    }

    public function getMdw() {
        return $this->mdw;
    }

    public function setMdl($input) {
        $this->mdl = $input;
    }

    public function getMdl() {
        return $this->mdl;
    }

}

class PLATEN_DIMENSION extends LEGACY_DIMENSION {

    protected $mdt;
    protected $mdw;
    protected $mdl;
    protected $T;
    protected $W;
    protected $L;
    protected $arrDimension;

    public function __construct($mdt, $mdw, $mdl) {
        #echo "start instantiate class PLATEN_DIMENSION <br>";
        parent::__construct($mdt, $mdw, $mdl);

        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        $T = $mdt;
        $W = $mdw;
        $L = $mdl;

        $this->arrDimension = array('T' => $T, 'W' => $W, 'L' => $L);
        #echo "End instantiate class PLATEN_DIMENSION <br>";
    }

    public function setMdt($input) {
        $this->mdt = $input;
    }

    public function getMdt() {
        return $this->mdt;
    }

    public function setMdw($input) {
        $this->mdw = $input;
    }

    public function getMdw() {
        return $this->mdw;
    }

    public function setMdl($input) {
        $this->mdl = $input;
    }

    public function getMdl() {
        return $this->mdl;
    }

    public function setArrDimension($input) {
        $this->arrDimension = $input;
    }

    public function getArrDimension() {
        return $this->arrDimension;
    }

}

class PLATEC_DIMENSION extends LEGACY_DIMENSION {

    protected $mdt;
    protected $mdw;
    protected $mdl;
    protected $DIA;
    protected $L;
    protected $arrDimension;

    public function __construct($mdt, $mdw, $mdl) {
        #echo "start instantiate class PLATEC_DIMENSION <br>";
        parent::__construct($mdt, $mdw, $mdl);
        //common practice is mdt = T, mdw = NULL, mdl = DIA;
        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        if (isset($mdw)) {
            if (!empty($mdw)) {
                #echo "The \$mdw has value, Please Check if this is a ROD material or not?<br>";
            }
        }

        $T = preg_replace('/[^0-9.]/', '', strtolower($mdt));
        $DIA = preg_replace('/([a-zA-Z]|[.])/', '', strtolower($mdl));
        //$DIA = preg_replace('/(dia[.,;:\s])/', '', strtolower($mdl));

        $this->arrDimension = array('T' => $T, 'DIA' => $DIA);
        #echo "End instantiate class PLATEC_DIMENSION <br>";
    }

    public function setMdt($input) {
        $this->mdt = $input;
    }

    public function getMdt() {
        return $this->mdt;
    }

    public function setMdw($input) {
        $this->mdw = $input;
    }

    public function getMdw() {
        return $this->mdw;
    }

    public function setMdl($input) {
        $this->mdl = $input;
    }

    public function getMdl() {
        return $this->mdl;
    }

    public function setArrDimension($input) {
        $this->arrDimension = $input;
    }

    public function getArrDimension() {
        return $this->arrDimension;
    }

}

class PLATECO_DIMENSION extends LEGACY_DIMENSION {

    protected $mdt;
    protected $mdw;
    protected $mdl;
    protected $T;
    protected $ID;
    protected $DIA; //Or OD, in the old input
    protected $arrDimension;

    public function __construct($mdt, $mdw, $mdl) {
        #echo "start instantiate class PLATECO_DIMENSION <br>";
        parent::__construct($mdt, $mdw, $mdl);
        //common practice is mdt = T, mdw = DIA/OD, mdl = ID

        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        $T = preg_replace('/[^0-9.]/', '', $mdt);
        $DIA = preg_replace('/([a-zA-Z]|[.,;:\s])/', '', $mdw);
        $ID = preg_replace('/([a-zA-Z]|[.,;:\s])/', '', $mdl);
//        $DIA = preg_replace('/(dia|od[.,;:\s])/', '', $mdw);
        $this->arrDimension = array('T' => $T, 'DIA' => $DIA, 'ID' => $ID);
        #echo "End instantiate class PLATECO_DIMENSION <br>";
    }

    public function setMdt($input) {
        $this->mdt = $input;
    }

    public function getMdt() {
        return $this->mdt;
    }

    public function setMdw($input) {
        $this->mdw = $input;
    }

    public function getMdw() {
        return $this->mdw;
    }

    public function setMdl($input) {
        $this->mdl = $input;
    }

    public function getMdl() {
        return $this->mdl;
    }

    public function setArrDimension($input) {
        $this->arrDimension = $input;
    }

    public function getArrDimension() {
        return $this->arrDimension;
    }

}

class FLAT_DIMENSION extends LEGACY_DIMENSION {

    protected $mdt;
    protected $mdw;
    protected $mdl;
    protected $T;
    protected $W;
    protected $L;
    protected $arrDimension;

    public function __construct($mdt, $mdw, $mdl) {
        #echo "start instantiate class FLAT_DIMENSION <br>";
        parent::__construct($mdt, $mdw, $mdl);

        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        $T = $mdt;
        $W = $mdw;
        $L = $mdl;

        $this->arrDimension = array('T' => $T, 'W' => $W, 'L' => $L);
        #echo "End instantiate class FLAT_DIMENSION <br>";
    }

    public function setMdt($input) {
        $this->mdt = $input;
    }

    public function getMdt() {
        return $this->mdt;
    }

    public function setMdw($input) {
        $this->mdw = $input;
    }

    public function getMdw() {
        return $this->mdw;
    }

    public function setMdl($input) {
        $this->mdl = $input;
    }

    public function getMdl() {
        return $this->mdl;
    }

    public function setArrDimension($input) {
        $this->arrDimension = $input;
    }

    public function getArrDimension() {
        return $this->arrDimension;
    }

}

class O_DIMENSION extends LEGACY_DIMENSION {

    protected $mdt;
    protected $mdw;
    protected $mdl;
    protected $PHI;
    protected $L;
    protected $arrDimension;

    public function __construct($mdt, $mdw, $mdl) {
        #echo "start instantiate class O_DIMENSION <br>>";
        parent::__construct($mdt, $mdw, $mdl);

        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        $PHI = $mdt;
        $L = $mdl;

        $this->arrDimension = array('PHI' => $PHI, 'L' => $L);
        #echo "End instantiate class O_DIMENSION <br>";
    }

    public function setMdt($input) {
        $this->mdt = $input;
    }

    public function getMdt() {
        return $this->mdt;
    }

    public function setMdw($input) {
        $this->mdw = $input;
    }

    public function getMdw() {
        return $this->mdw;
    }

    public function setMdl($input) {
        $this->mdl = $input;
    }

    public function getMdl() {
        return $this->mdl;
    }

    public function setArrDimension($input) {
        $this->arrDimension = $input;
    }

    public function getArrDimension() {
        return $this->arrDimension;
    }

}

class HEX_DIMENSION extends LEGACY_DIMENSION {

    protected $mdt;
    protected $mdw;
    protected $mdl;
    protected $HEX;
    protected $L;
    protected $arrDimension;

    public function __construct($mdt, $mdw, $mdl) {
        #echo "start instantiate class HEX_DIMENSION <br>";
        parent::__construct($mdt, $mdw, $mdl);

        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        $HEX = $mdt;
        $L = $mdl;

        $this->arrDimension = array('HEX' => $HEX, 'L' => $L);
        #echo "End instantiate class HEX_DIMENSION <br>";
    }

    public function setMdt($input) {
        $this->mdt = $input;
    }

    public function getMdt() {
        return $this->mdt;
    }

    public function setMdw($input) {
        $this->mdw = $input;
    }

    public function getMdw() {
        return $this->mdw;
    }

    public function setMdl($input) {
        $this->mdl = $input;
    }

    public function getMdl() {
        return $this->mdl;
    }

    public function setArrDimension($input) {
        $this->arrDimension = $input;
    }

    public function getArrDimension() {
        return $this->arrDimension;
    }

}

class SS_DIMENSION extends LEGACY_DIMENSION {

    protected $mdt;
    protected $mdw;
    protected $mdl;
    protected $W1;
    protected $W2;
    protected $L;
    protected $arrDimension;

    public function __construct($mdt, $mdw, $mdl) {
        #echo "start instantiate class SS_DIMENSION <br>";
        parent::__construct($mdt, $mdw, $mdl);

        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        $W1 = $mdt;
        $W2 = $mdw;
        $L = $mdl;

        $this->arrDimension = array('W1' => $W1, 'W2' => $W2, 'L' => $L);
        #echo "End instantiate class SS_DIMENSION <br>";
    }

    public function setMdt($input) {
        $this->mdt = $input;
    }

    public function getMdt() {
        return $this->mdt;
    }

    public function setMdw($input) {
        $this->mdw = $input;
    }

    public function getMdw() {
        return $this->mdw;
    }

    public function setMdl($input) {
        $this->mdl = $input;
    }

    public function getMdl() {
        return $this->mdl;
    }

    public function setArrDimension($input) {
        $this->arrDimension = $input;
    }

    public function getArrDimension() {
        return $this->arrDimension;
    }

}

class A_DIMENSION extends LEGACY_DIMENSION {

    protected $mdt;
    protected $mdw;
    protected $mdl;
    protected $T;
    protected $W1;
    protected $W2;
    protected $L;
    protected $arrDimension;

    public function __construct($mdt, $mdw, $mdl) {
        #echo "start instantiate class A_DIMENSION <br>";
        parent::__construct($mdt, $mdw, $mdl);

        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        //Check if mdw contains 'x' or not as separator
        if (stripos($mdw, 'x')) {
            //repair 'x' denominator
            $wMDW = str_replace(" ", "", strtolower($mdw));
            $nMDW = str_replace("x", " x ", strtolower($wMDW));
            //extracts the W1 and W2 from mdw;
            $arrWidth = preg_split("/(\s*x*\s)/", $nMDW);
            #common practice is that W1 must have lower value tha W2;
            sort($arrWidth);
        } else {
            $arrWidth = array(trim($mdw), trim($mdw));
            sort($arrWidth);
        }

        $T = (float) $mdt;
        $W1 = (float) $arrWidth['0'];
        $W2 = (float) $arrWidth['1'];
        $L = (float) $mdl;

        $this->arrDimension = array('T' => $T, 'W1' => $W1, 'W2' => $W2, 'L' => $L);
        #echo "End instantiate class A_DIMENSION <br>";
    }

    public function setMdt($input) {
        $this->mdt = $input;
    }

    public function getMdt() {
        return $this->mdt;
    }

    public function setMdw($input) {
        $this->mdw = $input;
    }

    public function getMdw() {
        return $this->mdw;
    }

    public function setMdl($input) {
        $this->mdl = $input;
    }

    public function getMdl() {
        return $this->mdl;
    }

    public function setArrDimension($input) {
        $this->arrDimension = $input;
    }

    public function getArrDimension() {
        return $this->arrDimension;
    }

}

class HS_DIMENSION extends LEGACY_DIMENSION {

    protected $mdt;
    protected $mdw;
    protected $mdl;
    protected $T;
    protected $W1;
    protected $W2;
    protected $L;
    protected $arrDimension;

    public function __construct($mdt, $mdw, $mdl) {
        #echo "start instantiate class HS_DIMENSION <br>";
        parent::__construct($mdt, $mdw, $mdl);

        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;

        //Check if mdw contains 'x' or not as separator
        if (stripos($mdw, 'x')) {
            //repair 'x' denominator
            $wMDW = str_replace(" ", "", strtolower($mdw));
            $nMDW = str_replace("x", " x ", strtolower($wMDW));
            //extracts the W1 and W2 from mdw;
            $arrWidth = preg_split("/(\s*x*\s)/", $nMDW);
            #common practice is that W1 must have lower value tha W2;
            sort($arrWidth);
        } else {
            $arrWidth = array(trim($mdw), trim($mdw));
            sort($arrWidth);
        }

        $T = (float) $mdt;
        $W1 = (float) $arrWidth['0'];
        $W2 = (float) $arrWidth['1'];
        $L = (float) $mdl;

        $this->arrDimension = array('T' => $T, 'W1' => $W1, 'W2' => $W2, 'L' => $L);
        #echo "End instantiate class HS_DIMENSION <br>";
    }

    public function setMdt($input) {
        $this->mdt = $input;
    }

    public function getMdt() {
        return $this->mdt;
    }

    public function setMdw($input) {
        $this->mdw = $input;
    }

    public function getMdw() {
        return $this->mdw;
    }

    public function setMdl($input) {
        $this->mdl = $input;
    }

    public function getMdl() {
        return $this->mdl;
    }

    public function setArrDimension($input) {
        $this->arrDimension = $input;
    }

    public function getArrDimension() {
        return $this->arrDimension;
    }

}

class HP_DIMENSION extends LEGACY_DIMENSION {

    protected $mdt;
    protected $mdw;
    protected $mdl;
    protected $ID;
    protected $OD;
    protected $L;
    protected $qty;
    protected $arrDimension;

    public function __construct($mdt, $mdw, $mdl) {
        #echo "start instantiate class HP_DIMENSION <br>";
        parent::__construct($mdt, $mdw, $mdl);

        $this->mdt = $mdt;
        $this->mdw = $mdw;
        $this->mdl = $mdl;
        #$this->qty = $qty;

        //check mdt
        $mdtCheck = $this->checkFor_T_ID_OD($mdt);
        switch ($mdtCheck) {
            case 'found T':
                $mdt = strtolower($mdt);
                $resT = trim(str_replace("t", "", strtolower($mdt)));
                #echo "resT = $resT, ";
                break;
            case 'found OD':
                $mdt = strtolower($mdt);
                $resOD = trim(str_replace("od", "", strtolower($mdt)));
                #echo "resOD = $resOD, ";
                break;
            case 'found ID':
                $mdt = strtolower($mdt);
                $resID = trim(str_replace("id", "", strtolower($mdt)));
                #echo "resID = $resID, ";
                break;
            case 'not found':
                $mdt = strtolower($mdt);
                $res1 = trim($mdt);
                #echo "res1 = $res1, ";
                break;
        }
        //check mdw
        $mdwCheck = $this->checkFor_T_ID_OD($mdw);
        switch ($mdwCheck) {
            case 'found T':
                $mdt = strtolower($mdw);
                $resT = trim(str_replace("t", "", strtolower($mdw)));
                #echo "resT = $resT, ";
                break;
            case 'found OD':
                $mdt = strtolower($mdw);
                $resOD = trim(str_replace("od", "", strtolower($mdw)));
                #echo "resOD = $resOD, ";
                break;
            case 'found ID':
                $mdt = strtolower($mdw);
                $resID = trim(str_replace("id", "", strtolower($mdw)));
                #echo "resID = $resID, ";
                break;
            case 'not found':
                $mdt = strtolower($mdw);
                $res2 = trim($mdw);
                #echo "res2 = $res2, ";
                break;
        }

        //note:
        //the logic is to search the T, ID , OD
        //Need to create a logic that extracts T, ID or OD if the other is found.
        //in the case of mdtCheck and mdwCheck is 'not found'
        if ($mdtCheck == 'not found' && $mdwCheck == 'not found') {
            //sort the resvalues
            $arr_res = array($res1, $res2);
            sort($arr_res);
            $res1 = (float)$arr_res['0'];
            $res2 = (float)$arr_res['1'];
            $diffRes = $res2 - $res1;

            $deltaRes = $diffRes / $res2 * 100;
            echo "res1 = $res1, res2 = $res2  diffRes = $diffRes, deltaRes = $deltaRes";
            if (abs($deltaRes) <= 70) {// ID and OD
                $resOD = $res2;
                $resID = $res1;
                $resT = $resOD - $resID;
            } elseif (abs($deltaRes) > 70) {//T & OD
                $resOD = $res2;
                $resT = $res1;
                $resID = $resOD - $resT;
            }
        } elseif ($mdtCheck != 'not found' && $mdwCheck != 'not found') {
            if (isset($resOD)) {
                if (isset($resT)) {
                    $resID = $resOD - $resT;
                } elseif (isset($resID)) {
                    $resT = $resOD - $resID;
                }
            } elseif (isset($resID)) {
                if (isset($resT)) {
                    $resOD = $resID + $resT;
                } elseif (isset($resOD)) {
                    $resT = $resOD + $resID;
                }
            }
        }
        #echo"<br> After Check : resT = $resT, resID = $resID, resOD, = $resOD<br>";

        $ID = (float) $resID;
        $OD = (float) $resOD;
        $L = (float) $mdl;

        $this->arrDimension = array('ID' => $ID, 'OD' => $OD, 'L' => $L);
        #echo "End instantiate class HP_DIMENSION <br>";
    }

    public function checkFor_T_ID_OD($mInput) {
        $chkInput = trim(strtolower($mInput));
        $resInput = preg_replace("/[0-9.\s]/", "", $chkInput);
        #echo "\$chkInput = $chkInput<br>";
        #$test = stripos($chkInput,"od");
        ##echo "\$test = $test<br>";
        switch ($resInput) {
            case 'od':
                $result = "found OD";
                break;
            case 'id':
                $result = "found ID";
                break;
            case 't':
                $result = "found T";
                break;
            default:
                $result = "not found";
                break;
        }
        ##echo "\$result =$result<br>";
        return $result;
    }

    public function setMdt($input) {
        $this->mdt = $input;
    }

    public function getMdt() {
        return $this->mdt;
    }

    public function setMdw($input) {
        $this->mdw = $input;
    }

    public function getMdw() {
        return $this->mdw;
    }

    public function setMdl($input) {
        $this->mdl = $input;
    }

    public function getMdl() {
        return $this->mdl;
    }

    public function setArrDimension($input) {
        $this->arrDimension = $input;
    }

    public function getArrDimension() {
        return $this->arrDimension;
    }

}

?>
