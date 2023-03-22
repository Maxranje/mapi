<?php

class Service_Page_Column_Create extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $uid = empty($this->request['uid']) ? 0 : intval($this->request['uid']);
        $subject_id = empty($this->request['subject_id']) ? 0 : intval($this->request['subject_id']);
        $price = empty($this->request['price']) ? 0 : $this->request['price'];
        $duration = empty($this->request['duration']) ? 0 : intval($this->request['duration']);

        if (empty($uid) || empty($subject_id)) {
            throw new Zy_Core_Exception(405, "部分参数为空, 请检查");
        }

        $serviceData = new Service_Data_Column();
        $column = $serviceData->getColumnByTSId($uid, $subject_id);
        if (!empty($column)) {
            throw new Zy_Core_Exception(405, "已经绑定无需重新绑定");
        }

        $profile = [
            "subject_id"  => $subject_id, 
            "teacher_id"  => $uid, 
            "price"  => intval($price) * 100, 
            "duration"  => $duration, 
            'update_time' => time(),
            'create_time' => time(),
        ];

        $ret = $serviceData->createColumn($profile);
        if ($ret == false) {
            throw new Zy_Core_Exception(405, "创建失败, 请重试");
        }
        return array();
    }
}