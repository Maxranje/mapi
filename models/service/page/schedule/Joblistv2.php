<?php
// 后台 学生课表显示能截图的
class Service_Page_Schedule_Joblistv2 extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $studentId  = empty($this->request['student_id']) ? 0 : intval($this->request['student_id']);
        
        if ($studentId <= 0) {
            throw new Zy_Core_Exception(405, "请求参数错误");
        }
        
        $ets = strtotime(date('Y-m-d',  strtotime("+90 day")));
        $sts = strtotime(date('Y-m-d',  strtotime("-15 day")));

        $serviceGroup = new Service_Data_User_Group();
        $groupMapInfo = $serviceGroup->getGroupMapBySid($studentId);
        if (empty($groupMapInfo)) {
            return array();
        }

        $groupIds = array();
        foreach ($groupMapInfo as $info) {
            $groupIds[] = intval($info['group_id']);
        }

        $serviceData = new Service_Data_Schedule();

        $conds = array();
        if (!empty($groupIds)) {
            $conds[] = sprintf('group_id in (%s)', implode(",", $groupIds));
        } 
        if ($sts > 0 && $ets > 0) {
            $conds[] = "start_time >= ".$sts;
            $conds[] = "end_time <= ".$ets;
        }

        $lists = $serviceData->getListByConds($conds);

        echo json_encode($this->formatBase($lists));
        exit;
    }

    private function formatBase ($lists) {
        if (empty($lists)) {
            return array();
        }

        $columnIds = array();
        $groupIds = array();
        foreach ($lists as $item) {
            $columnIds[] = intval($item['column_id']);
            $groupIds[] = intval($item['group_id']);
        }

        // 获取教师名字
        $serviceColumn = new Service_Data_Column();
        $columnInfos = $serviceColumn->getListByConds(array('id in ('.implode(',', $columnIds).')'));
        $subject_ids = array_column($columnInfos, 'subject_id');
        $columnInfos = array_column($columnInfos, null, 'id');

        $serviceSubject = new Service_Data_Subject();
        $subjectInfo = $serviceSubject->getListByConds(array('id in ('.implode(',', $subject_ids).')'));
        $subjectInfo = array_column($subjectInfo, null, 'id');
        
        $result = array();
        foreach ($lists as $key => $item) {
            $tmp = array();
            $tmp['start'] = date("Y-m-d H:i:s",$item['start_time']);
            $tmp['end'] = date("Y-m-d H:i:s",$item['end_time']);
            if (empty($columnInfos[$item['column_id']]['subject_id'])) {
                continue;
            }
            $sid = $columnInfos[$item['column_id']]['subject_id'];
            if (empty($subjectInfo[$sid]['name'])) {
                continue;
            }
            $subjectName = $subjectInfo[$sid]['name'];
            $tt = date("H:i", $item['start_time']) . '-' . date("H:i", $item['end_time']);
            $tmp['title'] = sprintf("%s %s", $tt, $subjectName);

            $result[] = $tmp;            
        }
        return $result;
    }
}