<?php

class Service_Page_Account_SignIn extends Zy_Core_Service{

    public function execute (){
        if (empty($this->request['username']) || empty($this->request['passport'])) {
            throw new Zy_Core_Exception(405, "用户名或密码为空");
        }

        $serviceData = new Service_Data_User_Profile();
        $userInfo = $serviceData->getUserInfo($this->request['username'], $this->request['passport']);

        if (empty($userInfo)) {
            throw new Zy_Core_Exception(405, "用户名或密码错误");
        }

        $data = $serviceData->setUserSession($userInfo);
        if (empty($data)) {
            throw new Zy_Core_Exception(405, "系统错误请重试");
        }

        return $data;
    }
}