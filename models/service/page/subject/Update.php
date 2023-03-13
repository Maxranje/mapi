<?php

class Service_Page_Subject_Update extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $id = empty($this->request['id']) ? 0 : intval($this->request['id']);
        $name = empty($this->request['name']) ? "" : $this->request['name'];
        $category1 = empty($this->request['category1']) ? "" : $this->request['category1'];
        $category2 = empty($this->request['category2']) ? "" : $this->request['category2'];
        $descs = empty($this->request['descs']) ? "" : $this->request['descs'];

        if ($id <= 0) {
            throw new Zy_Core_Exception(405, "请求参数错误, 请检查");
        }

        if (empty($name) || empty($category1) || empty($category2)) {
            throw new Zy_Core_Exception(405, "部分参数为空, 请检查");
        }

        $serviceData = new Service_Data_Subject();
        $subjectInfo = $serviceData->getSubjectById($id);
        if (empty($subjectInfo)) {
            throw new Zy_Core_Exception(405, "无法查到相关数据");
        }

        $profile = [
            "category1"  => $category1, 
            "category2"  => $category2, 
            "name"  => $name, 
            "descs"  =>  $descs, 
            "update_time" => time(),
        ];

        $ret = $serviceData->editSubject($id, $profile);
        if ($ret === false) {
            throw new Zy_Core_Exception(405, "更新失败, 请重试");
        }
        return array();
    }
}