<?php

include_once 'variables.inc.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of materialprice
 *
 * @author User
 */
class MaterialPrice {

    //put your code here
    protected $thickness = NULL;
    protected $PHI = NULL;
    protected $DIA = NULL;
    protected $ID = NULL;
    protected $width = NULL;
    protected $W1 = NULL;
    protected $W2 = NULL;
    protected $price = NULL;
    protected $density = NULL;
    protected $maxweight = NULL;
    protected $maxlength = NULL;
    protected $maxprice = NULL;
    protected $looselength = NULL;
    protected $looseprice = NULL;
    protected $cuttingcharges = NULL;

    public function __construct($arr_matprice = array()) {
        $data = $arr_matprice;
        extract($data, EXTR_PREFIX_ALL, "data"); //extracts $data as "data_[varname]"
        if (isset($data_thickness)) {
            $this->setThickness($data_thickness);
        }
        /**/ echo "thickness = " . $this->getThickness() . '<br>';
        if (isset($data_PHI)) {
            $this->setPHI($data_PHI);
        }
        /**/ echo "PHI = " . $this->getPHI() . '<br>';
        if (isset($data_DIA)) {
            $this->setDIA($data_DIA);
        }
        /**/ echo "DIA = " . $this->getDIA() . '<br>';
        if (isset($data_ID)) {
            $this->setID($data_ID);
        }
        /**/ echo "ID = " . $this->getID() . '<br>';
        if (isset($data_width)) {
            $this->setWidth($data_width);
            if (stripos($data_width, 'x')) {
                $new_data_width = str_replace(" ", "", $data_width);
                $arr_data_width = explode("x", $new_data_width);
                $this->setW1($arr_data_width['0']);
                $this->setW2($arr_data_width['1']);
            }
        }
        /**/ echo "Width = " . $this->getWidth() . '<br>';
        if (isset($data_W1)) {
            $this->setW1($data_W1);
        }
        /**/ echo "W1 = " . $this->getW1() . '<br>';
        if (isset($data_W2)) {
            $this->setW2($data_W2);
        }
        /**/ echo "W2 = " . $this->getW2() . '<br>';
        if (isset($data_price)) {
            $this->setPrice($data_price);
        }
        /**/ echo "Price = " . $this->getPrice() . '<br>';
        if (isset($data_density)) {
            $this->setDensity($data_density);
        }
        /**/ echo "Density = " . $this->getDensity() . '<br>';
        if (isset($data_maxlength)) {
            $this->setMaxlength($data_maxlength);
        }
        /**/ echo "Max Length = " . $this->getMaxlength() . '<br>';
        if (isset($data_maxweight)) {
            $this->setMaxweight($data_maxweight);
        }
        /**/ echo "Max Weight = " . $this->getMaxweight() . '<br>';
        if (isset($data_maxprice)) {
            $this->setMaxprice($data_maxprice);
        }
        /**/ echo "Max Price = " . $this->getMaxprice() . '<br>';
        if (isset($data_looselength)) {
            $this->setLooselength($data_looselength);
        }
        /**/ echo "Loose Length = " . $this->getLooselength() . '<br>';
        if (isset($data_looseprice)) {
            $this->setLooseprice($data_looseprice);
        }
        /**/ echo "Loose Price = " . $this->getLooseprice() . '<br>';
        if (isset($data_cuttingcharges)) {
            $this->setCuttingcharges($data_cuttingcharges);
        }
        /**/ echo "Cutting Charges = " . $this->getCuttingcharges() . '<br>';
    }

    public function setThickness($input) {
        $this->thickness = $input;
    }

    public function getThickness() {
        $output = $this->thickness;
        return $output;
    }

    public function setPHI($input) {
        $this->PHI = $input;
    }

    public function getPHI() {
        $output = $this->PHI;
        return $output;
    }

    public function setDIA($input) {
        $this->DIA = $input;
    }

    public function getDIA() {
        $output = $this->DIA;
        return $output;
    }

    public function setID($input) {
        $this->ID = $input;
    }

    public function getID() {
        $output = $this->ID;
        return $output;
    }

    public function setWidth($input) {
        $this->width = $input;
    }

    public function getWidth() {
        $output = $this->width;
        return $output;
    }

    public function setW1($input) {
        $this->W1 = $input;
    }

    public function getW1() {
        $output = $this->W1;
        return $output;
    }

    public function setW2($input) {
        $this->W2 = $input;
    }

    public function getW2() {
        $output = $this->W2;
        return $output;
    }

    public function setPrice($input) {
        $this->price = $input;
    }

    public function getPrice() {
        $output = $this->price;
        return $output;
    }

    public function setDensity($input) {
        $this->density = $input;
    }

    public function getDensity() {
        $output = $this->density;
        return $output;
    }

    public function setMaxweight($input) {
        $this->maxweight = $input;
    }

  public function getMaxweight() {
        $output = $this->maxweight;
        return $output;
    }

    public function setMaxlength($input) {
        $this->maxlength = $input;
    }

    public function getMaxlength() {
        $output = $this->maxlength;
        return $output;
    }

    public function setMaxprice($input) {
        $this->maxprice = $input;
    }

    public function getMaxprice() {
        $output = $this->maxprice;
        return $output;
    }

    public function setLooselength($input) {
        $this->looselength = $input;
    }

    public function getLooselength() {
        $output = $this->looselength;
        return $output;
    }

    public function setLooseprice($input) {
        $this->looseprice = $input;
    }

    public function getLooseprice() {
        $output = $this->looseprice;
        return $output;
    }

    public function setCuttingcharges($input) {
        $this->cuttingcharges = $input;
    }

    public function getCuttingcharges() {
        $output = $this->cuttingcharges;
        return $output;
    }

}

class PriceCalculation extends MaterialPrice {

    protected $materialcode;
    protected $com; //branch code;
    protected $cid; //customer code;
    protected $specialTbl; //table name for special price customers

    public function __construct($matcode, $com, $cid) {
        $this->materialcode = $matcode;
        $this->com = strtolower(trim($com));
        $this->cid = $cid;
        $this->specialTbl = $this->materialcode . "_" . $this->com . "_" . $this->cid;
    }

}
