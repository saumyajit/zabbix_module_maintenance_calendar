<?php

namespace Modules\MaintenanceCalendar\Actions;

use CController,
    CControllerResponseData;

class MaintenanceCalendar extends CController {

    public function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return true;
    }

    protected function checkPermissions(): bool {
        return true;
    }

    protected function doAction(): void {
        // Logic to get the data of scheduled maintenance via Zabbix API
        $maintenanceData = []; // Implement the logic to fetch maintenance data
        
        $data = ['maintenance_data' => $maintenanceData];
        $response = new CControllerResponseData($data);
        $this->setResponse($response);
    }
}
