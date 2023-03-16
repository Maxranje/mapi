<?php

class Service_Page_Schedule_Updatearea extends Zy_Core_Service{

    public $serviceSchedule;

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $id     = empty($this->request['id']) ? 0 : intval($this->request['id']);
        $areaId = empty($this->request['area_id']) ? "" : $this->request['area_id'];

        if ($id <= 0){
            throw new Zy_Core_Exception(405, "请求参数错误");
        }

        $roomId = 0;
        if (!empty($areaId) && strpos($areaId, "_") !== false) {
            list($areaId, $roomId) = explode("_", $areaId);
            if ($roomId <= 0 || $areaId <= 0){
                throw new Zy_Core_Exception(405, "校区教室不能为空");
            }    
        }
        
        // 不允许只配置一个值, 要有都有
        if ($roomId <= 0 || $areaId <= 0) {
            $roomId = $areaId = 0;
        }

        $param = array(
            'id'      => $id,
            'room_id' => $roomId,
            'area_id' => $areaId,
        );

        $serviceSchedule = new Service_Data_Schedule();
        $ret = $serviceSchedule->updateArea($param);

        if ($ret === false) {
            throw new Zy_Core_Exception(405, "更新失败, 请重试");
        }
        return array();
    }

}
