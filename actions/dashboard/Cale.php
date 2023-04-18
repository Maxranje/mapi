<?php

class Actions_Cale extends Zy_Core_Actions {

    // 执行入口
    public function execute() {
        if (!$this->isLogin() ) {
            $this->redirectLogin();
        }
        if ($this->_userInfo['type'] != Service_Data_User_Profile::USER_TYPE_TEACHER) {
            $this->displayTemplate("error");
        }
        $this->_output['data'] = $this->_request;
        $this->displayTemplate("calendar");
    }

}