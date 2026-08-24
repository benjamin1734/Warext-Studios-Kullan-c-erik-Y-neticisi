<?php

namespace WarextStudios\UserContentManager\Service;

use WarextStudios\UserContentManager\Entity\BulkOperation;

class BulkOperationQueue
{
    public function enqueue(
        string $contentType,
        int $actorUserId,
        int $targetUserId,
        string $action,
        array $filters,
        array $options,
        int $matchedCount
    ): BulkOperation
    {
        $operation = \XF::em()->create('WarextStudios/UserContentManager:BulkOperation');
        $operation->operation_key = \XF::generateRandomString(32);
        $operation->actor_user_id = $actorUserId;
        $operation->target_user_id = $targetUserId;
        $operation->content_type = $contentType;
        $operation->action = $action;
        $operation->filters = json_encode($filters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $operation->options = json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $operation->matched_count = max(0, $matchedCount);
        $operation->status = 'queued';
        $operation->created_date = \XF::$time;
        $operation->updated_date = \XF::$time;
        $operation->save();

        \XF::app()->jobManager()->enqueueUnique(
            'warextUcmBulk' . $operation->operation_id,
            'WarextStudios/UserContentManager:BulkOperationFinal2',
            ['operation_id' => $operation->operation_id],
            false
        );

        return $operation;
    }
}
