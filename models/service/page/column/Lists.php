<?php

class Service_Page_Column_Lists extends Zy_Core_Service{

    public function execute () {
        if (!$this->checkSuper()) {
            throw new Zy_Core_Exception(405, "无权限查看");
        }
        
        $pn = empty($this->request['page']) ? 0 : intval($this->request['page']);
        $rn = empty($this->request['perPage']) ? 0 : intval($this->request['perPage']);

        $pn = ($pn-1) * $rn;

        $teacherId = empty($this->request['teacherId']) ? 0 : intval($this->request['teacherId']);
        $isSelect = empty($this->request['isSelect']) ? false : true;

        $conds = array();

        if ($teacherId > 0) {
            $conds['teacher_id'] = $teacherId;
        }
        
        $serviceData = new Service_Data_Column();

        $arrAppends[] = 'order by id';

        if (!$isSelect) {
            $arrAppends[] = "limit {$pn} , {$rn}";
        }

        $lists = $serviceData->getListByConds($conds, false, NULL, $arrAppends);
        $lists = $this->formatBase($lists);

        if ($isSelect) {
            return $this->formatSelect($lists);
        }
        $total = $serviceData->getTotalByConds($conds);

        return array(
            'lists' => $lists,
            'total' => $total,
        );
    }

    private function formatBase ($lists) {
        if (empty($lists)) {
            return array();
        }

        $subjectIds = array_column($lists, 'subject_id');
        foreach ($subjectIds as &$v) {
            $v = intval($v);
        }

        $teacherIds = array_column($lists, 'teacher_id');
        foreach ($teacherIds as &$v) {
            $v = intval($v);
        }

        $conds = array(
            sprintf("id in (%s)", implode(",", $subjectIds)),
        );
        $serviceData = new Service_Data_Subject();
        $subjectInfos = $serviceData->getListByConds($conds);
        $subjectInfos = array_column($subjectInfos, null, 'id');

        $conds = array(
            sprintf("uid in (%s)", implode(",", $teacherIds)),
        );
        $serviceData = new Service_Data_User_Profile();
        $teacherInfos = $serviceData->getListByConds($conds);
        $teacherInfos = array_column($teacherInfos, null, 'uid');

        $result = array();
        foreach ($lists as $item) {
            $result[] = array(
                'subjectName' => $subjectInfos[$item['subject_id']]['name'],
                'teacherName' => $teacherInfos[$item['teacher_id']]['nickname'],
                'teacherId' => $item['teacher_id'],
                'subjectId' => $item['subject_id'],
                "price" => $item['price'],
                "priceInfo" => ($item['price'] / 100) . "元",
                "priceInfo2" => ($item['price'] / 100),
            );
        }
        return $result;
    }

    private function formatSelect ($lists) {
        return array(
            "type"=> "cards",
            "data"=> [
                'items' => $lists,
            ],
            "source" => '${items}',
            "card"=> [
                "body"=> [
                    [
                        "label"=> "课程名",
                        "name"=> "subjectName"
                    ],
                    [
                        "label"=> "课时单价",
                        "name"=> "priceInfo"
                    ]
                ],
                "actions"=> [
                    [
                        "type"=> "button",
                        "level"=> "link",
                        "icon"=> "fa fa-pencil",
                        "actionType"=> "dialog",
                        "dialog"=> [
                            "title"=> "查看详情",
                            "body"=>[
                                "type"=> "form",
                                "name"=> "update-column-form",
                                "api"=> [
                                    "method"=> "post",
                                    "url"=> "/mapi/column/update",
                                    "dataType"=> "form"
                                ],
                                "body"=> [
                                    [
                                        "type"=> "input-text",
                                        "name"=> "teacherId",
                                        "label"=> "教师ID",
                                        "disabled"=> true
                                    ],
                                    [
                                        "type"=> "divider"
                                    ],
                                    [
                                        "type"=> "input-text",
                                        "name"=> "subjectId",
                                        "label"=> "科目ID",
                                        "disabled"=> true
                                    ],
                                    [
                                        "type"=> "divider"
                                    ],
                                    [
                                        "type"=> "input-text",
                                        "name"=> "price",
                                        "label"=> "课时单价",
                                        "value"=>'${priceInfo2}',
                                        "addOn"=> [
                                            "type"=> "text",
                                            "label"=> "元"
                                        ],
                                        "desc"=> "一小时单价, 元为单位, 保留小数点后两位, 谨慎填写价格, 0为免费课"
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        "type"=> "button",
                        "icon"=> "fa fa-times text-danger",
                        "actionType"=> "ajax",
                        "tooltip"=> "删除",
                        "confirmText"=> "您确认要删除课程, 删除课程会删除所有未上课的排课?",
                        "api"=> [
                            "method"=> "get",
                            "url"=> '/mapi/column/delete?teacher_id=$teacherId&subject_id=$subjectId'
                        ]
                    ]
                ]
            ]
        );
    }
}