<?php

namespace WarextStudios\UserContentManager\Service;

use XF\Mvc\Entity\Entity;

class ResourceBulk
{
    public function apply(Entity $resource, string $action, array $options = []): bool
    {
        $contentId = (int)$resource->resource_id;
        $targetUserId = (int)$resource->user_id;
        $reason = (string)($options['reason'] ?? '');
        $metadata = ['category_id' => (int)$resource->resource_category_id];
        $changed = match ($action)
        {
            'move' => $this->move($resource, $options['target_category'] ?? null),
            'soft_delete' => $this->delete($resource, 'soft', $reason),
            'hard_delete' => $this->delete($resource, 'hard', $reason),
            'restore', 'approve' => $this->setState($resource, 'visible'),
            'unapprove' => $this->setState($resource, 'moderated'),
            'prefix' => $this->changePrefix($resource, (int)($options['prefix_id'] ?? 0)),
            'title_prepend' => $this->changeTitle($resource, 'prepend', (string)($options['title_value'] ?? '')),
            'title_append' => $this->changeTitle($resource, 'append', (string)($options['title_value'] ?? '')),
            'title_replace' => $this->replaceTitle($resource, (string)($options['title_search'] ?? ''), (string)($options['title_replace'] ?? '')),
            default => throw new \InvalidArgumentException('Unsupported resource bulk action.')
        };

        if (!$changed)
        {
            return false;
        }

        $logger = new ActionLogger();
        if ($action === 'hard_delete')
        {
            $logger->logById('resource', $contentId, $targetUserId, $action, $reason, $metadata);
        }
        else
        {
            $logger->log('resource', $resource, $targetUserId, $action, $reason, ['category_id' => (int)$resource->resource_category_id]);
        }

        return true;
    }

    protected function move(Entity $resource, ?Entity $category): bool
    {
        if (!$category) { throw new \InvalidArgumentException('Target category is required.'); }
        if (!$category->canView()) { throw new \LogicException('Target category is not viewable.'); }
        if ((int)$resource->resource_category_id === (int)$category->resource_category_id) { return false; }
        \XF::app()->service('XFRM:ResourceItem\Move', $resource)->move($category);
        return true;
    }

    protected function delete(Entity $resource, string $type, string $reason): bool
    {
        if ($type === 'soft' && $resource->resource_state === 'deleted') { return false; }
        \XF::app()->service('XFRM:ResourceItem\Delete', $resource)->delete($type, $reason);
        return true;
    }

    protected function setState(Entity $resource, string $state): bool
    {
        if ($resource->resource_state === $state) { return false; }
        $resource->resource_state = $state;
        $resource->save();
        return true;
    }

    protected function changePrefix(Entity $resource, int $prefixId): bool
    {
        if ($prefixId && (!$resource->Category || !$resource->Category->isPrefixUsable($prefixId))) { throw new \LogicException('Prefix is not usable in this category.'); }
        if ((int)$resource->prefix_id === $prefixId) { return false; }
        $resource->prefix_id = $prefixId;
        $resource->save();
        return true;
    }

    protected function changeTitle(Entity $resource, string $mode, string $value): bool
    {
        $value = trim($value);
        if ($value === '') { throw new \InvalidArgumentException('Title value is required.'); }
        if ($mode === 'prepend')
        {
            if ($resource->title === $value || str_starts_with($resource->title, $value . ' ')) { return false; }
            $resource->title = trim($value . ' ' . $resource->title);
        }
        else
        {
            if ($resource->title === $value || str_ends_with($resource->title, ' ' . $value)) { return false; }
            $resource->title = trim($resource->title . ' ' . $value);
        }
        $resource->save();
        return true;
    }

    protected function replaceTitle(Entity $resource, string $search, string $replace): bool
    {
        if ($search === '') { throw new \InvalidArgumentException('Search value is required.'); }
        if ($replace !== $search && str_contains($replace, $search)) { throw new \LogicException('Replacement value cannot contain the search value.'); }
        if (!str_contains($resource->title, $search)) { return false; }
        $newTitle = trim(str_replace($search, $replace, $resource->title));
        if ($newTitle === $resource->title) { return false; }
        $resource->title = $newTitle;
        $resource->save();
        return true;
    }
}
