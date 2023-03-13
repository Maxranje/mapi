<?php

class Service_Page_Schedule_Update extends Zy_Core_Service{

    public $serviceSchedule;

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $id = empty($this->request['id']) ? 0 : intval($this->request['id']);
        $date = empty($this->request['date']) ? 0 : intval($this->request['date']);
        $timeRange = empty($this->request['timeRange']) ? "" : $this->request['timeRange'];
        $timeRange = empty($timeRange) ? array() : explode(":", $timeRange);
        $timeDw = empty($this->request['timeDw']) ? 0 : floatval($this->request['timeDw']);

        if ($id <= 0){
            throw new Zy_Core_Exception(405, "请求参数错误");
        }

        $this->serviceSchedule = new Service_Data_Schedule();
        $info = $this->serviceSchedule->getScheduleById($id);
        if (empty($info) || $info['state'] != 1) {
            throw new Zy_Core_Exception(405, "订单已不存在或已结束");
        }

        $subjectId = $teacherId = 0;
        if (strpos($this->request['teacherId'], "_") !== false) {
            list($subjectId, $teacherId) = explode("_", $this->request['teacherId']);
            $subjectId = intval($subjectId);
            $teacherId = intval($teacherId);
        }

        if ($date <= 0){
            throw new Zy_Core_Exception(405, "调整日期格式不正确");
        }

        if (empty($timeRange)){
            throw new Zy_Core_Exception(405, "调整时间格式不正确");
        }

        if ($timeDw <=0 || $timeDw > 4){
            throw new Zy_Core_Exception(405, "调整时长格式不正确");
        }

        $needTimes = array(
            'sts' => $date + ($timeRange[0] * 3600) + ($timeRange[1] * 60),
            'ets' => $date + $timeDw * 3600 + (($timeRange[0] * 3600) + ($timeRange[1] * 60)),
        );

        $needDays = array(
            'sts' => strtotime(date('Ymd', $needTimes['sts'])),
            'ets' => strtotime(date('Ymd', $needTimes['ets'] + 86400)),
        );

        if ($subjectId > 0 && $teacherId > 0) {
            $serviceUser = new Service_Data_User_Profile();
            $userInfo = $serviceUser->getUserInfoByUid($teacherId);
            if (empty($userInfo)) {
                throw new Zy_Core_Exception(405, "无法查到老师信息");
            }
    
            $serviceColumn = new Service_Data_Column();
            $columnInfos = $serviceColumn->getColumnByTSId($teacherId, $subjectId);
            if (empty($columnInfos)) {
                throw new Zy_Core_Exception(405, "无法查到教师绑定信息");
            }

        } else {
            $serviceColumn = new Service_Data_Column();
            $columnInfos = $serviceColumn->getColumnById(intval($info['column_id']));
            if (empty($columnInfos)) {
                throw new Zy_Core_Exception(405, "无法查到教师绑定信息");
            }

        }

        $ret = $this->checkTeacherPk($needTimes, $needDays, $columnInfos['teacher_id'], $info);
        if (!$ret) {
            throw new Zy_Core_Exception(405, "教师时间有冲突, 请查询后在配置");
        }
        
        $serviceGroup = new Service_Data_Group();
        $groupInfos = $serviceGroup->getGroupById($info['group_id']);
        if (empty($groupInfos)) {
            throw new Zy_Core_Exception(405, "无法查到班级信息");
        }

        $ret = $this->checkGroup ($needTimes, $needDays, $groupInfos['id'], $info);
        if (!$ret) {
            throw new Zy_Core_Exception(405, "班级时间有冲突, 请查询后在配置");
        }

        $profile = [
            "id" => $id,
            "column_id" => $columnInfos['id'],
	    'needTimes' => $needTimes,
	    'teacher_id' => $columnInfos['teacher_id'],
        ];

        $ret = $this->serviceSchedule->update($profile);
        if ($ret === false) {
            throw new Zy_Core_Exception(405, "更新失败, 请重试");
        }
        return array();
    }

    private function checkGroup ($needTimes, $needDays, $groupId, $info) {
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
            if ($item['id'] == $info['id']) {
                continue;
            }
            if ($needTimes['sts'] > $item['start_time'] && $needTimes['sts'] < $item['end_time']) {
                return false;
            }
            if ($needTimes['ets'] > $item['start_time'] && $needTimes['ets'] < $item['end_time']) {
                return false;
            }
            if ($needTimes['sts'] < $item['start_time'] && $needTimes['ets'] > $item['end_time']) {
                return false;
            }
            if ($needTimes['sts'] == $item['start_time'] || $needTimes['ets'] == $item['end_time']) {
                return false;
            }
        }
        return true;
    }

    private function checkTeacherPk ($needTimes, $needDays, $teacherId, $info) {
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
        if (empty($list)) {
            return true;
        }
        foreach ($list as $item) {
            if ($item['id'] == $info['id']) {
                continue;
            }
            if ($needTimes['sts'] > $item['start_time'] && $needTimes['sts'] < $item['end_time']) {
                return false;
            }
            if ($needTimes['ets'] > $item['start_time'] && $needTimes['ets'] < $item['end_time']) {
                return false;
            }
            if ($needTimes['sts'] < $item['start_time'] && $needTimes['ets'] > $item['end_time']) {
                return false;
            }
            if ($needTimes['sts'] == $item['start_time'] || $needTimes['ets'] == $item['end_time']) {
                return false;
            }
        }
        return true;
    }
}
