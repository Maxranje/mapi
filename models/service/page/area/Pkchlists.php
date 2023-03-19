<?php

class Service_Page_Area_Pkchlists extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }
        $areaId = empty($this->request['area_id']) ? 0 : intval($this->request['area_id']);

        $serviceData = new Service_Data_Area();

        if ($areaId <= 0) {
            $lists = $serviceData->getList();
        }else {
            $lists = $serviceData->getAreaById($areaId);   
            $lists = array($lists);
        }
        return $this->format ($lists);

    }

    public function format($lists) {
        $options = array();
        foreach ($lists as $item) {
            $optionsItem = [
                'label' => $item['name'],
                'value' => $item['id'],
            ];
            foreach ($item['rooms'] as $room) {
                $optionsItem['children'][] = array(
                    'label' => $room['name'],
                    'value' => sprintf("%s_%s", $item["id"], $room["id"]),
                );
            }
            $options[] = $optionsItem;
        }
        return $options;
    }
}