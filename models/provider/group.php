<?php

class Dao_Group extends Zy_Core_Dao {

    public function __construct() {
        $this->_dbName      = "zy_mapi";
        $this->_table       = "tblGroup";
        $this->arrFieldsMap = array(
            "id"  => "id" , 
            "name"  => "name", 
            "descs"  => "descs" ,
            "area" => "area",
            "status" => "status",
            "price" => "price",
            "discount" => "discount",
            "duration" => "duration" ,
            "create_time"  => "create_time" , 
            "update_time"  => "update_time" , 
            "ext"  => "ext" , 
        );
    }
}