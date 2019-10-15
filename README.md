# EzMenuBundle

`Ez demo site Menu Bundle` is an extract of [eZPlatform DemoBundle](https://github.com/ezsystems/DemoBundle) to create the menu using [KnpMenuBundle](http://symfony.com/doc/master/bundles/KnpMenuBundle/index.html)

This bundle helps developer to add first level menu and to keep this bundle as example for custom implementation.

## Requirement

eZPublish 5.x, eZPlatform 1.x (Open Source or EE)

Note: Be sure that [KnpMenuBundle](http://symfony.com/doc/master/bundles/KnpMenuBundle/index.html) is installed and activated in AppKernel.php
  
## Usage

```
    {{ render(controller('EzMenuBundle:Menu:topMenu', {
        'currentLocationId': currentLocation is defined ? currentLocation.id : null,
        'template': 'EzMenuBundle:menu:page_header_menu.html.twig'
    })) }}
```
