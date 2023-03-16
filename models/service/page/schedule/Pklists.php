<?php

// 排课列表
class Service_Page_Schedule_PkLists extends Zy_Core_Service{

    public $weekName = [
        1 => "周一",
        2 => "周二",
        3 => "周三",
        4 => "周四",
        5 => "周五",
        6 => "周六",
        7 => "周日",
        0 => "周日",
    ];

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $pn = empty($this->request['page']) ? 1 : intval($this->request['page']);
        $rn = empty($this->request['perPage']) ? 20 : intval($this->request['perPage']);

        $pn = ($pn-1) * $rn;

        $groupId = empty($this->request['group_ids']) ? "" : strval($this->request['group_ids']);
        $teacherId = empty($this->request['teacher_id']) ? 0 : intval($this->request['teacher_id']);
        $areaId = empty($this->request['area_id']) ? 0 : intval($this->request['area_id']);
        $daterange = empty($this->request['daterange']) ? "" : $this->request['daterange'];
        $status = empty($this->request['status']) || !in_array($this->request['status'], [1,2]) ? 0 : $this->request['status'];

        list($sts, $ets) = empty($daterange) ? array(0,0) : explode(",", $daterange);

        $columnIds = array();
        if ($teacherId > 0) {
            $serviceColumn = new Service_Data_Column();
            $columnIds = $serviceColumn->getColumnByTId($teacherId);
            if (empty($columnIds)) {
                return array();
            }
            $columnIds = array_column($columnIds, "id");
        }

        $serviceData = new Service_Data_Schedule();

        $conds = array();
        if (!empty($groupId)) {
            $conds[] = sprintf("group_id in (%s)", $groupId);
        } 
        if (!empty($columnIds)) {
            $conds[] = sprintf('column_id in (%s)', implode(",", $columnIds));
        }
        if ($areaId > 0) {
            $conds[] = "area_id = ".$areaId;
        }
        if ($sts > 0) {
            $conds[] = "start_time >= ".$sts;
        }
        if ($ets > 0) {
            $conds[] = "end_time <= ".($ets + 1);
        }
        if ($status > 0) {
            $conds[] = "state = " . ($status == 1 ? 0 : 1) ;
        }
        $arrAppends[] = 'order by start_time';
        if (empty($this->request['export'])) {
            $arrAppends[] = "limit {$pn} , {$rn}";
        }

        $lists = $serviceData->getListByConds($conds, false, NULL, $arrAppends);
        $total = $serviceData->getTotalByConds($conds);

        $sum_duration = 0;
        $lists = $this->formatBase($lists, $sum_duration);
        
        if (!empty($this->request['export'])) {
            $data = $this->formatExcel($lists);
            Zy_Helper_Utils::exportExcelSimple("Schedule", $data['title'], $data['lists']);
        }

        $result = array(
            'rows' => $lists,
            'total' => $total,
            'sum_duration' => $sum_duration > 0 ? $sum_duration . "小时" : "-",
        );
        return $result;
    }

    private function formatBase ($lists, &$sum_duration) {
        if (empty($lists)) {
            return array();
        }

        // 初始化参数
        $operatorIds    = array();
        $columnIds      = array();
        $groupIds       = array();
        $areaIds        = array();
        $roomIds        = array();
        foreach ($lists as $item) {
            $columnIds[] = intval($item['column_id']);
            $groupIds[] = intval($item['group_id']);
            $operatorIds[] = intval($item['operator']);
            
            // 获取校区id
            if (!empty($item['area_id']) && !empty($item['room_id'])) {
                $areaIds[] = intval($item['area_id']);
                $roomIds[] = intval($item['room_id']);
            }
        }

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

        $operators = $serviceUser->getListByConds(array('uid in ('.implode(',', $operatorIds).')'));
        $operators = array_column($operators, null, 'uid');

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
        
        $sum_duration = 0;
        foreach ($lists as $key => &$item) {
            $item["week_time"] = $this->weekName[date("w", $item['start_time'])];
            $item['time_day'] = strtotime(date("Y-m-d", $item['start_time']));
            $item['time_hm'] = date("H:i", $item['start_time']);
            $item['time_len'] = ($item['end_time'] - $item['start_time']) / 3600;
            $item['range_time'] = date('Y-m-d H:i', $item['start_time']) . "~".date('H:i', $item['end_time']);
            $item['duration'] = (($item['end_time'] - $item['start_time']) / 3600);
            $sum_duration += $item['duration'];
            if (empty($this->request['export'])) {
                $item['duration'] .= "小时";
            }
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            if (empty($columnInfos[$item['column_id']]['teacher_id'])
                || empty($columnInfos[$item['column_id']]['subject_id'])) {
                unset($lists[$key]);
                continue;
            }
            $tid = $columnInfos[$item['column_id']]['teacher_id'];
            $sid = $columnInfos[$item['column_id']]['subject_id'];
            if (empty($userInfos[$tid]['nickname'])
                || empty($subjectInfo[$sid]['name'])
                || empty($groupInfos[$item['group_id']]['name'])) {
                unset($lists[$key]);
                continue;
            }
            $item['s_t_id'] = $sid . "_" . $tid;
            $item['teacher_name'] = $userInfos[$tid]['nickname'];
            $item['subject_name'] = $subjectInfo[$sid]['name'];
            $item['group_name'] = $groupInfos[$item['group_id']]['name'];
            
            // 校区信息
            $item['area_name'] = "";
            $item['a_r_id'] = "";
            if (!empty($item['area_id']) 
                && !empty($item['room_id'])
                && !empty($areaInfos[$item['area_id']]['name'])
                && !empty($roomInfos[$item['room_id']]['name'])) {
                $item['a_r_id'] = sprintf("%s_%s", $item['area_id'], $item['room_id']);
                $item['area_name'] = sprintf("%s(%s)", $areaInfos[$item['area_id']]['name'], $roomInfos[$item['room_id']]['name']);
            }

            $item['operator_name']= $operators[$item['operator']]['nickname'];
            $item['stateInfo'] = $item['state'] == 1 ? "未结算" : "已结算";
            
	    }
	    $lists = array_values($lists);
        return $lists;
    }

    private function formatExcel($lists) {
        $result = array(
            'title' => array('ID', '教师名', '班级名', '课程名', '校区', '排课人员', '状态', '星期', '时长', '时间', '创建时间'),
            'lists' => array(),
        );
        
        foreach ($lists as $item) {
            $tmp = array(
                $item['id'],
                $item['teacher_name'],
                $item['group_name'],
                $item['subject_name'],
                $item['area_name'],
                $item['operator_name'],
                $item['stateInfo'],
                $item['week_time'],
                $item['time_len'],
                $item['range_time'],
                $item['create_time'],
            );
            $result['lists'][] = $tmp;
        }
        return $result;

    }
}
