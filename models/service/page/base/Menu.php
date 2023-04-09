<?php

class Service_Page_Base_Menu extends Zy_Core_Service{

    // 通用组件, 页面获取信息
    public function execute () {

        $pages = $this->getUserRolePage();
        // 无权限配置且不是超管, 没有权限查看
        if (empty($pages) && !$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }

        // 获取menu conf
        $menuConf = Zy_Helper_Config::getAppConfig("menu");
        $menuConf = $menuConf['menu'];

        // menu输出
        $menuBase = array(
            'pages' => array(
                array(
                    'label' => "Home",
                    'url' => '/',
                    "redirect" => "/index/1"
                ),
                array(
                    'children' => array(),
                ),
            ),
        );

        // 管理员直接返回
        if ($this->checkSuper()) {
            $menuBase['pages'][1]['children'] = $menuConf;
            return $menuBase;
        }

        // 根据用户pages更新menus
        foreach ($menuConf as $key => $item) {
            if (empty($item['children'])) {
                if (!in_array($item['id'], $pages)) {
                    unset($menuConf[$key]);
                    continue;
                }
            } else {
                foreach ($item['children'] as $ck => $citem) {
                    if (!in_array($citem['id'], $pages)) {
                        unset($item['children'][$ck]);
                        continue;
                    }
                }
                if (empty($item['children'])) {
                    unset($menuConf[$key]);
                    continue;
                }
                $item['children'] = array_values($item['children']);
            }
        }

        $menuBase['pages'][1]['children'] = array_values($menuConf);
        return $menuBase;
    }
}