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
        $menuItems = array(
            new MenuItemModel('moderation', 'Modération', 'ad2_moderation_list', array(/* options */), 'iconclasses fa fa-chevron-circle-down'),
            $user = new MenuItemModel('user', 'Utilisateurs', 'ad2_user_list', array(/* options */), 'iconclasses fa fa-plane'),
            $ref = new MenuItemModel('ref', 'Référentiel', 'ad2_ref_list', array(/* options */), 'iconclasses fa fa-wrench '),
            $config = new MenuItemModel('config', 'Configurations', 'ad2_user_list', array(/* options */), 'iconclasses fa fa-wrench ')
        );

        $user->addChild(new MenuItemModel('user-list', 'Recherche & liste', 'ad2_user_list', array(), 'fa fa-user'));
        $user->addChild(new MenuItemModel('user-messages', 'Messages', 'ad2_user_list'));


        $config->addChild(new MenuItemModel('category', 'Catégories', 'ad2_categories_list'));

        return $this->activateByRoute($request->get('_route'), $menuItems);
    }

    protected function activateByRoute($route, $items) {

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