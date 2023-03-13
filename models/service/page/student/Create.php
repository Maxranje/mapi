<?php

class Service_Page_Student_Create extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        if (empty($this->request['name'])
            || empty($this->request['phone'])
            || empty($this->request['nickname'])) {
            throw new Zy_Core_Exception(405, "部分参数为空, 请检查");
        }

        if (!is_numeric($this->request['phone']) 
            || strlen($this->request['phone']) < 6
            || strlen($this->request['phone']) > 12) {
            throw new Zy_Core_Exception(405, "手机号参数错误, 请检查");
        }

        if (empty($this->request['school'])) {
            $this->request['school'] = "";
        }

        if (empty($this->request['graduate'])) {
            $this->request['graduate'] = "";
        }

        if (empty($this->request['sex'])) {
            $this->request['sex'] = "M";
        }

        if (empty($this->request['capital_remark'])) {
            $this->request['capital_remark'] = "";
        }

        if (empty($this->request['student_capital'])) {
            $this->request['student_capital'] = 0;
        }

        if (empty($this->request['student_price']) 
            || !is_numeric($this->request['student_price'])
            || $this->request['student_price'] <= 0) {
            $this->request['student_price'] = 0;
        }

        $serviceData = new Service_Data_User_Profile();
        $userInfo = $serviceData->getUserInfo($this->request['name'], $this->request['phone']);
        if (!empty($userInfo)) {
            throw new Zy_Core_Exception(405, "用户名和手机号已存在");
        }

        $profile = [
            "type"  => Service_Data_User_Profile::USER_TYPE_STUDENT , 
            "name"  => $this->request['name'] , 
            "nickname" => $this->request["nickname"],
            "phone"  => $this->request['phone']  , 
            "avatar" => "",
            "school"  => $this->request['school']  , 
            "graduate"  => $this->request['graduate']  ,
            "sex"  => $this->request['sex'] , 
            "capital_remark" => $this->request['capital_remark'],
            "student_capital" => $this->request['student_capital'],
            "student_price" => $this->request['student_price'],
            "teacher_capital" => 0,
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