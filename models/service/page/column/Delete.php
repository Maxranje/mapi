<?php

class Service_Page_Column_Delete extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        if (empty($this->request['teacher_id']) || empty($this->request['subject_id'])) {
            throw new Zy_Core_Exception(405, "部分参数为空, 请检查");
        }

        $serviceData = new Service_Data_Column();

        // 判断是否还有上课的map

        $status = $serviceData->deleteColumn(intval($this->request['teacher_id']), intval($this->request['subject_id']));
        if (!$status) {
            throw new Zy_Core_Exception(405, "删除错误, 请重试");
        }
        
        return array();
    }
}