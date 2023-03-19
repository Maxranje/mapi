<?php

class Service_Page_Area_Aslists extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $noarea = empty($this->request['noarea']) ? 0 : intval($this->request['noarea']);

        $serviceData = new Service_Data_Area();

        $lists = $serviceData->getList();   
        return $this->format ($lists, $noarea);

    }

    public function format($lists, $noarea) {
        $options = array();
        if ($noarea == 1) {
            $options[] = [
                'label' => "无校区",
                'value' => -1,
            ];
            $options[] = [
                'label' => "无教室",
                'value' => -2,
            ];
        }
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