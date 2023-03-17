<?php
// 学生端显示
class Service_Page_Schedule_Fscalendar extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkStudent() && !$this->checkTeacher()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $uid = $this->adption['userid'];
        $type = $this->adption['type'];

        $ets = strtotime(date('Y-m-d',  strtotime("+60 day")));
        $sts = strtotime(date('Y-m-d',  strtotime("-60 day")));

        $columnIds = array();
        if ($type == Service_Data_User_Profile::USER_TYPE_TEACHER) {
            $serviceColumn = new Service_Data_Column();
            $columnInfos = $serviceColumn->getColumnByTId($uid);
            if (empty($columnInfos)) {
                return array();
            }

            foreach ($columnInfos as $key => $info) {
                $columnIds[] = intval($info['id']);
            }
        }

        $groupIds = array();
        if ($type == Service_Data_User_Profile::USER_TYPE_STUDENT) {
            $serviceGroup = new Service_Data_User_Group();
            $groupMapInfo = $serviceGroup->getGroupMapBySid($uid);
            if (empty($groupMapInfo)) {
                return array();
            }

            foreach ($groupMapInfo as $info) {
                $groupIds[] = intval($info['group_id']);
            }
        }

        $serviceData = new Service_Data_Schedule();

        $conds = array();
        if (!empty($groupIds)) {
            $conds[] = sprintf('group_id in (%s)', implode(",", $groupIds));
        } 
        if (!empty($columnIds)) {
            $conds[] = sprintf('column_id in (%s)', implode(",", $columnIds));
        }
        if ($sts > 0 && $ets > 0) {
            $conds[] = "start_time >= ".$sts;
            $conds[] = "end_time <= ".$ets;
        }

        $lists = $serviceData->getListByConds($conds);
        echo json_encode($this->formatBase($lists, $type));
        exit;
    }

    private function formatBase ($lists, $type) {
        if (empty($lists)) {
            return array();
        }

        $columnIds      = array();
        $groupIds       = array();
        $areaIds        = array();
        $roomIds        = array();
        foreach ($lists as $item) {
            $columnIds[intval($item['column_id'])] = intval($item['column_id']);
            $groupIds[intval($item['group_id'])] = intval($item['group_id']);
            
            // 获取校区id
            if (!empty($item['area_id']) && !empty($item['room_id'])) {
                $areaIds[intval($item['area_id'])] = intval($item['area_id']);
                $roomIds[intval($item['room_id'])] = intval($item['room_id']);
            }
        }
        $columnIds = array_values($columnIds);
        $groupIds = array_values($groupIds);
        $areaIds = array_values($areaIds);
        $roomIds = array_values($roomIds);

        // 获取教师名字
        $serviceColumn = new Service_Data_Column();
        $columnInfos = $serviceColumn->getListByConds(array('id in ('.implode(',', $columnIds).')'));
        $teacher_ids = array_column($columnInfos, 'teacher_id');
        $subject_ids = array_column($columnInfos, 'subject_id');
        $columnInfos = array_column($columnInfos, null, 'id');

        $serviceSubject = new Service_Data_Subject();
        $subjectInfo = $serviceSubject->getListByConds(array('id in ('.implode(',', $subject_ids).')'));
        $subjectInfo = array_column($subjectInfo, null, 'id');

        $serviceUser = new Service_Data_User_Profile();
        $userInfos = $serviceUser->getListByConds(array('uid in ('.implode(',', $teacher_ids).')'));
        $userInfos = array_column($userInfos, null, 'uid');

        $serviceGroup = new Service_Data_Group();
        $groupInfos = $serviceGroup->getListByConds(array('id in ('.implode(",", $groupIds).')'));
        $groupInfos = array_column($groupInfos, null, 'id');

        $areaInfos = $roomInfos = array();
        if (!empty($areaIds) && !empty($roomIds)) {
            $serviceArea = new Service_Data_Area();
            $roomInfos = $serviceArea->getRoomListByConds(array('id in ('.implode(",", $roomIds).')'));
            $roomInfos = array_column($roomInfos, null, 'id');

            $areaInfos = $serviceArea->getAreaListByConds(array('id in ('.implode(",", $areaIds).')'));
            $areaInfos = array_column($areaInfos, null, 'id');
        }

        
        $result = array();
        foreach ($lists as $key => $item) {
            $tmp = array();
            $tmp['start'] = date("Y-m-d H:i:s",$item['start_time']);
            $tmp['end'] = date("Y-m-d H:i:s",$item['end_time']);
            if (empty($columnInfos[$item['column_id']]['teacher_id'])
                || empty($columnInfos[$item['column_id']]['subject_id'])) {
                continue;
            }
            $tid = $columnInfos[$item['column_id']]['teacher_id'];
            $sid = $columnInfos[$item['column_id']]['subject_id'];
            if (empty($userInfos[$tid]['nickname'])
                || empty($subjectInfo[$sid]['name'])
                || empty($groupInfos[$item['group_id']]['name'])) {
                continue;
            }
            $teacherName = $userInfos[$tid]['nickname'];
            $subjectName = $subjectInfo[$sid]['name'];
            $groupName = $groupInfos[$item['group_id']]['name'];

            // 校区信息
            $areaName = "";
            if (!empty($item['area_id']) 
                && !empty($item['room_id'])
                && !empty($areaInfos[$item['area_id']]['name'])
                && !empty($roomInfos[$item['room_id']]['name'])) {
                $areaName = sprintf("%s_%s", $areaInfos[$item['area_id']]['name'], $roomInfos[$item['room_id']]['name']);
            }

            if ($type == Service_Data_User_Profile::USER_TYPE_TEACHER) {
		        $duration = (($item['end_time'] - $item['start_time']) / 3600) . "小时";
                $tmp['title'] = sprintf("%s %s %s %s", $duration, $groupName, $subjectName, $areaName);    
		        if ($item['state'] == 0) {
                    $tmp['title'] .= "(已结算)";
                }
            } else {
                $tmp['title'] = sprintf("%s %s %s", $subjectName, $teacherName, $areaName);
            }

            $result[] = $tmp;            
        }
        return $result;
    }
}
