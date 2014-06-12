<?php
    
require_once "Model.php";

class GroupModel extends Model
{
    public function getAllGroups()
    {
        $sth = $this->execute("SELECT name FROM groups");
        $groupsDb = $sth->fetchAll();
        
        $groups = array();
        foreach($groupsDb as $groupDb)
        {
            $group = new Group();
            $group->setName($groupDb['name']);
            array_push($groups, $group);
        }
        
        return $groups;
    }
}