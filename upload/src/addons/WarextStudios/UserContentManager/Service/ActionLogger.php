<?php

namespace WarextStudios\UserContentManager\Service;

use XF\Mvc\Entity\Entity;

class ActionLogger
{
    public function log(string $contentType, Entity $content, int $targetUserId, string $action, string $reason = '', array $metadata = []): void
    {
        $contentId = (int)($content->getEntityId() ?: 0);
        $this->write($contentType, $contentId, $targetUserId, $action, $reason, $metadata);

        \XF::app()->logger()->logModeratorAction(
            $contentType,
            $content,
            'warext_ucm_' . $action,
            ['reason' => $reason, 'target_user_id' => $targetUserId],
            false
        );
    }

    public function logById(string $contentType, int $contentId, int $targetUserId, string $action, string $reason = '', array $metadata = []): void
    {
        $this->write($contentType, $contentId, $targetUserId, $action, $reason, $metadata);
    }

    protected function write(string $contentType, int $contentId, int $targetUserId, string $action, string $reason, array $metadata): void
    {
        $log = \XF::em()->create('WarextStudios\\UserContentManager:ActionLog');
        $log->actor_user_id = \XF::visitor()->user_id;
        $log->target_user_id = $targetUserId;
        $log->content_type = $contentType;
        $log->content_id = $contentId;
        $log->action = $action;
        $log->reason = mb_substr(trim($reason), 0, 255);
        $log->metadata = $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        $log->log_date = \XF::$time;
        $log->save();
    }
}
