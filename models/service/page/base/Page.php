<?php

class Service_Page_Base_Page extends Zy_Core_Service{

    // 查询通过登录uid上课俩表, 
    public function execute () {
        if (!$this->checkTeacher()) {
            return ;  
        }

        $sts = strtotime(date("Y-m-1"));
        $ets = strtotime(date('Y-m-d', strtotime('first day of next month')));

        $total = 0;
        $conds = array(
            "start_time >= " . $sts,
            "end_time < " . $ets,
            "teacher_id" => $this->adption["userid"],
        );
        $serviceSchedule = new Service_Data_Schedule();
        $lists = $serviceSchedule->getListByConds($conds, array('start_time', "end_time"));
        if (!empty($lists)) {
            foreach ($lists as $item) {
                $timeLength = ($item['end_time'] - $item['start_time']) / 3600;
                $total += $timeLength;
            }
        }
        return array("total" => $total);
    }
}