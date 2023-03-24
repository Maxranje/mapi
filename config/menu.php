<?php
defined('BASEPATH') OR exit('No direct script access allowed');
return array(
    array(
        "id" => 1,
        "label" =>  "Dashboard",
        "url"=>"/index/1",
        "icon"=>"fa fa-home",
        "schemaApi"=>"get:/public/pages/dashboard.json"
    ),
    array(
        "id" => 2,
        "label"=>"学生管理",
        "url"=>"/sutdent",
        "icon"=>"fa fa-bars",
        "schemaApi"=>"get:/public/pages/crud-student-list.json"
    ),
    array(
        "id" => 3,
        "label"=>"班级管理",
        "url"=>"/group",
        "icon"=>"fa fa-envelope-o",
        "schemaApi"=>"get:/public/pages/crud-group-list.json"
    ),
    array(
        "id" => 4,
        "label"=>"排课管理",
        "url"=>"/schedule",
        "icon"=>"fa fa-plus-square-o",
        "children"=>[
            array(
                "id" => 41,
                "label"=>"开始排课",
                "url"=>"/schedule/schedulestart",
                "icon"=>"fa fa-pencil-square-o",
                "schemaApi"=>"get:/public/pages/form-schedule.json"
            ),
            array(
                "id" => 42,
                "label"=>"排课列表",
                "url"=>"/schedule/schedulelist",
                "icon"=>"fa fa-list",
                "schemaApi"=>"get:/public/pages/crud-schedule-list.json"
            ),
            array(
                "id" => 43,
                "label"=>"校区教室",
                "url"=>"/schedule/pkarealist",
                "icon"=>"fa fa-bars",
                "schemaApi"=>"get:/public/pages/crud-pkarea-list.json"
            )
        ]
    ),
    array(
        "id" => 5,
        "label"=>"统计管理",
        "url"=>"/statistics",
        "icon"=>"fa fa-bar-chart-o",
        "children"=>[
            array(
                "id" => 51,
                "label"=>"订单记录",
                "url"=>"/statistics/lists",
                "icon"=>"fa fa-bars",
                "schemaApi"=>"get:/public/pages/crud-statistics-list.json"
            ),
            array(
                "id" => 52,
                "label"=>"学班记录",
                "url"=>"/statistics/detaillists",
                "icon"=>"fa fa-list",
                "schemaApi"=>"get:/public/pages/crud-statistics-details.json"
            )
        ]
    ),
    array(
        "id" => 6,
        "label"=>"科目分类",
        "url"=>"/subject",
        "icon"=>"fa fa-server",
        "schemaApi"=>"get:/public/pages/crud-subject-list.json"
    ),
    array(
        "id" => 7,
        "label"=>"教师管理",
        "url"=>"/teacher",
        "icon"=>"fa fa-credit-card",
        "schemaApi"=>"get:/public/pages/crud-teacher-list.json"
    ),
    array(
        "id" => 8,
        "label"=>"校区管理",
        "url"=>"/area",
        "icon"=>"fa fa-street-view",
        "schemaApi"=>"get:/public/pages/crud-area-list.json"
    ),
    array(
        "id" => 9,
        "label"=>"系统信息",
        "url"=>"/system",
        "icon"=>"fa fa-cog",
        "children"=>[
            array(
                "id" => 91,
                "label"=>"管理员",
                "url"=>"/system/admins",
                "icon"=>"fa fa-list",
                "schemaApi"=>"get:/public/pages/crud-admin-list.json"
            )
        ]
    )
);