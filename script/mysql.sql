use zy_mapi;
CREATE TABLE `tblUser` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '登录名',
  `nickname` varchar(100) NOT NULL DEFAULT '' COMMENT '昵称',
  `type` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '类型, 9超管, 11管理员, 12学生, 13老师',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号',
  `avatar` varchar(100) NOT NULL DEFAULT '' COMMENT '头像',
  `school` varchar(100) NOT NULL DEFAULT '' COMMENT '学校',
  `graduate` varchar(100) NOT NULL DEFAULT '' COMMENT '班级',
  `sex` CHAR(1) NOT NULL DEFAULT 'M' COMMENT '性别: M男生, F:女生',
  `student_capital` int(11) NOT NULL DEFAULT '0' COMMENT '学生余额',
  `teacher_capital` int(11) NOT NULL DEFAULT '0' COMMENT '教师余额',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ext` VARCHAR(2000) NOT NULL DEFAULT '' COMMENT "冗余",
  PRIMARY KEY (`uid`),
  KEY `nick` (`nickname`),
  KEY `n_p` (`name`, `phone`)
) ENGINE=InnoDB AUTO_INCREMENT=101000 DEFAULT CHARSET=utf8 COMMENT='用户表';

CREATE TABLE `tblCapital` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int(11) unsigned NOT NULL  COMMENT 'uid',
  `type` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '类型, 9超管, 11管理员, 12学生, 13老师',
  `category` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '类型, 1用户充值, 2作者充值, 3,用户消耗, 4,作者收入',
  `operator` int(11) unsigned NOT NULL  COMMENT 'uid',
  `capital` int(11) NOT NULL COMMENT 'capital',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ext` VARCHAR(2000) NOT NULL DEFAULT '' COMMENT "冗余",
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学生充值表';


CREATE TABLE `tblSubject` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `category1` varchar(200) NOT NULL DEFAULT '' COMMENT '一级类目',
  `category2` varchar(200) NOT NULL DEFAULT '' COMMENT '二级类目',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '名称',
  `descs` varchar(200) NOT NULL DEFAULT '' COMMENT '描述',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ext` VARCHAR(2000) NOT NULL DEFAULT '' COMMENT "冗余",
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学科表';


CREATE TABLE `tblGroup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '名称',
  `descs` varchar(200) NOT NULL DEFAULT '' COMMENT '描述',
  `area` varchar(200) NOT NULL DEFAULT '' COMMENT '学区',
  `status` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '状态, 1开启, 2关闭',
  `price` int(11) unsigned NOT NULL DEFAULT '0'  COMMENT 'price',
  `duration` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'duration',
  `discount` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'discount',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ext` VARCHAR(2000) NOT NULL DEFAULT '' COMMENT "冗余",
  PRIMARY KEY (`id`),
  KEY `n` (`name`),
  KEY `s` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='班级表(学生和科目绑定)';


CREATE TABLE `tblGroupMap` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '班级id',
  `student_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '学生Id',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ext` VARCHAR(2000) NOT NULL DEFAULT '' COMMENT "冗余",
  PRIMARY KEY (`id`),
  KEY `g` (`group_id`),
  KEY `s` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学生和班级绑定表';

CREATE TABLE `tblColumn` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `subject_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '学科id',
  `teacher_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '老师id',
  `price` int(11) unsigned NOT NULL DEFAULT '0'  COMMENT 'price',
  `duration` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'duration',
  `discount` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'discount',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ext` VARCHAR(2000) NOT NULL DEFAULT '' COMMENT "冗余",
  PRIMARY KEY (`id`),
  KEY `s_t` (`subject_id`, `teacher_id`),
  KEY `s` (`subject_id`),
  KEY `t` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='老师专科表(老师和科目绑定)';


CREATE TABLE `tblSchedule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `column_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '专科',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '班级',
  `start_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `end_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `state` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '0, 结束, 1:待开始',
  `operator` int(11) unsigned NOT NULL  COMMENT 'uid',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ext` VARCHAR(2000) NOT NULL DEFAULT '' COMMENT "冗余",
  PRIMARY KEY (`id`),
  KEY `c` (`column_id`, `state`, `start_time`),
  KEY `g` (`group_id`, `state`, `start_time`),
  KEY `s` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='上课记录表';


alter table tblCapital add column `capital_remark` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注';
alter table tblUser add column `student_price`  int(11) NOT NULL DEFAULT '0' COMMENT '学生客单价';

alter table tblSchedule add column `teacher_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '老师id';

alter table tblSchedule add index `t` (`teacher_id`, `state`, `start_time`);
