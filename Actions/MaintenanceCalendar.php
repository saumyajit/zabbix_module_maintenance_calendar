<?php

namespace Modules\maintenance_calendar\Actions;

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
        // Logic to fetch scheduled maintenance data via Zabbix API
        $maintenanceData = []; // Implement logic here to retrieve scheduled maintenance data
        
        $data = ['maintenance_data' => $maintenanceData];
        $response = new CControllerResponseData($data);
        $this->setResponse($response);
    }
}
