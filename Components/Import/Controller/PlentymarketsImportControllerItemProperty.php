<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013 plentymarkets GmbH
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License, supplemented by an additional
 * permission, and of our proprietary license can be found
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "plentymarkets" is a registered trademark of plentymarkets GmbH.
 * "shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, titles and interests in the
 * above trademarks remain entirely with the trademark owners.
 *
 * @copyright Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

require_once PY_SOAP . 'Models/PlentySoapRequest/GetPropertyGroups.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetProperties.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemPropertyGroup.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemPropertyOption.php';

/**
 * Imports the item properties
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportControllerItemProperty
{

	/**
	 * Performs the actual import
	 *
	 * @param integer $lastUpdateTimestamp
	 */
	public function run($lastUpdateTimestamp)
	{
		$Request_GetPropertyGroups = new PlentySoapRequest_GetPropertyGroups();
		$Request_GetPropertyGroups->Lang = 'de';
		$Request_GetPropertyGroups->LastUpdateFrom = $lastUpdateTimestamp;
		$Request_GetPropertyGroups->Page = 0;

		do
		{
			$Response_GetPropertyGroups = PlentymarketsSoapClient::getInstance()->GetPropertyGroups($Request_GetPropertyGroups);
			$Response_GetPropertyGroups instanceof PlentySoapResponse_GetPropertyGroups;

			foreach ($Response_GetPropertyGroups->PropertyGroups->item as $Option)
			{
				$PlentymarketsImportEntityItemPropertyGroup = new PlentymarketsImportEntityItemPropertyGroup($Option);
				$PlentymarketsImportEntityItemPropertyGroup->import();
			}
		}
		while (++$Request_GetPropertyGroups->Page < $Response_GetPropertyGroups->Pages);

		$Request_GetProperties = new PlentySoapRequest_GetProperties();
		$Request_GetProperties->Lang = 'de';
		$Request_GetProperties->LastUpdateFrom = $lastUpdateTimestamp;
		$Request_GetProperties->Page = 0;

		do
		{
			$Response_GetProperties = PlentymarketsSoapClient::getInstance()->GetProperties($Request_GetProperties);
			$Response_GetProperties instanceof PlentySoapResponse_GetProperties;

			foreach ($Response_GetProperties->Properties->item as $Option)
			{
				$PlentymarketsImportEntityItemPropertyOption = new PlentymarketsImportEntityItemPropertyOption($Option);
				$PlentymarketsImportEntityItemPropertyOption->import();
			}
		}
		while (++$Request_GetProperties->Page < $Response_GetProperties->Pages);
	}
}