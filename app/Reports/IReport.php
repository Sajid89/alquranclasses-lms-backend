<?php

namespace App\Reports;

interface IReport {

    const DATE = 'd/m/Y';
    const DATE_TIME = 'd/m/Y H:i:s';
    const TIME = 'H:i:s';

    const ORGANIZATION_NAME = 'Alquran Classes';
    const ORGANIZATION_ADDRESS = 'Company Address. Tell: 091-00000000-00';

    /**
	 * it generates a report then either displays or download the report.
	 * 
	 * @param $destination by default it is 'I' which means the report
	 * will be displayed in the browser. If it is needed to download
	 * the report then the value of $destination must be passed i.e 'D'
	 * 
	 * @return output of pdf object which can be displayed in a browser
	 */
    public function pdf($destination = 'I');

    /**
	 * @return a file stream to be downloaded
	 */
    public function csv();
}
