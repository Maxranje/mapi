<?php

class Actions_Listsv3 extends Zy_Core_Actions {

    // 执行入口
    public function execute() {
        if (!$this->isLogin()) {
            $this->error(405, "请先登录");
        }

        $serivce = new Service_Page_Schedule_Listsv3 ($this->_request, $this->_userInfo);
        $this->_data = $serivce->execute();
        return $this->_data;
    }

}