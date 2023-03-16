<?php
class Controller_Area extends Zy_Core_Controller{

    public $actions = array(
        "lists"     => "actions/area/Lists.php",
        "aslists"   => "actions/area/Aslists.php",
        "pklists"   => "actions/area/Pklists.php",
        "create"    => "actions/area/Create.php",
        "update"    => "actions/area/Update.php",
        "delete"    => "actions/area/Delete.php",
    );
}
