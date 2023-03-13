<?php

class Service_Page_Admins_Delete extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        if (empty($this->request['uid'])) {
            throw new Zy_Core_Exception(405, "部分参数为空, 请检查");
        }

        $serviceData = new Service_Data_User_Profile();
        $status = $serviceData->deleteUserInfo($this->request['uid']);
        if (!$status) {
            throw new Zy_Core_Exception(405, "删除错误, 请重试");
        }
        
        return array();
    }
}