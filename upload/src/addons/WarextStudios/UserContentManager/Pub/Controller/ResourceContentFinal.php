<?php

namespace WarextStudios\UserContentManager\Pub\Controller;

use WarextStudios\UserContentManager\Service\BulkOperationQueue;
use XF\Mvc\ParameterBag;

class ResourceContentFinal extends ResourceContent
{
    public function actionBulk(ParameterBag $params)
    {
        $this->assertPostOnly();
        $visitor = \XF::visitor();

        if (!$visitor->hasPermission('warextUcm', 'view') || !$visitor->hasPermission('warextUcm', 'bulk'))
        {
            return $this->noPermission();
        }

        if (!class_exists('XFRM\\Entity\\ResourceItem'))
        {
            return $this->error('XenForo Resource Manager kurulu veya etkin değil.');
        }

        $scope = $this->filter('selection_scope', 'str');

        if ($scope !== 'filter')
        {
            return parent::actionBulk($params);
        }

        $user = $this->assertRecordExists('XF:User', $params->user_id);

        if (!$user->canViewFullProfile($error))
        {
            return $this->noPermission($error);
        }

        $action = $this->filter('bulk_action', 'str');
        $allowed = [
            'move', 'soft_delete', 'hard_delete', 'restore', 'approve', 'unapprove',
            'prefix', 'title_prepend', 'title_append', 'title_replace'
        ];

        if (!in_array($action, $allowed, true))
        {
            return $this->error(\XF::phrase('warext_ucm_invalid_bulk_action'));
        }

        if ($action === 'hard_delete')
        {
            if (!$visitor->hasPermission('warextUcm', 'hardDelete'))
            {
                return $this->noPermission();
            }

            if (!$this->filter('confirm_hard_delete', 'bool'))
            {
                return $this->error(\XF::phrase('warext_ucm_hard_delete_confirmation_required'));
            }
        }

        $handler = $this->repository('WarextStudios/UserContentManager:Content')->getHandler('resource');

        if (!$handler)
        {
            return $this->error('XenForo Resource Manager kurulu veya etkin değil.');
        }

        $filters = $this->getFilters();
        [, $visibleCategoryIds] = $this->getCategoryChoices();
        $finder = $this->buildFinder($user, $handler, $filters, $visibleCategoryIds);
        $matchCount = (clone $finder)->total();

        if ($matchCount <= self::IMMEDIATE_LIMIT)
        {
            return parent::actionBulk($params);
        }

        $targetCategoryId = $this->filter('target_category_id', 'uint');

        if ($action === 'move' && (!$targetCategoryId || !in_array($targetCategoryId, $visibleCategoryIds, true)))
        {
            return $this->error('Taşıma işlemi için erişebildiğiniz bir kaynak kategorisi seçmelisiniz.');
        }

        $options = [
            'target_category_id' => $targetCategoryId,
            'reason' => trim($this->filter('reason', 'str')),
            'prefix_id' => $this->filter('target_prefix_id', 'uint'),
            'title_value' => trim($this->filter('title_value', 'str')),
            'title_search' => $this->filter('title_search', 'str'),
            'title_replace' => $this->filter('title_replace', 'str')
        ];

        $operation = (new BulkOperationQueue())->enqueue(
            'resource',
            $visitor->user_id,
            $user->user_id,
            $action,
            $filters,
            $options,
            $matchCount
        );

        return $this->redirect(
            $this->buildLink('kullanici-kaynaklari', $user, $this->getFilterParams($filters)),
            'Toplu işlem #' . $operation->operation_id . ' iş kuyruğuna alındı. ' . $matchCount . ' kaynak arka planda güvenli batch işlemleriyle işlenecek.'
        );
    }
}
