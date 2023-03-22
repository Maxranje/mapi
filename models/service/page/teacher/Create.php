<?php

class Service_Page_Teacher_Create extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $name = empty($this->request['name']) ? "" : $this->request['name'];
        $nickname = empty($this->request['nickname']) ? "" : $this->request['nickname'];
        $phone = empty($this->request['phone']) ? "" : $this->request['phone'];
        $sex = empty($this->request['sex']) ? "M" : $this->request['sex'];
        $capital = empty($this->request['teacher_capital']) ? 0 : $this->request['teacher_capital'];

        if (empty($name) || empty($nickname) || empty($phone) || !in_array($sex, ['M', "F"])) {
            throw new Zy_Core_Exception(405, "部分参数为空, 请检查");
        }

        $serviceData = new Service_Data_User_Profile();
        $userInfo = $serviceData->getUserInfo($name, $phone);
        if (!empty($userInfo)) {
            throw new Zy_Core_Exception(405, "用户名和手机号已存在");
        }

        $profile = [
            "type"  => Service_Data_User_Profile::USER_TYPE_TEACHER , 
            "name"  => $name, 
            "nickname"  => $nickname, 
            "phone"  => $phone, 
            "avatar" => "",
            "sex"  => $sex, 
            "student_capital" => 0,
            "teacher_capital" => $capital,
            "create_time" => time(),
            "update_time" => time(),
        ];

        $ret = $serviceData->createUserInfo($profile);
        if ($ret == false) {
            throw new Zy_Core_Exception(405, "创建失败, 请重试");
        }
        return array();
    }
}