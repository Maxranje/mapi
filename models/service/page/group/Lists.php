<?php

class Service_Page_Group_Lists extends Zy_Core_Service{

    public $serviceGroup;
    public $serviceUsers;
    public $serviceGroupMap;

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $arrOutput = array("lists" => array(), 'total' => 0);

        $pn = empty($this->request['page']) ? 0 : intval($this->request['page']);
        $rn = empty($this->request['perPage']) ? 0 : intval($this->request['perPage']);

        $pn = ($pn-1) * $rn;

        $name = empty($this->request['groupName']) ? "" : $this->request['groupName'];
        $status = empty($this->request['status']) ? 0 : intval($this->request['status']);
        $studentId = empty($this->request['studentId']) ? 0 : intval($this->request['studentId']);
        $studentNickName = empty($this->request['studentNickName']) ? "" : $this->request['studentNickName'];
        $isSelect = empty($this->request['isSelect']) ? false : true;

        $this->serviceGroup = new Service_Data_Group();
        $this->serviceUsers = new Service_Data_User_Profile();
        $this->serviceGroupMap = new Service_Data_User_Group();

        $groupConds = array();
        if (!empty($studentNickName)) {
            $conds = array(
                "nickname like '%".$studentNickName."%'"
            );
            $students = $this->serviceUsers->getListByConds($conds);
            if (empty($students)) {
                return $arrOutput;
            }

            $studentUids = array_column($students, 'uid');
            foreach ($studentUids as &$v)  {
                $v = intval($v);
            }

            $conds = array(
                sprintf("student_id in (%s)", implode(",", $studentUids)),
            );
            $groupMap = $this->serviceGroupMap->getListByConds($conds);
            if (empty($groupMap)) {
                return $arrOutput;
            }

            $groupIds = array_column($groupMap, 'group_id');
            foreach ($groupIds as &$v)  {
                $v = intval($v);
            }

            $groupConds[] = sprintf("id in (%s)", implode(",", $groupIds)); 
        }

        // 选择列表中的, 所以返回和正常返回不一样
        if ($isSelect && $studentId > 0) {
            $conds = array(
                "student_id" => $studentId,
            );

            $groupMap = $this->serviceGroupMap->getListByConds($conds);
            if (empty($groupMap)) {
                return array();
            }

            $groupIds = array_column($groupMap, 'group_id');
            foreach ($groupIds as &$v)  {
                $v = intval($v);
            }

            $groupConds[] = sprintf("id in (%s)", implode(",", $groupIds)); 
        }

        if (!empty($name)) {
            $groupConds[] = "name like '%".$name."%'";
        }

        if (!empty($status)) {
            $groupConds[] = "status = " . $status;
        }

        $arrAppends[] = 'order by id desc';

        if (!$isSelect) {
            $arrAppends[] = "limit {$pn} , {$rn}";
        }

        $lists = $this->serviceGroup->getListByConds($groupConds, false, null, $arrAppends);
        if ($isSelect) {
            return $this->formatSelect($lists);
        }

        $total = $this->serviceGroup->getTotalByConds($groupConds);
        $lists = $this->formatBase($lists);
        return array(
            'rows' => $lists,
            'total' => $total,
        );
    }

    private function formatBase($lists) {
        if (empty($lists)) {
            return array();
        }

        $groupIds = $groupMapInfo = $studentUids = array();
        foreach ($lists as $item) {
            $groupIds[] = intval($item['id']);
        }
        $conds = array(
            sprintf("group_id in (%s)", implode(",", $groupIds))
        );
        $groupMap = $this->serviceGroupMap->getListByConds($conds);
        foreach ($groupMap as $key => $item) {
            if (!isset($groupMapInfo[$item['group_id']])) {
                $groupMapInfo[$item['group_id']] = array();
            }
            $groupMapInfo[$item['group_id']][] = intval($item['student_id']);
            $studentUids[] = intval($item['student_id']);
        }

        $conds = array(
            sprintf("uid in (%s)", implode(",", $studentUids))
        );
        $studetntInfos = $this->serviceUsers->getListByConds($conds);
        $studetntInfos = array_column($studetntInfos, null, 'uid');

        $serviceSchedule = new Service_Data_Schedule();
        $scheduleCount = $serviceSchedule->getLastDuration($groupIds);

        foreach ($lists as &$item) {
            $gInfo = empty($groupMapInfo[$item['id']]) ? array() : $groupMapInfo[$item['id']];
            $item['studentNames'] = array();
            $item['studentCount'] = count($gInfo);
            if (!empty($gInfo)) {
                foreach ($gInfo as $index => $values) {
                    if (!empty($studetntInfos[$values])) {
                        $item['students'][] = $studetntInfos[$values];
                        $item['studentNames'][] = $studetntInfos[$values]['nickname'];
                    }
                }
                if (!empty($item['students'])) {
                    $item['studentNames'] = implode(",", $item['studentNames']);     
                }
            }
            if (isset($scheduleCount[$item['id']])) {
                $item['lastDuration'] = $item['duration'] - $scheduleCount[$item['id']];
            } else {
                $item['lastDuration'] = $item['duration'];
            }
            $item['lastDurationInfo'] = $item['lastDuration'] . "课时";
        }
        return $lists;
    }

    private function formatSelect($lists) {
        $options = array();
        foreach ($lists as $item) {
            $options[] = array(
                'label' => $item['name'],
                'value' => $item['id'],
            );
        }
        return array('options' => array_values($options));
    }
}