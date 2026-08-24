<?php

namespace WarextStudios\UserContentManager\Content;

use XF\Entity\User;
use XF\Mvc\Entity\Entity;

class Resource extends AbstractHandler
{
    public function isAvailable(): bool
    {
        return class_exists('XFRM\\Entity\\ResourceItem');
    }

    public function getEntityShortName(): string
    {
        return 'XFRM:ResourceItem';
    }

    public function getOwnerColumn(): string
    {
        return 'user_id';
    }

    public function getContentId(Entity $entity): int|string
    {
        return $entity->resource_id;
    }

    public function getOwnerId(Entity $entity): int
    {
        return $entity->user_id;
    }

    public function getCategoryId(Entity $entity): ?int
    {
        return $entity->resource_category_id ?: null;
    }

    public function getTitle(Entity $entity): string
    {
        return $entity->title;
    }

    public function getState(Entity $entity): string
    {
        return $entity->resource_state;
    }

    public function getLink(Entity $entity): string
    {
        return \XF::app()->router('public')->buildLink('resources', $entity);
    }

    public function getSupportedActions(): array
    {
        return [
            self::ACTION_EDIT,
            self::ACTION_MOVE,
            self::ACTION_SOFT_DELETE,
            self::ACTION_HARD_DELETE,
            self::ACTION_RESTORE,
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
            self::ACTION_APPROVE => $entity->canApprove($error),
            self::ACTION_UNAPPROVE => $entity->canUnapprove($error),
            default => false
        };
    }
}
