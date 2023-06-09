<?php

class Service_Page_Statistics_Lists extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $pn = empty($this->request['page']) ? 0 : intval($this->request['page']);
        $rn = empty($this->request['perPage']) ? 0 : intval($this->request['perPage']);

        $pn = ($pn-1) * $rn;

        $serviceStatic = new Service_Data_Statistics();

        $conds = array();
        if (!empty($this->request['uid'])) {
            $conds['uid'] = intval($this->request['uid']);
        }

        if (!empty($this->request['category'])) {
            $conds['category'] = intval($this->request['category']);
        }

        if (!empty($this->request['daterangee'])) {
            list($start_time, $end_time) = explode(",", $this->request['daterangee']);
            $conds[] = "create_time >= ". $start_time;
            $conds[] = "create_time <= ". ($end_time + 1);
        }

        $arrAppends[] = 'order by id desc';
        if (empty($this->request['export'])) {
            $arrAppends[] = "limit {$pn} , {$rn}";
        }

        $lists = $serviceStatic->getListByConds($conds, false, NULL, $arrAppends);
        $total = $serviceStatic->getTotalByConds($conds);

        $lists = $this->formatBase($lists);

        if (!empty($this->request['export'])) {
            $data = $this->formatExcel($lists);
            Zy_Helper_Utils::exportExcelSimple("Statistics", $data['title'], $data['lists']);
        }

        return array(
            'rows' => $lists,
            'total' => $total,
        );
    }

    private function formatBase($lists) {

        if (empty($lists)) {
            return array();
        }
        $uids = array_column($lists, 'uid');
        $serviceUsers = new Service_Data_User_Profile();
        $userInfos = $serviceUsers->getListByConds(array('uid in ('.implode(",", $uids).')'));
        $userInfos = array_column($userInfos, null, "uid");

        $opuids = array_column($lists, 'operator');
        $opUserInfos = $serviceUsers->getListByConds(array('uid in ('.implode(",", $opuids).')'));
        $opUserInfos = array_column($opUserInfos, null, "uid");

        foreach ($lists as &$item) {
            if (isset($userInfos[$item['uid']])) {
                $item['name'] = $userInfos[$item['uid']]['nickname'];
                $item['typeInfo'] = $userInfos[$item['uid']]['type'] == Service_Data_User_Profile::USER_TYPE_STUDENT ? "学生" : "教师";
            }
            if (isset($opUserInfos[$item['operator']])) {
                $item['operatorName'] = $opUserInfos[$item['operator']]['name'];
            }
            if ($item['category'] == Service_Data_Schedule::CATEGORY_TEACHER_RECHARGE) {
                $item['categoryInfo'] = "教师充值";
            } else if ($item['category'] == Service_Data_Schedule::CATEGORY_TEACHER_PAID) {
                $item['categoryInfo'] = "教师收入";
            } else if ($item['category'] == Service_Data_Schedule::CATEGORY_STUDENT_PAID) {
                $item['categoryInfo'] = "学生消耗(班级定价)";
            } else if ($item['category'] == Service_Data_Schedule::CATEGORY_STUDENT_RECHARGE) {
                $item['categoryInfo'] = "学生充值";
            } else if ($item['category'] == Service_Data_Schedule::CATEGORY_STUDENT_PAID_PERSONAL) {
                $item['categoryInfo'] = "学生消耗(个人定价)";
            }
            $item['capitalInfo']  = ($item['capital'] / 100) . "元";
            $item['create_time'] = date('Y年m月d日 H:i:s', $item['create_time']);
            $item['update_time'] = date('Y年m月d日 H:i:s', $item['update_time']);
        }
        return $lists;
    }

    private function formatExcel($lists) {
        $result = array(
            'title' => array('UID', '用户名', '用户类型', '场景', '金额', '备注', '操作员', '创建时间'),
            'lists' => array(),
        );
        
        foreach ($lists as $item) {
            if (empty($item['name'])) {
                continue;
            }
            $tmp = array(
                $item['uid'],
                $item['name'],
                $item['typeInfo'],
                $item['categoryInfo'],
                $item['capitalInfo'],
                $item['capital_remark'],
                $item['operatorName'],
                $item['create_time'],
            );
            $result['lists'][] = $tmp;
        }
        return $result;

    }
}