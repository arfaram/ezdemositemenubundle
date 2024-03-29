<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ez\MenuBundle\Menu;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use eZ\Publish\Core\Helper\TranslationHelper;

/**
 * A simple eZ Publish menu provider.
 *
 * Generates a two level menu, starting from the configured root node.
 * Locations below the root node and until a relative depth of 2 are included.
 * Only visible locations with a ContentType included in `MenuContentSettings.TopIdentifierList` in legacy's `menu.ini`
 * are included.
 */
class Builder
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * @var TranslationHelper
     */
    private $translationHelper;

    public function __construct(
        FactoryInterface $factory,
        SearchService $searchService,
        RouterInterface $router,
        ConfigResolverInterface $configResolver,
        LocationService $locationService,
        TranslationHelper $translationHelper
    ) {
        $this->factory = $factory;
        $this->searchService = $searchService;
        $this->router = $router;
        $this->configResolver = $configResolver;
        $this->locationService = $locationService;
        $this->translationHelper = $translationHelper;
    }

    public function createTopMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        $this->addLocationsToMenu(
            $menu,
            $this->getMenuItems(
                $this->configResolver->getParameter('content.tree_root.location_id')
            )
        );

        return $menu;
    }

    /**
     * Adds locations from $searchHit to $menu.
     *
     * @param ItemInterface $menu
     * @param SearchHit[] $searchHits
     */
    private function addLocationsToMenu(ItemInterface $menu, array $searchHits)
    {
        foreach ($searchHits as $searchHit) {
            /** @var Location $location */
            $location = $searchHit->valueObject;
            $menuItem = isset($menu[$location->parentLocationId]) ? $menu[$location->parentLocationId] : $menu;
            $menuItem->addChild(
                $location->id,
                [
                    'label' => $this->translationHelper->getTranslatedContentNameByContentInfo($location->contentInfo),
                    'uri' => $this->router->generate($location),
                    'attributes' => ['id' => 'nav-location-' . $location->id],
                ]
            );
            $menuItem->setChildrenAttribute('class', 'nav');
        }
    }

    /**
     * Queries the repository for menu items, as locations filtered on the list in TopIdentifierList in menu.ini.
     * @param int|string $rootLocationId Root location for menu items. Only two levels below this one are searched
     * @return SearchHit[]
     */
    private function getMenuItems($rootLocationId)
    {
        $rootLocation = $this->locationService->loadLocation($rootLocationId);

        $query = new LocationQuery();

        $query->query = new Criterion\LogicalAnd([
            new Criterion\ContentTypeIdentifier($this->getTopMenuContentTypeIdentifierList()),
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\Location\Depth(Criterion\Operator::EQ, $rootLocation->depth + 1),
            new Criterion\Subtree($rootLocation->pathString),
            new Criterion\LanguageCode($this->configResolver->getParameter('languages')),
        ]);
        $query->sortClauses = [new Query\SortClause\Location\Priority(Query::SORT_DESC)];
        $query->performCount = false;

        return $this->searchService->findLocations($query)->searchHits;
    }

    private function getTopMenuContentTypeIdentifierList()
    {
        return $this->configResolver->getParameter('MenuContentSettings.TopIdentifierList', 'menu');
    }
}
