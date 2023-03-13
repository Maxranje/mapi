<?php

class Service_Page_Admins_Update extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        if (empty($this->request['uid'])) {
            throw new Zy_Core_Exception(405, "请求参数错误, 请检查");
        }

        if (empty($this->request['name'])
            || empty($this->request['nickname'])
            || empty($this->request['phone'])) {
            throw new Zy_Core_Exception(405, "部分参数为空, 请检查");
        }

        $serviceData = new Service_Data_User_Profile();
        $userInfo = $serviceData->getUserInfoByUid($this->request['uid']);
        if (empty($userInfo)) {
            throw new Zy_Core_Exception(405, "无法查到相关用户");
        }

        $profile = [
            "type"  => Service_Data_User_Profile::USER_TYPE_ADMIN, 
            "name"  => $this->request['name'] , 
            "nickname"  => $this->request['nickname'] , 
            "phone"  => $this->request['phone']  , 
            "avatar" => "",
            "sex"  => "M" , 
            "update_time"  => time() , 
        ];

        $ret = $serviceData->editUserInfo($this->request['uid'], $profile);
        if ($ret === false) {
            throw new Zy_Core_Exception(405, "更新失败, 请重试");
        }
        return array();
    }
}