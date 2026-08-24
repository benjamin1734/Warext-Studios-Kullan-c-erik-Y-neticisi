<?php

namespace WarextStudios\UserContentManager\Pub\Controller;

class ResourceContentFinalCompat extends ResourceContentFinal
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
