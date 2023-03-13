<?php

class Service_Page_Base_Menu extends Zy_Core_Service{

    // 通用组件, 页面获取信息
    public function execute () {

        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $menuConf = Zy_Helper_Config::getAppConfig("menu");
        $menuAdmin = json_decode($menuConf['admin'], true);

        if ($this->checkSuper()) {
            $menuSuper = json_decode($menuConf['super'], true);
            $menuAdmin = array_merge($menuAdmin, $menuSuper);
        }

        $menuBase = array(
            'pages' => array(
                array(
                    'label' => "Home",
                    'url' => '/',
                    "redirect" => "/index/1"
                ),
                array(
                    'children' => $menuAdmin,
                ),
            ),
        );

        return $menuBase;
    }
}