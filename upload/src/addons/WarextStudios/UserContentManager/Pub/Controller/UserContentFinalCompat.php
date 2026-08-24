<?php

namespace WarextStudios\UserContentManager\Pub\Controller;

class UserContentFinalCompat extends UserContentFinal
{
    public function repository($identifier)
    {
        if ($identifier === 'WarextStudios/UserContentManager:Content')
        {
            $identifier = 'WarextStudios\\UserContentManager:Content';
        }

        return parent::repository($identifier);
    }
}
