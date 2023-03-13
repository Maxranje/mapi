<?php

class Service_Page_Schedule_Updatearea extends Zy_Core_Service{

    public $serviceSchedule;

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $id = empty($this->request['id']) ? 0 : intval($this->request['id']);
        $area = empty($this->request['area']) ? "" : $this->request['area'];

        if ($id <= 0){
            throw new Zy_Core_Exception(405, "请求参数错误");
        }

        $this->serviceSchedule = new Service_Data_Schedule();
        $info = $this->serviceSchedule->getScheduleById($id);
        if (empty($info) || $info['state'] != 1) {
            throw new Zy_Core_Exception(405, "订单已不存在或已结束");
        }

        $profile = [
            "id" => $id,
            "area" => $area,
        ];

        $ret = $this->serviceSchedule->updateArea($profile);
        if ($ret === false) {
            throw new Zy_Core_Exception(405, "更新失败, 请重试");
        }
        return array();
    }
}