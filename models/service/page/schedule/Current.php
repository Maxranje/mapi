<?php

class Service_Page_Schedule_Current extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $serviceData = new Service_Data_Schedule();
        $conds = array(
            'start_time <' . time(),
        );
        $rn = empty($this->request['perPage']) ? 5 : intval($this->request['perPage']);

        $count = $serviceData->getTotalByConds($conds);
        $pn = intval($count / $rn) + 1;
        return array('serverPn' => $pn);
    }
}