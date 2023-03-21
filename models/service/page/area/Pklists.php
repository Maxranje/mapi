<?php

class Service_Page_Area_Pklists extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $serviceData = new Service_Data_Area();

        $lists = $serviceData->getList();   
        return $this->format ($lists);

    }

    public function format($lists) {
        $options = array();
        foreach ($lists as $item) {
            $optionsItem = [
                'label' => $item['name'],
                'value' => $item['id'],
                "children" => array(),
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