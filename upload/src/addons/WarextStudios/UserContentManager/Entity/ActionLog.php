<?php

namespace WarextStudios\UserContentManager\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class ActionLog extends Entity
{
    public static function getStructure(Structure $structure): Structure
    {
        $structure->table = 'xf_warext_ucm_action_log';
        $structure->shortName = 'WarextStudios/UserContentManager:ActionLog';
        $structure->primaryKey = 'action_log_id';
        $structure->columns = [
            'action_log_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'actor_user_id' => ['type' => self::UINT, 'required' => true],
            'target_user_id' => ['type' => self::UINT, 'required' => true],
            'content_type' => ['type' => self::STR, 'maxLength' => 25, 'required' => true],
            'content_id' => ['type' => self::UINT, 'required' => true],
            'action' => ['type' => self::STR, 'maxLength' => 50, 'required' => true],
            'reason' => ['type' => self::STR, 'maxLength' => 255, 'default' => ''],
            'metadata' => ['type' => self::STR, 'default' => ''],
            'log_date' => ['type' => self::UINT, 'required' => true]
        ];
        $structure->relations = [
            'Actor' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'actor_user_id',
                'primary' => true
            ]
        ];

        return $structure;
    }
}
