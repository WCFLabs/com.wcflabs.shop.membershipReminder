<?php

/**
 * @author	Joshua Ruesweg
 * @copyright	2016-2022 WCFLabs.de
 * @license	GNU GENERAL PUBLIC LICENSE <https://www.gnu.org/licenses/gpl-3.0.txt>
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