<?php

// 排课中查询功能
class Service_Page_Schedule_Pkcalendar extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $groupId    = empty($this->request['group_id']) ? 0 : intval($this->request['group_id']);
        $teacherId  = empty($this->request['teacher_id']) ? 0 : intval($this->request['teacher_id']);
        
        if (empty($groupId) && empty($teacherId)) {
            return array();
        }

        $sts = time() - (6 * 30 * 86400);
        $ets = time() + (6 * 30 * 86400);

        $output = array(
            "type" => "calendar",
            "largeMode" => true,
            "value" => time(),
            "schedules" => array(),
        );   

        $columnIds = array();
        if ($groupId == 0 && $teacherId > 0) {
            $serviceColumn = new Service_Data_Column();
            $columnInfos = $serviceColumn->getColumnByTId($teacherId);
            if (empty($columnInfos)) {
                return $output;
            }
            $columnIds = array_column($columnInfos, "id");
        }

        $serviceData = new Service_Data_Schedule();

        $conds = array();
        if ($groupId > 0) {
            $conds['group_id'] = $groupId;
        } else if (!empty($columnIds)) {
            $conds[] = sprintf('column_id in (%s)', implode(",", $columnIds));
        }

        $conds[] = "start_time >= ".$sts;
        $conds[] = "end_time <= ".$ets;

        $arrAppends[] = 'order by start_time';

        $lists = $serviceData->getListByConds($conds, false, null, $arrAppends);
        if (empty($lists)) {
            return $output;
        }

        $lists = $this->formatSelect($lists);

        $output['schedules'] = $lists;
        return $output;
    }

    private function formatSelect ($lists) {

        $resultList = array();

        // 初始化参数
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

        $serviceUser = new Service_Data_User_Profile();
        $userInfos = $serviceUser->getListByConds(array('uid in ('.implode(',', $teacher_ids).')'));
        $userInfos = array_column($userInfos, null, 'uid');

        $serviceGroup = new Service_Data_Group();
        $groupInfos = $serviceGroup->getListByConds(array('id in ('.implode(",", $groupIds).')'));
        $groupInfos = array_column($groupInfos, null, 'id');

        $serviceSubject = new Service_Data_Subject();
        $subjectInfos = $serviceSubject->getListByConds(array('id in ('.implode(",", $subject_ids).')'));
        $subjectInfos = array_column($subjectInfos, null, 'id');

        $areaInfos = $roomInfos = array();
        if (!empty($areaIds) && !empty($roomIds)) {
            $serviceArea = new Service_Data_Area();
            $roomInfos = $serviceArea->getRoomListByConds(array('id in ('.implode(",", $roomIds).')'));
            $roomInfos = array_column($roomInfos, null, 'id');

            $areaInfos = $serviceArea->getAreaListByConds(array('id in ('.implode(",", $areaIds).')'));
            $areaInfos = array_column($areaInfos, null, 'id');
        }
        
        foreach ($lists as $item) {
            if (empty($columnInfos[$item['column_id']]['teacher_id'])) {
                continue;
            }
            $tid = $columnInfos[$item['column_id']]['teacher_id'];
            if (empty($userInfos[$tid]['nickname'])) {
                continue;
            }
            $tname = $userInfos[$tid]['nickname'];
            if (empty($groupInfos[$item['group_id']]['name'])) {
                continue;
            }
            $gname = $groupInfos[$item['group_id']]['name'];

            if (empty($columnInfos[$item['column_id']]['subject_id'])) {
                continue;
            }
            $sid = $columnInfos[$item['column_id']]['subject_id'];
            $sname = $subjectInfos[$sid]['name'];
            
            // 校区信息
            $areaName = "";
            if (!empty($item['area_id']) 
                && !empty($item['room_id'])
                && !empty($areaInfos[$item['area_id']]['name'])
                && !empty($roomInfos[$item['room_id']]['name'])) {
                $areaName = sprintf("%s_%s", $areaInfos[$item['area_id']]['name'], $roomInfos[$item['room_id']]['name']);
            }

            $end_time = $item['end_time'];
            if (date('H:s', $end_time) == "00:00") {
                $item['end_time'] -= 1;
            }

            $tm = array(
                'startTime' => date('Y-m-d H:i:s', $item['start_time']),
                'endTime' => date('Y-m-d H:i:s', $item['end_time']),
                "className" => $item['state'] == 1 ? "bg-pink-800" : "bg-green-700",
            );

            if ($this->request['group_id'] > 0) {
                $tm['content'] = date('H:i', $item['start_time']) . "-".date('H:i', $end_time). " " .$sname . "-" . $tname;
            } else {
                $tm['content'] = date('H:i', $item['start_time']) . "-".date('H:i', $end_time). " " .$sname . "-" . $gname;
            }

            $resultList[] = $tm;
        }

        return $resultList;
    }
}