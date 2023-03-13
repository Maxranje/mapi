<?php

class Service_Data_User_Group {

    private $daoGroup ;

    public function __construct() {
        $this->daoGroup = new Dao_Groupmap () ;
    }

    public function getGroupMapByGid ($id){
        $arrConds = array(
            'group_id'  => $id,
        );

        $arrFields = $this->daoGroup->arrFieldsMap;

        $Group = $this->daoGroup->getListByConds($arrConds, $arrFields);
        if (empty($Group)) {
            return array();
        }

        return $Group;
    }

    public function getGroupMapBySid ($id){
        $arrConds = array(
            'student_id'  => $id,
        );

        $arrFields = $this->daoGroup->arrFieldsMap;

        $Group = $this->daoGroup->getListByConds($arrConds, $arrFields);
        if (empty($Group)) {
            return array();
        }

        return $Group;
    }

    public function getListByConds ($conds){
        $Group = $this->daoGroup->getListByConds($conds, $this->daoGroup->arrFieldsMap);
        if (empty($Group)) {
            return array();
        }

        return $Group;
    }
}