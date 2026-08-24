<?php

namespace WarextStudios\UserContentManager\Job;

use WarextStudios\UserContentManager\Content\AbstractHandler;
use XF\Entity\User;
use XF\Mvc\Entity\Finder;

class BulkOperationFinal extends BulkOperation
{
    protected function buildThreadFinder(AbstractHandler $handler, User $targetUser, array $filters): Finder
    {
        $finder = $handler->getFinderForUser($targetUser)
            ->with('Forum')
            ->where('discussion_type', '<>', 'redirect');

        [$nodes, $visibleForumIds] = $this->getVisibleForums();

        if (!$visibleForumIds)
        {
            $finder->where('thread_id', 0);
            return $finder;
        }

        $finder->where('node_id', $visibleForumIds);
        $categoryId = (int)($filters['category_id'] ?? 0);
        $nodeId = (int)($filters['node_id'] ?? 0);

        if ($categoryId)
        {
            $categoryForumIds = $this->getCategoryForumIds($nodes, $categoryId);
            if ($categoryForumIds) { $finder->where('node_id', $categoryForumIds); }
            else { $finder->where('thread_id', 0); }
        }

        if ($nodeId)
        {
            if (in_array($nodeId, $visibleForumIds, true)) { $finder->where('node_id', $nodeId); }
            else { $finder->where('thread_id', 0); }
        }

        if (!empty($filters['state'])) { $finder->where('discussion_state', $filters['state']); }
        if (($filters['locked'] ?? '') === 'locked') { $finder->where('discussion_open', 0); }
        elseif (($filters['locked'] ?? '') === 'open') { $finder->where('discussion_open', 1); }

        $prefixId = (int)($filters['prefix_id'] ?? 0);
        if ($prefixId === -1) { $finder->where('prefix_id', 0); }
        elseif ($prefixId > 0) { $finder->where('prefix_id', $prefixId); }

        if (!empty($filters['date_from_ts'])) { $finder->where('post_date', '>=', (int)$filters['date_from_ts']); }
        if (!empty($filters['date_to_ts'])) { $finder->where('post_date', '<=', (int)$filters['date_to_ts']); }
        if (!empty($filters['title'])) { $finder->where('title', 'LIKE', $finder->escapeLike((string)$filters['title'], '%?%')); }
        if (($filters['min_replies'] ?? null) !== null) { $finder->where('reply_count', '>=', (int)$filters['min_replies']); }
        if (($filters['max_replies'] ?? null) !== null) { $finder->where('reply_count', '<=', (int)$filters['max_replies']); }
        if (($filters['min_views'] ?? null) !== null) { $finder->where('view_count', '>=', (int)$filters['min_views']); }
        if (($filters['max_views'] ?? null) !== null) { $finder->where('view_count', '<=', (int)$filters['max_views']); }

        return $finder;
    }

    protected function buildResourceFinder(AbstractHandler $handler, User $targetUser, array $filters): Finder
    {
        $finder = $handler->getFinderForUser($targetUser)->with('Category');
        $visibleIds = $this->getVisibleResourceCategoryIds();

        if (!$visibleIds)
        {
            $finder->where('resource_id', 0);
            return $finder;
        }

        $finder->where('resource_category_id', $visibleIds);
        $categoryId = (int)($filters['category_id'] ?? 0);

        if ($categoryId)
        {
            if (in_array($categoryId, $visibleIds, true)) { $finder->where('resource_category_id', $categoryId); }
            else { $finder->where('resource_id', 0); }
        }

        if (!empty($filters['state'])) { $finder->where('resource_state', $filters['state']); }
        if (!empty($filters['title'])) { $finder->where('title', 'LIKE', $finder->escapeLike((string)$filters['title'], '%?%')); }

        return $finder;
    }

    public function canCancel()
    {
        return false;
    }
}
