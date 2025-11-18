<?php
namespace Modules\maintenance_calendar;

use CModule;
use CMenuItem;

class Module extends CModule {
    public function init() {
        $menu = \App::getMenu()->findOrAdd('main')->getSubmenu();
        $reportsMenu = $menu->findByName('reports');
        if ($reportsMenu) {
            $reportsMenu->insertAfter(
                new CMenuItem('maintenance_calendar', _('Maintenance Calendar'))
                    ->setAction('maintenance.calendar')
                    ->setIcon('fas fa-calendar-alt'),
                'dashboard'
            );
        }
    }
}
