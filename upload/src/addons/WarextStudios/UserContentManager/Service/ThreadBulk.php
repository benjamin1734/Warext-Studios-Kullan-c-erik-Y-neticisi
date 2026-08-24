<?php

namespace WarextStudios\UserContentManager\Service;

use XF\Entity\Forum;
use XF\Entity\Thread;

class ThreadBulk
{
    public function apply(Thread $thread, string $action, array $options = []): bool
    {
        $contentId = (int)$thread->thread_id;
        $targetUserId = (int)$thread->user_id;
        $reason = (string)($options['reason'] ?? '');
        $metadata = ['node_id' => (int)$thread->node_id];
        $changed = match ($action)
        {
            'move' => $this->move($thread, $options['target_forum'] ?? null),
            'soft_delete' => $this->delete($thread, 'soft', $reason),
            'hard_delete' => $this->delete($thread, 'hard', $reason),
            'restore' => $this->setState($thread, 'visible'),
            'lock' => $this->setOpen($thread, false),
            'unlock' => $this->setOpen($thread, true),
            'stick' => $this->setSticky($thread, true),
            'unstick' => $this->setSticky($thread, false),
            'approve' => $this->approve($thread),
            'unapprove' => $this->setState($thread, 'moderated'),
            'prefix' => $this->changePrefix($thread, (int)($options['prefix_id'] ?? 0)),
            'title_prepend' => $this->changeTitle($thread, 'prepend', (string)($options['title_value'] ?? '')),
            'title_append' => $this->changeTitle($thread, 'append', (string)($options['title_value'] ?? '')),
            'title_replace' => $this->replaceTitle($thread, (string)($options['title_search'] ?? ''), (string)($options['title_replace'] ?? '')),
            default => throw new \InvalidArgumentException('Unsupported bulk action.')
        };

        if (!$changed)
        {
            return false;
        }

        $logger = new ActionLogger();
        if ($action === 'hard_delete')
        {
            $logger->logById('thread', $contentId, $targetUserId, $action, $reason, $metadata);
        }
        else
        {
            $logger->log('thread', $thread, $targetUserId, $action, $reason, ['node_id' => (int)$thread->node_id]);
        }

        return true;
    }

    protected function move(Thread $thread, ?Forum $targetForum): bool
    {
        if (!$targetForum) { throw new \InvalidArgumentException('Target forum is required.'); }
        if (!$targetForum->canView()) { throw new \LogicException('Target forum is not viewable.'); }
        if ((int)$thread->node_id === (int)$targetForum->node_id) { return false; }
        $mover = \XF::app()->service('XF:Thread\Mover', $thread);
        if ($thread->prefix_id && !$targetForum->isPrefixUsable($thread->prefix_id)) { $mover->setPrefix(0); }
        $mover->move($targetForum);
        return true;
    }

    protected function delete(Thread $thread, string $type, string $reason): bool
    {
        if ($type === 'soft' && $thread->discussion_state === 'deleted') { return false; }
        \XF::app()->service('XF:Thread\Deleter', $thread)->delete($type, $reason);
        return true;
    }

    protected function setState(Thread $thread, string $state): bool
    {
        if ($thread->discussion_state === $state) { return false; }
        $thread->discussion_state = $state;
        $thread->save();
        return true;
    }

    protected function setOpen(Thread $thread, bool $open): bool
    {
        if ((bool)$thread->discussion_open === $open) { return false; }
        $thread->discussion_open = $open ? 1 : 0;
        $thread->save();
        return true;
    }

    protected function setSticky(Thread $thread, bool $sticky): bool
    {
        if ((bool)$thread->sticky === $sticky) { return false; }
        $thread->sticky = $sticky ? 1 : 0;
        $thread->save();
        return true;
    }

    protected function approve(Thread $thread): bool
    {
        if ($thread->discussion_state !== 'moderated') { return false; }
        \XF::app()->service('XF:Thread\Approver', $thread)->approve();
        return true;
    }

    protected function changePrefix(Thread $thread, int $prefixId): bool
    {
        if ($prefixId && (!$thread->Forum || !$thread->Forum->isPrefixUsable($prefixId))) { throw new \LogicException('Prefix is not usable in the target forum.'); }
        if ((int)$thread->prefix_id === $prefixId) { return false; }
        $thread->prefix_id = $prefixId;
        $thread->save();
        return true;
    }

    protected function changeTitle(Thread $thread, string $mode, string $value): bool
    {
        $value = trim($value);
        if ($value === '') { throw new \InvalidArgumentException('Title value is required.'); }
        if ($mode === 'prepend')
        {
            if ($thread->title === $value || str_starts_with($thread->title, $value . ' ')) { return false; }
            $thread->title = trim($value . ' ' . $thread->title);
        }
        else
        {
            if ($thread->title === $value || str_ends_with($thread->title, ' ' . $value)) { return false; }
            $thread->title = trim($thread->title . ' ' . $value);
        }
        $thread->save();
        return true;
    }

    protected function replaceTitle(Thread $thread, string $search, string $replace): bool
    {
        if ($search === '') { throw new \InvalidArgumentException('Search value is required.'); }
        if ($replace !== $search && str_contains($replace, $search)) { throw new \LogicException('Replacement value cannot contain the search value.'); }
        if (!str_contains($thread->title, $search)) { return false; }
        $title = trim(str_replace($search, $replace, $thread->title));
        if ($title === $thread->title) { return false; }
        $thread->title = $title;
        $thread->save();
        return true;
    }
}
