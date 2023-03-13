<?php

class Service_Page_Subject_Create extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        if (empty($this->request['category1'])
            || empty($this->request['category2'])
            || empty($this->request['name'])){
            throw new Zy_Core_Exception(405, "部分参数为空, 请检查");
        }

        $serviceData = new Service_Data_Subject();
        $subjectInfo = $serviceData->getListByConds($this->request['name']);
        if (!empty($subjectInfo)) {
            throw new Zy_Core_Exception(405, "科目已存在, 请检查");
        }

        if (empty($this->request['descs'])) {
            $this->request['descs'] = "";
        }

        $profile = [
            "category1"  => $this->request['category1'], 
            "category2"  => $this->request['category2'], 
            "name"  => $this->request['name'] , 
            "descs"  =>  $this->request['descs'], 
            "create_time" => time(),
            "update_time" => time(),
        ];

        $ret = $serviceData->createSubject($profile);
        if ($ret == false) {
            throw new Zy_Core_Exception(405, "创建失败, 请重试");
        }
        return $ret;
    }
}