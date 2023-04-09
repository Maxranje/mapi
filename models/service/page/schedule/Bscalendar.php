<?php

// 后台学生管理学生端查询
class Service_Page_Schedule_Bscalendar extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $groupId    = empty($this->request['group_id']) ? 0 : intval($this->request['group_id']);
        $studentId  = empty($this->request['student_id']) ? 0 : intval($this->request['student_id']);

        $sts = time() - (6 * 30 * 86400);
        $ets = time() + (6 * 30 * 86400);

        if ($studentId <= 0) {
            return array();
        }

        $output = array(
            "type" => "calendar",
            "largeMode" => true,
            "value" => time(),
            "schedules" => array(),
        );   

        $serviceData = new Service_Data_Schedule();

        $conds = array();

        if ($groupId <= 0 ) {
            $serviceMap = new Service_Data_User_Group();
            $mapInfo = $serviceMap->getGroupMapBySid($studentId);
            if (empty($mapInfo)) {
                throw new Zy_Core_Exception(405, "学生没有绑定班级, 无法查询");
            }

            $mapInfo = array_column($mapInfo, 'group_id');
            array_walk($mapInfo, function(&$v){return intval($v);});
            $conds[] =sprintf("group_id in (%s)", implode(",", $mapInfo));
        } else {
            $conds['group_id'] = $groupId;
        }

        $conds[] = "start_time >= ".$sts;
        $conds[] = "end_time <= ".$ets;

        $arrAppends[] = 'order by start_time';

        $lists = $serviceData->getListByConds($conds, false , null, $arrAppends);
        if (empty($lists)) {
            return $output;
        }

        $lists = $this->formatSelect($lists);

        $output['schedules'] = $lists;
        return $output;
    }

    private function formatSelect ($lists) {

        $resultList = array();
        $columnIds = array_column($lists, 'column_id');

        // 获取教师名字
        $serviceColumn = new Service_Data_Column();
        $columnInfos = $serviceColumn->getListByConds(array('id in ('.implode(',', $columnIds).')'));
        $teacher_ids = array_column($columnInfos, 'teacher_id');
        $subject_ids = array_column($columnInfos, 'subject_id');
        $columnInfos = array_column($columnInfos, null, 'id');

        $serviceUser = new Service_Data_User_Profile();
        $userInfos = $serviceUser->getListByConds(array('uid in ('.implode(',', $teacher_ids).')'));
        $userInfos = array_column($userInfos, null, 'uid');

        $serviceSubject = new Service_Data_Subject();
        $subjectInfos = $serviceSubject->getListByConds(array('id in ('.implode(",", $subject_ids).')'));
        $subjectInfos = array_column($subjectInfos, null, 'id');
        
        foreach ($lists as $item) {
            if (empty($columnInfos[$item['column_id']])) {
                continue;
            }
            $tid = $columnInfos[$item['column_id']]['teacher_id'];
            $sid = $columnInfos[$item['column_id']]['subject_id'];
            if (empty($userInfos[$tid]['nickname']) || empty($subjectInfos[$sid]['name'])) {
                continue;
            }
            $tname = $userInfos[$tid]['nickname'];
            $sname = $subjectInfos[$sid]['name'];

            $end_time = $item['end_time'];
            if (date('H:s', $end_time) == "00:00") {
                $item['end_time'] -= 1;
            }

            $resultList[] = array(
                'startTime' => date('Y-m-d H:i:s', $item['start_time']),
                'endTime' => date('Y-m-d H:i:s', $item['end_time']),
                'content' => date('H:i', $item['start_time']) . "-".date('H:i', $end_time). " " .$sname . "-" . $tname,
                "className" => $item['state'] == 1 ? "bg-pink-800" : "bg-green-700",
            );
        }

        return $resultList;
    }
}