<?php

class Service_Page_Admins_Create extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        if (empty($this->request['name'])
            || empty($this->request['nickname'])
            || empty($this->request['phone'])) {
            throw new Zy_Core_Exception(405, "部分参数为空, 请检查");
        }

        $serviceData = new Service_Data_User_Profile();
        $userInfo = $serviceData->getUserInfo($this->request['name'], $this->request['phone']);
        if (!empty($userInfo)) {
            throw new Zy_Core_Exception(405, "用户名和手机号已存在");
        }

        $profile = [
            "type"  => Service_Data_User_Profile::USER_TYPE_ADMIN , 
            "nickname"  => $this->request['nickname'] , 
            "name"  => $this->request['name'] , 
            "phone"  => $this->request['phone']  , 
            "avatar" => "",
            "sex"  => "M" , 
            "create_time"  => time() , 
            "update_time"  => time() , 
        ];

        $ret = $serviceData->createUserInfo($profile);
        if ($ret == false) {
            throw new Zy_Core_Exception(405, "创建失败, 请重试");
        }
        return array();
    }
}