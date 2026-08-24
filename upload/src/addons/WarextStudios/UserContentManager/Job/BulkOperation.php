<?php

namespace WarextStudios\UserContentManager\Job;

use WarextStudios\UserContentManager\Content\AbstractHandler;
use WarextStudios\UserContentManager\Entity\BulkOperation as BulkOperationEntity;
use WarextStudios\UserContentManager\Service\ResourceBulk;
use WarextStudios\UserContentManager\Service\ThreadBulk;
use XF\Entity\User;
use XF\Job\AbstractJob;
use XF\Mvc\Entity\Finder;

class BulkOperation extends AbstractJob
{
    protected $defaultData = [
        'operation_id' => 0,
        'batch' => 100
    ];

    public function run($maxRunTime)
    {
        $operation = $this->app->em()->find('WarextStudios/UserContentManager:BulkOperation', $this->data['operation_id']);

        if (!$operation || in_array($operation->status, ['completed', 'failed', 'cancelled'], true))
        {
            return $this->complete();
        }

        $lockToken = $this->acquireLock($operation);

        if ($lockToken === null)
        {
            return $this->resume();
        }

        $started = microtime(true);
        $originalVisitor = \XF::visitor();

        try
        {
            $actor = $this->app->em()->find('XF:User', $operation->actor_user_id);
            $targetUser = $this->app->em()->find('XF:User', $operation->target_user_id);

            if (!$actor || !$targetUser)
            {
                return $this->failOperation($operation, 'İşlemi başlatan veya hedef kullanıcı bulunamadı.');
            }

            if (!$actor->hasPermission('warextUcm', 'view') || !$actor->hasPermission('warextUcm', 'bulk'))
            {
                return $this->failOperation($operation, 'İşlemi başlatan kullanıcının toplu işlem yetkisi artık yok.');
            }

            if ($operation->action === 'hard_delete' && !$actor->hasPermission('warextUcm', 'hardDelete'))
            {
                return $this->failOperation($operation, 'İşlemi başlatan kullanıcının kalıcı silme yetkisi artık yok.');
            }

            \XF::setVisitor($actor);

            $handler = $this->app->repository('WarextStudios/UserContentManager:Content')->getHandler($operation->content_type);

            if (!$handler)
            {
                return $this->failOperation($operation, 'İçerik işleyicisi kullanılamıyor.');
            }

            $filters = $this->decodeJson($operation->filters);
            $options = $this->decodeJson($operation->options);
            $finder = $this->buildFinder($operation, $handler, $targetUser, $filters);
            $idColumn = $operation->content_type === 'thread' ? 'thread_id' : 'resource_id';
            $finder->where($idColumn, '>', $operation->last_content_id)
                ->order($idColumn, 'ASC')
                ->limit(max(10, min(250, (int)$this->data['batch'])));

            $items = $finder->fetch();

            if (!$items->count())
            {
                $operation->status = 'completed';
                $operation->updated_date = \XF::$time;
                $operation->save();
                return $this->complete();
            }

            $serviceOptions = $this->prepareServiceOptions($operation, $options);
            $permissionAction = $this->getPermissionAction($operation->action);
            $processed = 0;

            foreach ($items as $item)
            {
                $contentId = (int)$item->getEntityId();
                $operation->last_content_id = $contentId;
                $actionError = null;

                if (!$handler->canView($item, $actor) || !$handler->canPerformAction($permissionAction, $item, $actor, $actionError))
                {
                    $operation->skipped_count++;
                }
                else
                {
                    try
                    {
                        if ($operation->content_type === 'thread')
                        {
                            (new ThreadBulk())->apply($item, $operation->action, $serviceOptions);
                        }
                        else
                        {
                            (new ResourceBulk())->apply($item, $operation->action, $serviceOptions);
                        }

                        $operation->processed_count++;
                    }
                    catch (\Throwable $e)
                    {
                        $operation->failed_count++;
                        $operation->last_error = mb_substr($e->getMessage(), 0, 2000);
                        \XF::logException($e, false, 'Warext UCM job ' . $operation->operation_id . ' content ' . $contentId . ': ');
                    }
                }

                $processed++;

                if (microtime(true) - $started >= max(1, (float)$maxRunTime - 0.5))
                {
                    break;
                }
            }

            $operation->status = 'running';
            $operation->updated_date = \XF::$time;
            $operation->save();

            if ($processed < $items->count())
            {
                return $this->resume();
            }

            if ($items->count() < (int)$this->data['batch'])
            {
                $operation->status = 'completed';
                $operation->updated_date = \XF::$time;
                $operation->save();
                return $this->complete();
            }

            return $this->resume();
        }
        catch (\Throwable $e)
        {
            \XF::logException($e, false, 'Warext UCM bulk job ' . $operation->operation_id . ': ');
            return $this->failOperation($operation, $e->getMessage());
        }
        finally
        {
            \XF::setVisitor($originalVisitor);
            $this->releaseLock($operation->operation_id, $lockToken);
        }
    }

