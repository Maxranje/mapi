<?php

class Service_Page_Base_Menu extends Zy_Core_Service{

    // 通用组件, 页面获取信息
    public function execute () {

        if (!$this->checkAdmin()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        $menuConf = Zy_Helper_Config::getAppConfig("menu");

        $superMenuIds = array(52, 6 , 7, 8, 91);

        if (!$this->checkSuper()) {
            foreach ($menuConf as $key => $item) {
                if (in_array($item['id'], $superMenuIds)) {
                    unset($menuConf[$key]);
                    continue;
                }
                if (!empty($item['children'])) {
                    foreach ($item['children'] as $ck => $citem) {
                        if (in_array($citem['id'], $superMenuIds)) {
                            unset($item['children'][$ck]);
                            continue;
                        }
                    }
                    if (empty($item['children'])) {
                        unset($menuConf[$key]);
                        continue;
                    }
                    $item['children'] = array_values($item['children']);
                    $menuConf[$key] = $item;
                }
            }
            $menuConf = array_values($menuConf);
        }

        $menuBase = array(
            'pages' => array(
                array(
                    'label' => "Home",
                    'url' => '/',
                    "redirect" => "/index/1"
                ),
                array(
                    'children' => $menuConf,
                ),
            ),
        );

        return $menuBase;
    }
}