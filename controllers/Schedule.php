<?php
class Controller_Schedule extends Zy_Core_Controller{

    public $actions = array(
        "pkcalendar"    => "actions/schedule/Pkcalendar.php",
        "create"        => "actions/schedule/Create.php",
        "createv2"      => "actions/schedule/Createv2.php",
        "pklists"       => "actions/schedule/Pklists.php",
        "bscalendar"    => "actions/schedule/Bscalendar.php",
        "update"        => "actions/schedule/Update.php",
        "delete"        => "actions/schedule/Delete.php",
        "checkout"      => "actions/schedule/Checkout.php",
        "fscalendar"    => "actions/schedule/Fscalendar.php",
        "timelist"      => "actions/schedule/Timelist.php",
    );
}
