<?php

class Service_Page_Area_Pkarealists extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $serviceData = new Service_Data_Area();

        $lists = $serviceData->getAreaListByConds(array());   
        return $this->format ($lists);

    }

    public function format($lists) {
        $options = array();
        foreach ($lists as $item) {
            $optionsItem = [
                'label' => $item['name'],
                'value' => $item['id'],
            ];
            $options[] = $optionsItem;
        }
        return $options;
    }
}