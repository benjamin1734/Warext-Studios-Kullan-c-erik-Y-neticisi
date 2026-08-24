<?php

namespace WarextStudios\UserContentManager\XF\Entity;

class User extends XFCP_User
{
    public function hasPermission($group, $permission)
    {
        if ($group === 'warextUcm' && $this->is_super_admin)
        {
            return true;
        }

        return parent::hasPermission($group, $permission);
    }
}
