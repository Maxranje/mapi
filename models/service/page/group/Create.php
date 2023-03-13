<?php

class Service_Page_Group_Create extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $name = empty($this->request['name']) ? "" : $this->request['name'];
        $descs = empty($this->request['descs']) ? "" : $this->request['descs'];
        $area = empty($this->request['area']) ? "" : $this->request['area'];
        $price = empty($this->request['price']) ? 0 : $this->request['price'];
        $duration = empty($this->request['duration']) || !is_numeric($this->request['duration']) ? 0 : $this->request['duration'];
        $discount = empty($this->request['discount']) ? 0 : intval($this->request['discount']);
        $studentIds = empty($this->request['studentIds']) ? array() : explode(",", $this->request['studentIds']);
        $status = empty($this->request['status']) || !in_array($this->request['status'], [1,2]) ? 1 : intval($this->request['status']);
        
        if (empty($studentIds) || empty($name) || empty($status) || $duration <= 0 || $price <= 0){
            throw new Zy_Core_Exception(405, "部分参数填写不正确, 请检查");
        }

        $serviceData = new Service_Data_Group();
        $profile = [
            "student_ids"  => $studentIds,
            "name" => $name,
            "descs"  =>  $descs, 
            "area" => $area,
            "price" => intval($price * 100),
            "status" => $status,
            'duration' => $duration,
            'discount' => $discount,
            "create_time" => time(),
            "update_time" => time(),
        ];

        $ret = $serviceData->createGroup($profile);
        if ($ret == false) {
            throw new Zy_Core_Exception(405, "创建失败, 请重试");
        }
        return array();
    }
}