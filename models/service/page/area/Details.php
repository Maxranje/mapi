<?php

class Service_Page_Area_Details extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限");
        }

        $areaId = empty($this->request['area_id']) ? 0 : intval($this->request['area_id']);
        $dateTime = empty($this->request['datetime']) ? 0 : intval($this->request['datetime']);

        if ($areaId <= 0 && $dateTime <= 0 ) {
            return array();
        }

        if ($areaId <= 0 || $dateTime <= 0 ) {
            throw new Zy_Core_Exception(405, "校区或时间必须进行选择");
        }

        $serviceArea = new Service_Data_Area();
        $roomInfos = $serviceArea->getRoomListByAid($areaId);
        if (empty($roomInfos)) {
            throw new Zy_Core_Exception(405, "该校区下暂时没有教室");
        }
        $roomInfos = array_column($roomInfos, null, "id");

        $sts = $dateTime + (8 * 3600);
        $ets = $dateTime + (20 * 3600);

        $conds = array(
            "area_id = ". $areaId,
            "start_time >= " . $sts,
            "state=1", 
            "end_time <= " . $ets
        );

        $serviceSchedule = new Service_Data_Schedule();
        $scheduleList = $serviceSchedule->getListByConds($conds);

        $roomList = array();
        $teacherIds = array();
        $groupIds = array();
        foreach ($roomInfos as $room) {
            $roomList[$room['id']] = array(
                "name" => $room['name'],
                "schedule" => array(),
            );
            if (empty($scheduleList)) {
                continue;
            }
            foreach ($scheduleList as $schedule) {
                if ($schedule['room_id'] == $room['id'] && $schedule['area_id'] == $room['area_id']) {
                    $roomList[$room['id']]['schedule'][] = array(
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                        "teacher_id" => $schedule['teacher_id'],
                        "group_id" => $schedule['group_id'],
                    );
                    $teacherIds[intval($schedule['teacher_id'])] = intval($schedule['teacher_id']);
                    $groupIds[intval($schedule['group_id'])] = intval($schedule['group_id']);
                }
            }
        }

        $teacherIds = array_values($teacherIds);
        $groupIds = array_values($groupIds);

        $serviceUser = new Service_Data_User_Profile();
        $userInfos = $serviceUser->getUserInfoByUids($teacherIds);
        $userInfos = array_column($userInfos, null, "uid");

        $serviceGroup = new Service_Data_Group();
        $groupInfos = $serviceGroup->getListByConds(array(sprintf("id in (%s)", implode(",", $groupIds))));
        $groupInfos = array_column($groupInfos, null, "id");

        return $this->format($roomList, $userInfos, $groupInfos);    
    }

    public function format ($roomList, $userInfos, $groupInfos) {
        $output = array();

        foreach($roomList as $id => $room) {
            $output[] = array(
                "type"=> "html",
                "html"=> $room['name'],
            );
            $defulatIndex = 8; 
            $defulatMax = 14.5;
            for ($index = 0; $index < 2; $index++) {
                $tmp = array(
                    "type"=>"grid",
                    "className"=>"m-b m-t-sm",
                    "columns"=> [],
                );
                for($i = $defulatIndex; $i < $defulatMax; $i+=0.5) {
                    $t1 = sprintf("%s:%s", intval($i),  ($i * 10 % 10 == 5) ? "30" : "00");
                    $column = array(
                        "columnClassName"=> "text-sm text-secondary py-1 text-center text-white  border-l-4 border-gray-500 rounded-base shadow-lg",
                        "body"=> [
                            array(
                                "type"=> "tooltip-wrapper",
                                "content"=> "无排课",
                                "tags" => 123,
                                "body"=> $t1 . "-29:00"
                            )
                        ]
                    );
                    $tmp['columns'][] = $column;
                }
                $output[] = $tmp;
                $defulatIndex += 6;
                $defulatMax += 6;
            }


            // if (!empty($room['schedule'])) {
            //     foreach ($room["schedule"] as $item) {
            //         $t = ceil($item['end_time']  - $item['start_time']) /1800;
            //         $tname = empty($userInfos[$item['teacher_id']]) ? "" : $userInfos[$item['teacher_id']]['nickname'];
            //         $gname = empty($groupInfos[$item['group_id']]) ? "" : $groupInfos[$item['group_id']]['name'];
            //         for ($i= 0; $i < $t; $i++){
            //             $tt = date("H", $item['start_time']) + $i;
            //             $tmp['columns'][$tt]['columnClassName'] = "text-xs py-1 text-center text-white  border-l-4 border-gray-500 bg-yellow-500 rounded-base shadow-lg";
            //             $tmp['columns'][$tt]['body'][0]['content'] = sprintf("%s-%s", $tname, $gname);
            //         }
            //     }
            // }
            
            $output[] = array("type" => "divider");
        }
        return $output;
    }
}