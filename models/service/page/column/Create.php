<?php

class Service_Page_Column_Create extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $teacherId = empty($this->request['teacher_id']) ? 0 : intval($this->request['teacher_id']);
        $subjectId = empty($this->request['subject_id']) ? 0 : intval($this->request['subject_id']);
        $price      = empty($this->request['price']) ? 0 : $this->request['price'];
        $duration   = empty($this->request['duration']) ? 0 : intval($this->request['duration']);

        if ($teacherId <= 0 || $subjectId <= 0) {
            throw new Zy_Core_Exception(405, "无法获取教师或科目, 请检查");
        }

        $serviceData = new Service_Data_Column();
        $column = $serviceData->getColumnByTSId($teacherId, $subjectId);
        if (!empty($column)) {
            throw new Zy_Core_Exception(405, "已经绑定无需重新绑定");
        }

        $profile = [
            "subject_id"    => $subjectId, 
            "teacher_id"    => $teacherId, 
            "price"         => intval($price) * 100, 
            "duration"      => $duration, 
            'update_time'   => time(),
            'create_time'   => time(),
        ];

        $ret = $serviceData->createColumn($profile);
        if ($ret == false) {
            throw new Zy_Core_Exception(405, "创建失败, 请重试");
        }
        return array();
    }
}