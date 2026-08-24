<?php

namespace WarextStudios\UserContentManager\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class BulkOperation extends Entity
{
    public static function getStructure(Structure $structure): Structure
    {
        $structure->table = 'xf_warext_ucm_bulk_operation';
        $structure->shortName = 'WarextStudios\\UserContentManager:BulkOperation';
        $structure->primaryKey = 'operation_id';
        $structure->columns = [
            'operation_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'operation_key' => ['type' => self::STR, 'maxLength' => 40, 'required' => true],
            'actor_user_id' => ['type' => self::UINT, 'required' => true],
            'target_user_id' => ['type' => self::UINT, 'required' => true],
            'content_type' => ['type' => self::STR, 'maxLength' => 25, 'required' => true],
            'action' => ['type' => self::STR, 'maxLength' => 50, 'required' => true],
            'filters' => ['type' => self::STR, 'default' => ''],
            'options' => ['type' => self::STR, 'default' => ''],
            'last_content_id' => ['type' => self::UINT, 'default' => 0],
            'matched_count' => ['type' => self::UINT, 'default' => 0],
            'processed_count' => ['type' => self::UINT, 'default' => 0],
            'skipped_count' => ['type' => self::UINT, 'default' => 0],
            'failed_count' => ['type' => self::UINT, 'default' => 0],
            'status' => ['type' => self::STR, 'maxLength' => 20, 'default' => 'queued'],
            'last_error' => ['type' => self::STR, 'default' => ''],
            'lock_token' => ['type' => self::STR, 'maxLength' => 32, 'default' => ''],
            'lock_expires' => ['type' => self::UINT, 'default' => 0],
            'created_date' => ['type' => self::UINT, 'required' => true],
            'updated_date' => ['type' => self::UINT, 'required' => true]
        ];
        $structure->relations = [
            'Actor' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'actor_user_id',
                'primary' => true
            ],
            'TargetUser' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'target_user_id',
                'primary' => true
            ]
        ];

        return $structure;
    }
}
