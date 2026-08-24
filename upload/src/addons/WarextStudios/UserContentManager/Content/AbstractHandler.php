<?php

namespace WarextStudios\UserContentManager\Content;

use XF\Entity\User;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Finder;

abstract class AbstractHandler
{
    public const ACTION_EDIT = 'edit';
    public const ACTION_MOVE = 'move';
    public const ACTION_SOFT_DELETE = 'soft_delete';
    public const ACTION_HARD_DELETE = 'hard_delete';
    public const ACTION_RESTORE = 'restore';
    public const ACTION_LOCK = 'lock';
    public const ACTION_UNLOCK = 'unlock';
    public const ACTION_STICK = 'stick';
    public const ACTION_UNSTICK = 'unstick';
    public const ACTION_APPROVE = 'approve';
    public const ACTION_UNAPPROVE = 'unapprove';
    public const ACTION_PREFIX = 'prefix';
    public const ACTION_WARN = 'warn';

    protected string $contentType;

    public function __construct(string $contentType)
    {
        $this->contentType = $contentType;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getTypeTitle(): string
    {
        return $this->contentType;
    }

    public function getDateColumn(): ?string
    {
        return null;
    }

    abstract public function getEntityShortName(): string;
    abstract public function getOwnerColumn(): string;
    abstract public function getContentId(Entity $entity): int|string;
    abstract public function getOwnerId(Entity $entity): int;
    abstract public function getCategoryId(Entity $entity): ?int;
    abstract public function getTitle(Entity $entity): string;
    abstract public function getState(Entity $entity): string;
    abstract public function getLink(Entity $entity): string;
    abstract public function getSupportedActions(): array;
    abstract public function canView(Entity $entity, ?User $visitor = null): bool;
    abstract public function canPerformAction(string $action, Entity $entity, ?User $visitor = null, mixed &$error = null): bool;

    public function supportsAction(string $action): bool
    {
        return in_array($action, $this->getSupportedActions(), true);
    }

    public function getFinderForUser(User|int $user): Finder
    {
        $userId = $user instanceof User ? $user->user_id : $user;

        return \XF::finder($this->getEntityShortName())
            ->where($this->getOwnerColumn(), $userId);
    }
}
