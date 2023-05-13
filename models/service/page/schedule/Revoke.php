<?php

class Service_Page_Schedule_Revoke extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $id = empty($this->request['id']) ? 0 : intval($this->request['id']);

        if ($id <= 0){
            throw new Zy_Core_Exception(405, "请求参数错误");
        }

        $serviceSchedule = new Service_Data_Schedule();
        $info = $serviceSchedule->getScheduleById($id);
        if (empty($info) || $info['state'] != 0) {
            throw new Zy_Core_Exception(405, "排课记录查询失败或该记录未结算");
        }

        $serviceStatic = new Service_Data_Statistics();
        $capitalList = $serviceStatic->getListByJobId($id);
        if (empty($capitalList)) {
            throw new Zy_Core_Exception(405, "撤销失败, 没有消费记录");
        }

        $uids = array();
        foreach ($capitalList as $item) {
            $uids[intval($item['uid'])] = intval($item['uid']);
        }
        $uids = array_values($uids);

        $serviceUser = new Service_Data_User_Profile();
        $userInfos = $serviceUser->getUserInfoByUids($uids);
        $userInfos = array_column($userInfos, null, 'uid');

        $params = array(
            'id' => $id,
            'list' => array(),
        );
        foreach ($capitalList as $item) {
            if (!empty($userInfos[$item['uid']])) {
                $params['list'][] = array(
                    'uid' => $item['uid'],
                    'type'=> $item['type'],
                    "capital" => $item['capital'],
                );
            }
        }

        if (empty($params['list'])) {
            throw new Zy_Core_Exception(405, "获取教师和学生信息失败, 请检查班级中相关人员是否存在");
        }

        $ret = $serviceSchedule->revoke($params);
        if ($ret === false) {
            throw new Zy_Core_Exception(405, "撤销失败, 请重试");
        }

        // 检查时候有课时冲突
        $needDays = array(
            'sts' => strtotime(date('Ymd', $info['start_time'])),
            'ets' => strtotime(date('Ymd', $info['end_time'] + 86400)),
        );
        $needTimes = array(
            array(
                "sts" => $info['start_time'],
                "ets" => $info['end_time'],
            ),
        );

        $ret = $serviceSchedule->checkGroup($needTimes, $needDays, $info['group_id'], $info);
        if (!empty($ret)) {
            throw new Zy_Core_Exception(0, "班级时间有冲突, 请检查班级时间, 系统查询到其中一个排课编号ID=" . $ret['id']. " 仅做参考");
        }

        $ret = $serviceSchedule->checkTeacherPk($needTimes, $needDays, $info['teacher_id'], $info);
        if (!empty($ret)) {
            throw new Zy_Core_Exception(0, "教师时间有冲突, 请检查教师时间, 系统查询到其中一个排课编号ID=" . $ret['id']. " 仅做参考");
        }

        // 排查教室 (3.15线上不管)
        if (!empty($info['area_id']) && !empty($info['room_id']) && $info['area_id'] != 3 && $info['room_id'] != 15) {
            $ret = $serviceSchedule->checkRoom ($needTimes, $needDays, $info);
            if (!empty($ret)) {
                throw new Zy_Core_Exception(0, "教室时间有冲突, 请检查教室占用, 系统查询到其中一个排课编号ID=" . $ret['id']. " 仅做参考");
            }
        }

        return array();
    }
}
