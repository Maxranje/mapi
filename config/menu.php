<?php
defined('BASEPATH') OR exit('No direct script access allowed');
return array(
    'admin' => '[
        {
            "label": "Dashboard",
            "url": "/index/1",
            "icon": "fa fa-home",
            "schemaApi": "get:/public/pages/dashboard.json"
        },
        {
            "label": "学生管理",
            "url": "/sutdent",
            "icon": "fa fa-bars",
            "schemaApi": "get:/public/pages/crud-student-list.json"
        },
        {
            "label": "班级管理",
            "url": "/group",
            "icon": "fa fa-envelope-o",
            "schemaApi": "get:/public/pages/crud-group-list.json"
        },
        {
            "label": "排课管理",
            "url": "/schedule",
            "icon": "fa fa-plus-square-o",
            "children": [
            {
                "label": "开始排课",
                "url": "/schedule/schedulestart",
                "icon": "fa fa-pencil-square-o",
                "schemaApi": "get:/public/pages/form-schedule.json"
            },
            {
                "label": "排课列表",
                "url": "/schedule/schedulelist",
                "icon": "fa fa-list",
                "schemaApi": "get:/public/pages/crud-schedule-list.json"
            }]
        },
        {
            "label": "订单记录",
            "url": "/statistics",
            "icon": "fa fa-bar-chart-o",
            "schemaApi": "get:/public/pages/crud-statistics-list.json"
        }
    ]',
    'super' => '[
        {
            "label": "科目分类",
            "url": "/subject",
            "icon": "fa fa-server",
            "schemaApi": "get:/public/pages/crud-subject-list.json"
        },
        {
            "label": "教师管理",
            "url": "/teacher",
            "icon": "fa fa-credit-card",
            "schemaApi": "get:/public/pages/crud-teacher-list.json"
        },
        {
            "label": "系统信息",
            "url": "/system",
            "icon": "fa fa-cog",
            "children": [
                {
                    "label": "管理员",
                    "url": "/system/admins",
                    "icon": "fa fa-list",
                    "schemaApi": "get:/public/pages/crud-admin-list.json"
                }
            ]
        }
    ]'
);