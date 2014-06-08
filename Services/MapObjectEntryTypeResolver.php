<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 6/8/14
 * Time: 10:15 PM
 */

namespace EveMapp\ManagerBundle\Services;


class MapObjectEntryTypeResolver {
	public function getEntryType($objectType)
	{
		switch ($objectType) {
			case "FoodStand":
				return 'prices';
				break;
			case "Toilet":
				return 'prices';
				break;
			case "MarketStall":
				return 'prices';
				break;
			default:
				return 'none';
				break;
		}
	}
} 