<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 6/8/14
 * Time: 10:15 PM
 */

namespace EveMapp\ManagerBundle\Services;


class MapObjectEntryTypeResolver
{
	public function getEntryType($objectType)
	{
		if (in_array($objectType, array(
			"FoodStand", "Toilet", "MarketStall"
		))
		) {
			return 'Prices';
		}

		if (in_array($objectType, array(
			"Stage"
		))
		) {
			return 'Timetable';
		}

		return 'none';
	}
} 