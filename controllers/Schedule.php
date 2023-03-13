<?php
class Controller_Schedule extends Zy_Core_Controller{

    public $actions = array(
        "lists"     => "actions/schedule/Lists.php",
        "create"    => "actions/schedule/Create.php",
        "createv2"  => "actions/schedule/Createv2.php",
        "listsv2"   => "actions/schedule/Listsv2.php",
        "listsv3"   => "actions/schedule/Listsv3.php",
        "update"    => "actions/schedule/Update.php",
        "delete"    => "actions/schedule/Delete.php",
        "jobs"      => "actions/schedule/Jobs.php",
        "joblist"   => "actions/schedule/Joblist.php",
        "joblistv2"   => "actions/schedule/Joblistv2.php",
        "timelist"  => "actions/schedule/Timelist.php",
        "updatearea"    => "actions/schedule/Updatearea.php",
        "current"    => "actions/schedule/Current.php",
    );
}
