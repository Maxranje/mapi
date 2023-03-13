<?php

class Service_Page_Group_Delete extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        if (empty($this->request['id'])) {
            throw new Zy_Core_Exception(405, "2部分参数为空, 请检查");
        }

        $serviceData = new Service_Data_Group();

        // 判断是否还有上课的map
        $status = $serviceData->deleteGroup($this->request['id']);
        if (!$status) {
            throw new Zy_Core_Exception(405, "删除错误, 请重试");
        }
        
        return array();
    }
}