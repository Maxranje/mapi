<?php

class Service_Page_Student_Update extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        if (empty($this->request['uid'])) {
            throw new Zy_Core_Exception(405, "请求参数错误, 请检查");
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
        
        $serviceData = new Service_Data_User_Profile();
        $userInfo = $serviceData->getUserInfoByUid($this->request['uid']);
        if (empty($userInfo)) {
            throw new Zy_Core_Exception(405, "无法查到相关用户");
        }

        $profile = [
            "type"  => Service_Data_User_Profile::USER_TYPE_STUDENT , 
            "name"  => $this->request['name'] ,
            "nickname"  => $this->request['nickname'] , 
            "phone"  => $this->request['phone']  , 
            "birthplace"  => $this->request['birthplace']  , 
            "avatar" => "",
            "school"  => $this->request['school']  , 
            "graduate"  => $this->request['graduate']  ,
            "sex"  => $this->request['sex'] , 
            "update_time"  => time() , 
            "capital_remark" => empty($this->request['capital_remark']) ? "" : $this->request['capital_remark'],
        ];

        $needStudentCapital = false;
        if (!empty($this->request['capital'])) {
            $needStudentCapital = true;
            $userInfo['student_capital'] += intval($this->request['capital'] * 100);
            $profile['student_capital'] = $userInfo['student_capital'];
            $profile['capital'] = intval($this->request['capital'] * 100);
        }

        $ret = $serviceData->editUserInfo($this->request['uid'], $profile, $needStudentCapital);
        if ($ret === false) {
            throw new Zy_Core_Exception(405, "更新失败, 请重试");
        }
        return array();
    }
}