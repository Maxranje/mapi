<?php

class Service_Page_Statistics_Update extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $id = empty($this->request['id']) ? 0 : intval($this->request['id']);
        $capital_remark = empty($this->request['capital_remark']) ? "" : strval($this->request['capital_remark']);

        if (empty($id)) {
            throw new Zy_Core_Exception(405, "备注和ID不能为空");
        }

        $serviceStatic = new Service_Data_Statistics();

        $profile = array(
            "capital_remark" => $capital_remark,
        );

        $ret = $serviceStatic->edit($id, $profile);
        if ($ret === false) {
            throw new Zy_Core_Exception(405, "更新失败, 请重试");
        }
        return array();
    }
}