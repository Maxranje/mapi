<?php

class Service_Page_Teacher_Update extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $uid = empty($this->request['uid']) ? "" : intval($this->request['uid']);
        $name = empty($this->request['name']) ? "" : $this->request['name'];
        $nickname = empty($this->request['nickname']) ? "" : $this->request['nickname'];
        $phone = empty($this->request['phone']) ? "" : $this->request['phone'];
        $sex = empty($this->request['sex']) ? "M" : $this->request['sex'];
        $capital = empty($this->request['capital']) ? 0 : $this->request['capital'];

        if (empty($name) || empty($nickname) || empty($phone) || !in_array($sex, ['M', "F"])) {
            throw new Zy_Core_Exception(405, "部分参数为空, 请检查");
        }

        $serviceData = new Service_Data_User_Profile();
        $userInfo = $serviceData->getUserInfoByUid($uid);
        if (empty($userInfo)) {
            throw new Zy_Core_Exception(405, "无法查到相关用户");
        }

        $profile = [
            "type"  => Service_Data_User_Profile::USER_TYPE_TEACHER, 
            "name"  => $name , 
            "nickname"  => $nickname , 
            "phone"  => $phone, 
            "avatar" => "",
            "sex"  => $sex, 
            "update_time"  => time() , 
        ];

        $needTeacherCapital = false;
        if (!empty($capital)) {
            $needTeacherCapital = true;
            $userInfo['teacher_capital'] += intval($capital * 100);
            $profile['teacher_capital'] = $userInfo['teacher_capital'];
            $profile['capital'] = intval($capital * 100);
        }

        $ret = $serviceData->editUserInfo($uid, $profile, false, $needTeacherCapital);
        if ($ret === false) {
            throw new Zy_Core_Exception(405, "更新失败, 请重试");
        }
        return array();
    }
}