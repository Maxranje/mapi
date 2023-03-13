<?php

class Actions_Joblist extends Zy_Core_Actions {

    // 执行入口
    public function execute() {
        if (!$this->isLogin()) {
            $this->error(405, "请先登录");
        }

        $serivce = new Service_Page_Schedule_Joblist ($this->_request, $this->_userInfo);
        $this->_data = $serivce->execute();
        return $this->_data;
    }

}