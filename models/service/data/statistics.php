<?php

class Service_Data_Statistics {

    private $daoCapital ;

    public function __construct() {
        $this->daoCapital = new Dao_Capital () ;
    }

    public function getListByConds($conds, $isSimple = true, $indexs = null, $appends = null) {
        $fields = $this->daoCapital->arrFieldsMap;
        $lists = $this->daoCapital->getListByConds($conds, $fields, $indexs, $appends);
        if (empty($lists)) {
            return array();
        }
        return $lists;
    }

    public function getTotalByConds($conds) {
        return  $this->daoCapital->getCntByConds($conds);
    }


    public function edit ($id, $profile) {
        $arrConds = array(
            'id'  => $id,
        );

        $ret = $this->daoCapital->updateByConds($arrConds, $profile);
        return $ret;
    }

}