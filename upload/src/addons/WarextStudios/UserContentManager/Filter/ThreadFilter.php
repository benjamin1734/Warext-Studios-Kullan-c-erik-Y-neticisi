<?php

namespace WarextStudios\UserContentManager\Filter;

use XF\Mvc\Entity\Finder;

class ThreadFilter
{
    public function apply(Finder $finder, array $filters, array $categoryNodeIds = []): Finder
    {
        if (!empty($filters['category_id']))
        {
            if ($categoryNodeIds)
            {
                $finder->where('node_id', $categoryNodeIds);
            }
            else
            {
                $finder->where('thread_id', 0);
            }
        }

        if (!empty($filters['node_id']))
        {
            $finder->where('node_id', $filters['node_id']);
        }

        if (!empty($filters['state']))
        {
            $finder->where('discussion_state', $filters['state']);
        }

        if (($filters['locked'] ?? '') === 'locked')
        {
            $finder->where('discussion_open', 0);
        }
        elseif (($filters['locked'] ?? '') === 'open')
        {
            $finder->where('discussion_open', 1);
        }

        if (($filters['prefix_id'] ?? 0) === -1)
        {
            $finder->where('prefix_id', 0);
        }
        elseif (!empty($filters['prefix_id']))
        {
            $finder->where('prefix_id', $filters['prefix_id']);
        }

        if (!empty($filters['date_from_ts']))
        {
            $finder->where('post_date', '>=', $filters['date_from_ts']);
        }

        if (!empty($filters['date_to_ts']))
        {
            $finder->where('post_date', '<=', $filters['date_to_ts']);
        }

        if (!empty($filters['title']))
        {
            $finder->where('title', 'LIKE', $finder->escapeLike($filters['title'], '%?%'));
        }

        if (($filters['min_replies'] ?? null) !== null)
        {
            $finder->where('reply_count', '>=', $filters['min_replies']);
        }

        if (($filters['max_replies'] ?? null) !== null)
        {
            $finder->where('reply_count', '<=', $filters['max_replies']);
        }

        if (($filters['min_views'] ?? null) !== null)
        {
            $finder->where('view_count', '>=', $filters['min_views']);
        }

        if (($filters['max_views'] ?? null) !== null)
        {
            $finder->where('view_count', '<=', $filters['max_views']);
        }

        $sortMap = [
            'last_post' => 'last_post_date',
            'created' => 'post_date',
            'title' => 'title',
            'replies' => 'reply_count',
            'views' => 'view_count'
        ];

        $sort = $sortMap[$filters['sort'] ?? 'last_post'] ?? 'last_post_date';
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        return $finder->order($sort, $direction)->order('thread_id', 'DESC');
    }
}