    protected function buildFinder(BulkOperationEntity $operation, AbstractHandler $handler, User $targetUser, array $filters): Finder
    {
        if ($operation->content_type === 'thread')
        {
            return $this->buildThreadFinder($handler, $targetUser, $filters);
        }

        if ($operation->content_type === 'resource' && class_exists('XFRM\\Entity\\ResourceItem'))
        {
            return $this->buildResourceFinder($handler, $targetUser, $filters);
        }

        $finder = $handler->getFinderForUser($targetUser);
        $finder->where($handler->getOwnerColumn(), $targetUser->user_id);
        return $finder;
    }

    protected function buildThreadFinder(AbstractHandler $handler, User $targetUser, array $filters): Finder
    {
        $finder = $handler->getFinderForUser($targetUser)
            ->with('Forum')
            ->where('discussion_type', '<>', 'redirect');

        [$nodes, $visibleForumIds] = $this->getVisibleForums();

        if (!$visibleForumIds)
        {
            return $finder->where('thread_id', 0);
        }

        $finder->where('node_id', $visibleForumIds);
        $categoryId = (int)($filters['category_id'] ?? 0);
        $nodeId = (int)($filters['node_id'] ?? 0);

        if ($categoryId)
        {
            $categoryForumIds = $this->getCategoryForumIds($nodes, $categoryId);
            $finder->where($categoryForumIds ? ['node_id', $categoryForumIds] : ['thread_id', 0]);
        }

        if ($nodeId)
        {
            $finder->where(in_array($nodeId, $visibleForumIds, true) ? ['node_id', $nodeId] : ['thread_id', 0]);
        }

        if (!empty($filters['state'])) { $finder->where('discussion_state', $filters['state']); }
        if (($filters['locked'] ?? '') === 'locked') { $finder->where('discussion_open', 0); }
        if (($filters['locked'] ?? '') === 'open') { $finder->where('discussion_open', 1); }

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
            return $finder->where('resource_id', 0);
        }

        $finder->where('resource_category_id', $visibleIds);
        $categoryId = (int)($filters['category_id'] ?? 0);

        if ($categoryId)
        {
            $finder->where(in_array($categoryId, $visibleIds, true) ? ['resource_category_id', $categoryId] : ['resource_id', 0]);
        }

        if (!empty($filters['state'])) { $finder->where('resource_state', $filters['state']); }
        if (!empty($filters['title'])) { $finder->where('title', 'LIKE', $finder->escapeLike((string)$filters['title'], '%?%')); }

