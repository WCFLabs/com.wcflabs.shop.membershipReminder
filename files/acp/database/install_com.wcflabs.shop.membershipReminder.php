<?php

/**
 * @author	Joshua Ruesweg
 * @copyright	2016-2022 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('shop1_membership')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('remindedToDate')
                ->defaultValue(0),
        ]),
];