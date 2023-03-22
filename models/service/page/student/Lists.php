<?php

class Service_Page_Student_Lists extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限操作");
        }

        $pn = empty($this->request['page']) ? 1 : intval($this->request['page']);
        $rn = empty($this->request['perPage']) ? 20 : intval($this->request['perPage']);

        $pn = ($pn-1) * $rn;

        $conds = array(
            'type' => Service_Data_User_Profile::USER_TYPE_STUDENT,
        );

        if (!empty($this->request['isSelect']) && !empty($this->request['term'])) {
            $conds[] = "nickname like '%".$this->request['term']."%'";
        }

        if (!empty($this->request['studentName'])) {
            $conds[] = "name like '%".$this->request['studentName']."%'";
        }

        if (!empty($this->request['studentNickName'])) {
            $conds[] = "nickname like '%".$this->request['studentNickName']."%'";
        }

        if (!empty($this->request['studentPhone'])) {
            $conds['phone'] = intval($this->request['studentPhone']);
        }
        
        $serviceData = new Service_Data_User_Profile();

        $arrAppends[] = 'order by uid desc';

        if (empty($this->request['isSelect'])) {
            $arrAppends[] = "limit {$pn} , {$rn}";
        }

        $lists = $serviceData->getListByConds($conds, false, NULL, $arrAppends);
        $total = $serviceData->getTotalByConds($conds);

        if (!empty($this->request['isSelect'])) {
            return $this->formatSelect ($lists);
        }

        return array(
            'rows' => $lists,
            'total' => $total,
        );
    }

    private function formatSelect ($lists) {
        $options = array();
        foreach ($lists as $item) {
            $options[] = array(
                'label' => sprintf("%s 【%s - %s】", $item['nickname'] , $item['school'], $item['graduate']),
                'value' => $item['uid'],
            );
        }
        $values = array();
        if(!empty($this->request['group_id'])) { 
            $serviceGroupMap = new Service_Data_User_Group();
            $miList = $serviceGroupMap->getGroupMapByGid(intval($this->request['group_id']));
            if (!empty($miList)) {
                foreach ($miList as $t) {
                    $values[]= $t['student_id'];
                }
            }
        }
        return array('options' => $options, 'value' => $values);
    }
}