        return $finder;
    }

    protected function prepareServiceOptions(BulkOperationEntity $operation, array $options): array
    {
        if ($operation->content_type === 'thread' && $operation->action === 'move')
        {
            $nodeId = (int)($options['target_node_id'] ?? 0);
            $forum = $nodeId ? $this->app->em()->find('XF:Forum', $nodeId) : null;
            if (!$forum || !$forum->canView()) { throw new \LogicException('Hedef forum artık kullanılamıyor.'); }
            $options['target_forum'] = $forum;
        }

        if ($operation->content_type === 'resource' && $operation->action === 'move')
        {
            $categoryId = (int)($options['target_category_id'] ?? 0);
            $category = $categoryId ? $this->app->em()->find('XFRM:Category', $categoryId) : null;
            if (!$category || !$category->canView()) { throw new \LogicException('Hedef kaynak kategorisi artık kullanılamıyor.'); }
            $options['target_category'] = $category;
        }

        return $options;
    }

    protected function getPermissionAction(string $action): string
    {
        return match ($action)
        {
            'move' => AbstractHandler::ACTION_MOVE,
            'soft_delete' => AbstractHandler::ACTION_SOFT_DELETE,
            'hard_delete' => AbstractHandler::ACTION_HARD_DELETE,
            'restore' => AbstractHandler::ACTION_RESTORE,
            'lock' => AbstractHandler::ACTION_LOCK,
            'unlock' => AbstractHandler::ACTION_UNLOCK,
            'stick' => AbstractHandler::ACTION_STICK,
            'unstick' => AbstractHandler::ACTION_UNSTICK,
            'approve' => AbstractHandler::ACTION_APPROVE,
            'unapprove' => AbstractHandler::ACTION_UNAPPROVE,
            'prefix' => AbstractHandler::ACTION_PREFIX,
            default => AbstractHandler::ACTION_EDIT
        };
    }

    protected function getVisibleForums(): array
    {
        $nodes = $this->app->repository('XF:Node')->getNodeList();
        $ids = [];

        foreach ($nodes as $node)
        {
            if ($node->node_type_id === 'Forum' && $node->canView())
            {
                $ids[] = (int)$node->node_id;
            }
        }

        return [$nodes, $ids];
    }

    protected function getCategoryForumIds($nodes, int $categoryId): array
    {
        if (!$categoryId || !isset($nodes[$categoryId])) { return []; }
        $category = $nodes[$categoryId];
        if (!$category->canView()) { return []; }
        if ($category->node_type_id === 'Forum') { return [(int)$category->node_id]; }
        if ($category->node_type_id !== 'Category') { return []; }

        $ids = [];
        foreach ($nodes as $node)
        {
            if ($node->node_type_id === 'Forum' && $node->canView() && $node->lft > $category->lft && $node->rgt < $category->rgt)
            {
                $ids[] = (int)$node->node_id;
            }
        }
        return $ids;
    }

    protected function getVisibleResourceCategoryIds(): array
    {
        $ids = [];
        foreach ($this->app->finder('XFRM:Category')->order('lft')->fetch() as $category)
        {
            if ($category->canView()) { $ids[] = (int)$category->resource_category_id; }
        }
        return $ids;
    }

    protected function acquireLock(BulkOperationEntity $operation): ?string
    {
        $token = \XF::generateRandomString(20);
        $affected = $this->app->db()->update(
            'xf_warext_ucm_bulk_operation',
            ['lock_token' => $token, 'lock_expires' => \XF::$time + 120],
            'operation_id = ? AND (lock_token = ? OR lock_expires < ?)',
            [$operation->operation_id, '', \XF::$time]
        );

        return $affected ? $token : null;
    }

    protected function releaseLock(int $operationId, string $token): void
    {
        $this->app->db()->update(
            'xf_warext_ucm_bulk_operation',
            ['lock_token' => '', 'lock_expires' => 0],
            'operation_id = ? AND lock_token = ?',
            [$operationId, $token]
        );
    }

    protected function failOperation(BulkOperationEntity $operation, string $message)
    {
        $operation->status = 'failed';
        $operation->last_error = mb_substr($message, 0, 2000);
        $operation->updated_date = \XF::$time;
        $operation->save();
        return $this->complete();
    }

    protected function decodeJson(string $json): array
    {
        if ($json === '') { return []; }
        $value = json_decode($json, true);
        return is_array($value) ? $value : [];
    }

    public function getStatusMessage()
    {
        return 'Warext Studios | Kullanıcı İçerik Yöneticisi toplu işlemi çalışıyor';
    }

    public function canCancel()
    {
        return true;
    }

    public function canTriggerByChoice()
    {
        return false;
    }
}
