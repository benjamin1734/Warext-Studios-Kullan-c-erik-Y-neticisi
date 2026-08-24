<?php

namespace WarextStudios\UserContentManager\Content;

use XF\Entity\User;
use XF\Mvc\Entity\Entity;

class Thread extends AbstractHandler
{
    public function getEntityShortName(): string
    {
        return 'XF:Thread';
    }

    public function getOwnerColumn(): string
    {
        return 'user_id';
    }

    public function getContentId(Entity $entity): int|string
    {
        return $entity->thread_id;
    }

    public function getOwnerId(Entity $entity): int
    {
        return $entity->user_id;
    }

    public function getCategoryId(Entity $entity): ?int
    {
        return $entity->node_id ?: null;
    }

    public function getTitle(Entity $entity): string
    {
        return $entity->title;
    }

    public function getState(Entity $entity): string
    {
        return $entity->discussion_state;
    }

    public function getLink(Entity $entity): string
    {
        return \XF::app()->router('public')->buildLink('threads', $entity);
    }

    public function getSupportedActions(): array
    {
        return [
            self::ACTION_EDIT,
            self::ACTION_MOVE,
            self::ACTION_SOFT_DELETE,
            self::ACTION_HARD_DELETE,
            self::ACTION_RESTORE,
            self::ACTION_LOCK,
            self::ACTION_UNLOCK,
            self::ACTION_STICK,
            self::ACTION_UNSTICK,
            self::ACTION_APPROVE,
            self::ACTION_UNAPPROVE,
            self::ACTION_PREFIX
        ];
    }

    public function canView(Entity $entity, ?User $visitor = null): bool
    {
        return $entity->canView();
    }

    public function canPerformAction(string $action, Entity $entity, ?User $visitor = null, mixed &$error = null): bool
    {
        if (!$this->supportsAction($action))
        {
            return false;
        }

        return match ($action)
        {
            self::ACTION_EDIT,
            self::ACTION_PREFIX => $entity->canEdit($error),
            self::ACTION_MOVE => $entity->canMove($error),
            self::ACTION_SOFT_DELETE => $entity->canDelete('soft', $error),
            self::ACTION_HARD_DELETE => $entity->canDelete('hard', $error),
            self::ACTION_RESTORE => $entity->canUndelete($error),
            self::ACTION_LOCK,
            self::ACTION_UNLOCK => $entity->canLockUnlock($error),
            self::ACTION_STICK,
            self::ACTION_UNSTICK => $entity->canStickUnstick($error),
            self::ACTION_APPROVE,
            self::ACTION_UNAPPROVE => $entity->canApproveUnapprove($error),
            default => false
        };
    }
}
