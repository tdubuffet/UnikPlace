<?php
namespace Admin2Bundle\EventListener;

// ...

use Avanzu\AdminThemeBundle\Model\MenuItemModel;
use Avanzu\AdminThemeBundle\Event\SidebarMenuEvent;
use Symfony\Component\HttpFoundation\Request;

class MenuItemListListener {

    // ...

    public function onSetupMenu(SidebarMenuEvent $event) {

        $request = $event->getRequest();

        foreach ($this->getMenu($request) as $item) {
            $event->addItem($item);
        }

    }

    protected function getMenu(Request $request) {
        // Build your menu here by constructing a MenuItemModel array
        $menuItems = [

            new MenuItemModel('ecommerce', 'Ecommerce', false),

            new MenuItemModel('moderation', 'Modération', 'ad2_moderation_list', [/* options */], 'iconclasses fa fa-chevron-circle-down'),
            $user = new MenuItemModel('user', 'Utilisateurs', 'ad2_user_list', [/* options */], 'iconclasses fa fa-plane'),

            new MenuItemModel('configuration', 'Ecommerce', false),
            new MenuItemModel('category', 'Catégories', 'ad2_categories_list', [], 'iconclasses fa fa-bars'),
            new MenuItemModel('collection', 'Tendances', 'ad2_collections_list', [], 'iconclasses fa fa-bars'),
            new MenuItemModel('ref', 'Référentiel', 'ad2_ref_list', [/* options */], 'iconclasses fa fa-wrench '),
        ];

        $user->addChild(new MenuItemModel('user-list', 'Recherche & liste', 'ad2_user_list', [], 'fa fa-user'));
        $user->addChild(new MenuItemModel('user-messages', 'Messages', 'ad2_user_list'));


        return $this->activateByRoute($request->get('_route'), $menuItems);
    }

    protected function activateByRoute($route, $items) {
        /** @var MenuItemModel $item */
        foreach($items as $item) {
            if($item->hasChildren()) {
                $this->activateByRoute($route, $item->getChildren());
            }
            else {
                if($item->getRoute() == $route) {
                    $item->setIsActive(true);
                }
            }
        }

        return $items;
    }

}