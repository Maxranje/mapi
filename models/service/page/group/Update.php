<?php

class Service_Page_Group_Update extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $id = empty($this->request['id']) ? 0 : intval($this->request['id']);
        $name = empty($this->request['name']) ? "" : $this->request['name'];
        $descs = empty($this->request['descs']) ? "" : $this->request['descs'];
        $price = empty($this->request['price']) ? 0 : $this->request['price'];
        $duration = empty($this->request['duration']) || !is_numeric($this->request['duration']) ? 0 : $this->request['duration'];
        $discount = empty($this->request['discount']) ? 0 : intval($this->request['discount']);
        $areaop = empty($this->request['area_op']) ? 0 : intval($this->request['area_op']);
        $studentIds = empty($this->request['studentIds']) ? array() : 
            (is_array($this->request['studentIds']) ? $this->request['studentIds'] : explode(",", $this->request['studentIds']));
        $status = empty($this->request['status']) || !in_array($this->request['status'], [1,2]) ? 1 : intval($this->request['status']);

        if ($id <= 0 || empty($name) || empty($studentIds)) {
            throw new Zy_Core_Exception(405, "请求参数错误, 请检查");
        }

        $serviceData = new Service_Data_Group();
        $groupInfo = $serviceData->getGroupById($id);
        if (empty($groupInfo)) {
            throw new Zy_Core_Exception(405, "无法查到相关数据");
        }

        $serviceGroupMap = new Service_Data_User_Group();
        $studentLists = $serviceGroupMap->getListByConds(array('group_id' => $id));
        $oldStudentIds= array_column($studentLists, "student_id");

        $profile = [
            "diff2_student" => array_diff($studentIds, $oldStudentIds),
            "diff1_student" => array_diff($oldStudentIds, $studentIds),
            "student_ids" => $studentIds,
            "name" => $name,
            "descs"  =>  $descs, 
            "area_op" => $areaop,
            "status" => $status,
            "price" => intval($price * 100),
            'duration' => $duration,
            'discount' => $discount,
            "update_time" => time(),
        ];

        $ret = $serviceData->editGroup($id, $profile);
        if ($ret === false) {
            throw new Zy_Core_Exception(405, "更新失败, 请重试");
        }
        return array();
    }
}