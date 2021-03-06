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

            new MenuItemModel('dashboard', 'Dashboard', 'ad2_index', array(/* options */), 'iconclasses fa fa-tachometer'),
            new MenuItemModel('ecommerce', 'Ecommerce', false),

            new MenuItemModel('moderation', 'Modération', 'ad2_moderation_list', array(/* options */), 'iconclasses fa fa-chevron-circle-down'),
            new MenuItemModel('orders', 'Commandes', 'ad2_orders_list', array(/* options */), 'iconclasses fa fa-envelope-o'),
            new MenuItemModel('user', 'Utilisateurs', 'ad2_user_list', array(/* options */), 'iconclasses fa fa-plane'),
            new MenuItemModel('products', 'Produits', 'ad2_product_list', array(/* options */), 'iconclasses fa fa-cube'),
            new MenuItemModel('comments', 'Commentaires', 'ad2_comments_list', array(/* options */), 'iconclasses fa fa-comments'),
            new MenuItemModel('user-messages', 'Messages', 'ad2_messages', [], 'iconclasses fa fa-envelope'),
            new MenuItemModel('user-litiges', 'Litiges', 'ad2_disputes', [], 'iconclasses fa fa-graduation-cap'),


            new MenuItemModel('blog', 'Blog', false),
            new MenuItemModel('blog-article', 'Articles', 'ad2_blog_article_list', [], 'iconclasses fa fa-file-text-o'),
            new MenuItemModel('blog_category', 'Catégories de blog', 'ad2_blog_categories_list', [], 'iconclasses fa fa-bars'),


            new MenuItemModel('configuration', 'Configuration', false),
            new MenuItemModel('category', 'Catégories', 'ad2_categories_list', [], 'iconclasses fa fa-bars'),
            new MenuItemModel('collection', 'Tendances', 'ad2_collections_list', [], 'iconclasses fa fa-bars'),
            new MenuItemModel('attribute', 'Attributs', 'ad2_attribute_list', [/* options */], 'iconclasses fa fa-wrench '),
            new MenuItemModel('ref', 'Référentiel', 'ad2_ref_list', [/* options */], 'iconclasses fa fa-wrench '),
            new MenuItemModel('order_status', 'Statut de commande', 'ad2_order_status_list', [/* options */], 'iconclasses fa fa-wrench '),
            new MenuItemModel('product_status', 'Statut de produit', 'ad2_product_status_list', [/* options */], 'iconclasses fa fa-wrench '),
            new MenuItemModel('fee_rates', 'Taux de commission', 'ad2_fee_rate_list', [/* options */], 'iconclasses fa fa-wrench '),
            new MenuItemModel('currency', 'Devises', 'ad2_currency_list', [/* options */], 'iconclasses fa fa-wrench '),
        ];


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