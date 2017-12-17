<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ez\MenuBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use eZ\Bundle\EzPublishCoreBundle\Controller;

class MenuController extends Controller
{
    /**
     * Generates top menu.
     *
     * @param mixed|null $currentLocationId
     * @param string $template
     *
     * @return Response
     */
    public function topMenuAction($currentLocationId, $template)
    {
        if ($currentLocationId !== null) {
            $location = $this->getLocationService()->loadLocation($currentLocationId);
            if (isset($location->path[2])) {
                $secondLevelLocationId = $location->path[2];
            }
        }

        $response = new Response();

        $menu = $this->getMenu('top');
        $parameters = ['menu' => $menu];
        if (isset($secondLevelLocationId) && isset($menu[$secondLevelLocationId])) {
            $parameters['submenu'] = $menu[$secondLevelLocationId];
        }

        return $this->render($template, $parameters, $response);
    }

    /**
     * @param string $identifier
     *
     * @return \Knp\Menu\MenuItem
     */
    private function getMenu($identifier)
    {
        return $this->container->get("ez_menu.menu.$identifier");
    }

    /**
     * @return \eZ\Publish\API\Repository\LocationService
     */
    private function getLocationService()
    {
        return $this->container->get('ezpublish.api.service.location');
    }
}
