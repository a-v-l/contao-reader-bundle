<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\ModuleModel;
use Contao\StringUtil;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\ListBundle\Module\ModuleList;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;

class ListConfigElementType implements ConfigElementType
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @param ItemInterface            $item
     * @param ReaderConfigElementModel $readerConfigElement
     *
     * @return void|null
     */
    public function addToItemData(ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        $module = $this->framework->getAdapter(ModuleModel::class)->findById($readerConfigElement->listModule);

        if (null === $module) {
            return;
        }

        $listModule = $this->framework->createInstance(ModuleList::class, [$module]);
        /** @var FilterConfig $filterConfig */
        $filterConfig = $listModule->getFilterConfig();
        $filter = StringUtil::deserialize($readerConfigElement->initialFilter, true);

        if (!isset($filter[0]['filterElement']) || !isset($filter[0]['selector'])) {
            return;
        }

        $filterConfig->addContextualValue($filter[0]['filterElement'], $item->getRawValue($filter[0]['selector']));
        $filterConfig->initQueryBuilder();

        $lists = $item->getFormattedValue('list');

        $item->setFormattedValue('list', array_merge(\is_array($lists) ? $lists : [], [$readerConfigElement->listName => $listModule->generate()]));
    }
}
