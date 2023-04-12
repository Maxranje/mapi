<?php

// 自定义
class Service_Page_Schedule_Createv2 extends Zy_Core_Service{

    public $serviceSchedule;

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $groupId    = empty($this->request['group_id']) ? 0 : intval($this->request['group_id']);
        $teacherId  = empty($this->request['teacher_id']) ? "" : $this->request['teacher_id'];
        $times      = empty($this->request['times']) ? array() : $this->request['times'];
        $areaId     = empty($this->request['area_id']) ? 0 : intval($this->request['area_id']);

        // 教师信息获取
        if (empty($teacherId) || strpos($teacherId, "_") === false) {
            throw new Zy_Core_Exception(405, "教师不能为空");
        }
        list($subjectId, $teacherId) = explode("_", $teacherId);
        if ($groupId <= 0 || $teacherId <= 0 || $subjectId <= 0){
            throw new Zy_Core_Exception(405, "教师和班级不能为空");
        }

        if (empty($times)) {
            throw new Zy_Core_Exception(405, "必须选择一个默认时间");
        }

        $needTimes = array();
        $needDays  = array();
        foreach ($times as $time) {
            if (empty($time['date']) || empty($time['timeRange']) || empty($time['timeDw']) ) {
                throw new Zy_Core_Exception(405, "时间格式错误, 存在空情况");
            }
            $range = explode(":", $time['timeRange']);
            $sts = $time['date'] + ($range[0] * 3600) + ($range[1] * 60);
            $ets = $time['timeDw'] * 3600 + $sts;

            $needTimes[] = array(
                'sts' => $sts,
                'ets' => $ets,
            );
            $needDays[] = strtotime(date("Ymd", $sts));
            $needDays[] = strtotime(date("Ymd", $ets)) + 86400;
        }
        
        if (empty($needTimes)) {
            throw new Zy_Core_Exception(405, "时间格式错误, 请检查");
        }

        $ret = $this->checkParamsTime($needTimes) ;
        if (!$ret) {
            throw new Zy_Core_Exception(405, "保存的时间有冲突, 请查询后在配置");
        }

        $needDays = array(
            'sts' => min($needDays),
            'ets' => max($needDays),
        );

        $serviceUser = new Service_Data_User_Profile();
        $userInfo = $serviceUser->getUserInfoByUid(intval($teacherId));
        if (empty($userInfo)) {
            throw new Zy_Core_Exception(405, "无法查到老师信息");
        }
        
        $serviceGroup = new Service_Data_Group();
        $groupInfos = $serviceGroup->getGroupById($groupId);
        if (empty($groupInfos)) {
            throw new Zy_Core_Exception(405, "无法查到班级信息");
        }
        $areaop = intval($groupInfos['area_op']);

        $serviceColumn = new Service_Data_Column();
        $columnInfos = $serviceColumn->getColumnByTSId($teacherId, $subjectId);
        if (empty($columnInfos)) {
            throw new Zy_Core_Exception(405, "无法查到教师绑定信息");
        }

        if ($areaId > 0) {
            $serviceArea = new Service_Data_Area();
            $areaInfo = $serviceArea->getAreaById($areaId, false);
            if (empty($areaInfo)) {
                throw new Zy_Core_Exception(405, "无法查到校区信息");
            }
        }

        $this->serviceSchedule = new Service_Data_Schedule();

        $ret = $this->checkGroup ($needTimes, $needDays, $groupId);
        if (!$ret) {
            throw new Zy_Core_Exception(405, "班级时间有冲突, 请查询后在配置");
        }

        $ret = $this->checkTeacherPk($needTimes, $needDays, $teacherId);
        if (!$ret) {
            throw new Zy_Core_Exception(405, "老师时间有冲突(排课或锁时间), 请查询后在配置");
        }

        $profile = [
            "column_id" => $columnInfos['id'],
            'group_id' => $groupInfos['id'],
            'needTimes' => $needTimes,
            'area_op'  => $areaop,
            'teacher_id' => $teacherId,
            'area_id' => $areaId,
            "state" => 1,
        ];

        $ret = $this->serviceSchedule->create($profile);
        if ($ret === false) {
            throw new Zy_Core_Exception(405, "添加失败, 请重试");
        }
        return array();
    }

    private function checkParamsTime ($needTimes) {
        $times = $needTimes;
        foreach ($times as $k1 => $item) {
            foreach ($needTimes as $k2 => $t) {
                // 比较, 开始时间大于存开始时间,  结束时间小于存结束时间
                if ($k1 == $k2) {
                    continue;
                }
                if ($t['sts'] > $item['sts'] && $t['sts'] < $item['ets']) {
                    return false;
                }
                if ($t['ets'] > $item['sts'] && $t['ets'] < $item['ets']) {
                    return false;
                }
                if ($t['sts'] < $item['sts'] && $t['ets'] > $item['ets']) {
                    return false;
                }
                if ($t['sts'] == $item['sts'] || $t['ets'] == $item['ets']) {
                    return false;
                }
            }
        }
        return true;
    }

    private function checkGroup ($needTimes, $needDays, $groupId) {
        $conds= array(
            sprintf('start_time >= %d', $needDays['sts']),
            sprintf('end_time <= %d', $needDays['ets']),
            'group_id' => $groupId,
            'state' => 1,
        );
        $list = $this->serviceSchedule->getListByConds($conds);
        if ($list === false) {
            return false;
        }
        if (empty($list)) {
            return true;
        }

        foreach ($list as $item) {
            foreach ($needTimes as $t) {
                if ($t['sts'] > $item['start_time'] && $t['sts'] < $item['end_time']) {
                    return false;
                }
                if ($t['ets'] > $item['start_time'] && $t['ets'] < $item['end_time']) {
                    return false;
                }
                if ($t['sts'] < $item['start_time'] && $t['ets'] > $item['end_time']) {
                    return false;
                }
                if ($t['sts'] == $item['start_time'] || $t['ets'] == $item['end_time']) {
                    return false;
                }
            }
        }
        return true;
    }

    private function checkTeacherPk ($needTimes, $needDays, $teacherId) {
        $conds= array(
            sprintf('start_time >= %d', $needDays['sts']),
            sprintf('end_time <= %d', $needDays['ets']),
            'teacher_id' => intval($teacherId),
            'state' => 1,
        );
        $list = $this->serviceSchedule->getListByConds($conds);
        if ($list === false) {
            return false;
        }
        $list = empty($list) ? array() : $list;

        // 锁定的时间
        $conds= array(
            sprintf('start_time >= %d', $needDays['sts']),
            sprintf('end_time <= %d', $needDays['ets']),
            'uid' => intval($teacherId),
        );
        $serviceLock = new Service_Data_Lock();
        $locks = $serviceLock->getListByConds($conds);
        if ($locks === false) {
            return false;
        }
        $locks = empty($locks) ? array() : $locks;

        // 2个记录合并
        $list = array_merge($list, $locks);
        if (empty($list)) {
            return true;
        }

        foreach ($list as $item) {
            foreach ($needTimes as $t) {
                if ($t['sts'] > $item['start_time'] && $t['sts'] < $item['end_time']) {
                    return false;
                }
                if ($t['ets'] > $item['start_time'] && $t['ets'] < $item['end_time']) {
                    return false;
                }
                if ($t['sts'] < $item['start_time'] && $t['ets'] > $item['end_time']) {
                    return false;
                }
                if ($t['sts'] == $item['start_time'] || $t['ets'] == $item['end_time']) {
                    return false;
                }
            }
        }
        return true;
    }
}