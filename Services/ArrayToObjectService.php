<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 6/10/14
 * Time: 9:12 AM
 */

namespace EveMapp\ManagerBundle\Services;


use EveMapp\ManagerBundle\Entity\MapObject;
use EveMapp\ManagerBundle\Entity\MapObjectLineUpEntry;

class ArrayToObjectService
{
	public function arrayToPriceEntry($data, $object = null)
	{
		$entry = new MapObjectLineUpEntry();
		if ($object != null) {
			$entry = $object;
		}

		$entry->setPerformer($data['performer']);
		$entry->setStartTime(new \DateTime($data['startTime']['date']));
		$entry->setEndTime(new \DateTime($data['endTime']['date']));

		return $entry;

	}

	public function arrayToMapObject($object, MapObject $mapObject)
	{
		return $mapObject
			->setObjectId($object['object_id'])
			->setAngle($object['angle'])
			->setHeight($object['height'])
			->setWidth($object['width'])
			->setLat($object['lat'])
			->setLng($object['lng'])
			->setType($object['object_type'])
			->setUrl($object['image_url'])
			->setDescription($object['desc']);

	}
} 