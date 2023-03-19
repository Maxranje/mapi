<?php

class Service_Page_Admins_Arealists extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $conds = array(
            'type' => Service_Data_User_Profile::USER_TYPE_ADMIN,
        );
        
        $serviceData = new Service_Data_User_Profile();

        $arrAppends[] = 'order by create_time desc';

        $lists = $serviceData->getListByConds($conds, false, NULL, $arrAppends);
        return $this->formatBase($lists);
    }

    private function formatBase($lists) {
        $options = array();
        foreach ($lists as $item) {
            $optionsItem = [
                'label' => $item['nickname'],
                'value' => $item['uid'],
            ];
            $options[] = $optionsItem;
        }
        return $options;
    }
}