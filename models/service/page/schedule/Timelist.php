<?php

class Service_Page_Schedule_Timelist extends Zy_Core_Service{

    public $serviceSchedule;

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $type = empty($this->request['type']) ? 0 : intval($this->request['type']);
        $week = empty($this->request['week']) ? "" : $this->request['week'];
        $week = empty($week) ? array() : explode(",", $week);
        $length = empty($this->request['length']) ? 0 : intval($this->request['length']);
        $startDay = empty($this->request['startDay']) ? 0 : intval($this->request['startDay']);
        $defaultTime = empty($this->request['defaultTime']) ? array() : $this->request['defaultTime'];

        if (!in_array($type, array(1,2))){
            throw new Zy_Core_Exception(405, "每周/隔周必须选一个");
        }

        if (empty($week) || !empty(array_diff($week, array("1","2","3","4","5","6","7")))){
            throw new Zy_Core_Exception(405, "必须选择周几");
        }

        if ($length <= 0 || $length >20){
            throw new Zy_Core_Exception(405, "课时长度必须20内且大于0");
        }

        if (empty($defaultTime)) {
            throw new Zy_Core_Exception(405, "必须选择一个默认时间");
        }

        if ($startDay <= 0) {
            throw new Zy_Core_Exception(405, "起始时间不能为空");
        }
        
        if (empty($defaultTime['timeDw']) || empty($defaultTime['timeRange'])) {
            throw new Zy_Core_Exception(405, "时间格式错误, 存在空情况");
        }
        $range = explode(":", $defaultTime['timeRange']);

        $needTimes = array(
            'sts' => ($range[0] * 3600) + ($range[1] * 60),
            'ets' => $defaultTime['timeDw'] * 3600 + ($range[0] * 3600) + ($range[1] * 60),
        );

        if (empty($needTimes)) {
            throw new Zy_Core_Exception(405, "时间格式错误, 请检查");
        }

        if (count($week) > $length) {
            throw new Zy_Core_Exception(405, "配置中一周所上的课时不能大于总课时数, 请检查");
        }

        // 计算具体时间
        $needTimes = $this->initParamsTime($needTimes, $startDay, $type, $week, $length) ;
        $ret = $this->checkParamsTime($needTimes) ;
        if (!$ret) {
            throw new Zy_Core_Exception(405, "保存的时间有冲突, 请查询后在配置");
        }
        
        return  $this->formatBase($needTimes, $defaultTime);
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

    private function initParamsTime ($needTimes, $startDay, $type, $week, $length) {
        $result = array();
        $typeTime = $type * 7 * 86400;
        $startWeekDay = strtotime("next Monday", $startDay) - 7 * 86400; // 本周第一天
        sort($week);
        $i = 0;
        while ($i < $length){
            foreach ($week as $w) {
                $sts = ($w - 1) * 86400 + $startWeekDay + $needTimes['sts'];
                $ets = ($w - 1) * 86400 + $startWeekDay + $needTimes['ets'];
                if ($sts <= time()) {
                    continue;
                }
                $result[] = array(
                    'sts' => $sts,
                    'ets' => $ets,
                );
                $i++;
                if ($i >= $length) {
                    break;
                }
            }
            $startWeekDay += $typeTime;
        }
        return $result;
    }

    private function formatBase ($needTimes, $defaultTime) {
        $result = array(
            "type"=> "combo",
            "name"=> "times" . rand(1, 1000000),
            "multiple"=> true,
            "items"=> [
                array(
                    "type"=> "input-date",
                    "name"=> "date",
                    "onlyLeaf"=>true
                ),
                array(
                    "type"=> "input-time",
                    "name"=> "timeRange",
                    "format"=> "HH:mm",
                    "onlyLeaf"=>true
                ),
                array(
                    "type"=> "input-number",
                    "name"=> "timeDw",
                    "min" => 0.5,
                    "max" => 4,
                    "step" => 0.5,
                    "precision" => 1,
                    "showSteps"=>true,
                    "suffix" => " hour"
                )
            ],
            "value" => array(),
        );

        foreach ($needTimes as $time) {
            $v = array(
                'date' => strtotime(date('Ymd', $time['sts'])),
                'timeRange' => $defaultTime['timeRange'],
                'timeDw' => $defaultTime['timeDw'],
            );
            $result['value'][] = $v;
        }
        return $result;
    }

}