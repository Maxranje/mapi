<?php

class Dao_User extends Zy_Core_Dao {

    public function __construct() {
        $this->_dbName      = "zy_mapi";
        $this->_table       = "tblUser";
        $this->arrFieldsMap = array(
            "uid"  => "uid" , 
            "type"  => "type" , 
            "name"  => "name" , 
            "nickname"  => "nickname" , 
            "phone"  => "phone" , 
            "avatar" => "avatar",
            "school"  => "school" , 
            "graduate"  => "graduate" , 
            "sex"  => "sex" , 
            "student_capital" => "student_capital",
            "teacher_capital" => "teacher_capital",
            "student_price" => "student_price",
            "create_time"  => "create_time" , 
            "update_time"  => "update_time" , 
            "ext"  => "ext" , 
        );
    }
}