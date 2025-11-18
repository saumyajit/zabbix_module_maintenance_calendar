<?php
namespace Modules;

use CModule;
use CMenuItem;

class Module extends CModule {
    public function init() {
        // Register menu item under Reports
        $menu = \App::getMenu()->findOrAdd('main')->getSubmenu();
        $reportsMenu = $menu->findByName('reports');
        if ($reportsMenu) {
            $reportsMenu->insertAfter(
                new CMenuItem('maintenance_calendar', 'Maintenance Calendar')
                    ->setAction('maintenance.calendar')
                    ->setIcon('fas fa-calendar-alt'),
                'dashboard'
            );
        }
    }
}
