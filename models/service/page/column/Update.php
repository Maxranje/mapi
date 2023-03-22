<?php

class Service_Page_Column_Update extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $teacherId = empty($this->request['teacherId']) ? 0 : intval($this->request['teacherId']);
        $subjectId = empty($this->request['subjectId']) ? 0 : intval($this->request['subjectId']);
        $price = empty($this->request['price']) ? 0 : floatval($this->request['price']);

        if ($teacherId <= 0 || $subjectId <= 0) {
            throw new Zy_Core_Exception(405, "部分参数为空, 请检查");
        }

        $serviceSubject = new Service_Data_Subject();
        $subjectInfo = $serviceSubject->getSubjectById($subjectId);
        if (empty($subjectInfo)) {
            throw new Zy_Core_Exception(405, "科目不存在");
        }

        $serviceColumn = new Service_Data_Column();
        $column = $serviceColumn->getColumnByTSId($teacherId, $subjectId);
        if (empty($column)) {
            throw new Zy_Core_Exception(405, "课程不存在");
        }


        $conds = array(
            'teacher_id' => $teacherId,
            'subject_id' => $subjectId,
        );
        $profile = [
            "price"  => intval($price) * 100, 
            "discount"  => 0,
            'update_time' => time(),
        ];
        $ret = $serviceColumn->editColumn($conds, $profile);

        if ($ret === false) {
            throw new Zy_Core_Exception(405, "更新失败, 请重试");
        }
        return array();
    }
}