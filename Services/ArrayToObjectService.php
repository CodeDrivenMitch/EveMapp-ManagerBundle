<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 6/10/14
 * Time: 9:12 AM
 */

namespace EveMapp\ManagerBundle\Services;


use EveMapp\ManagerBundle\Entity\MapObjectLineUpEntry;

class ArrayToObjectService {
	private $typeResolver;

	public function __construct(MapObjectEntryTypeResolver $resolver) {
		$this->typeResolver = $resolver;
	}

	public function arrayToPriceEntry($data, $object=null) {
		$entry = new MapObjectLineUpEntry();
		if($object != null) {
			$entry = $object;
		}

		$entry->setPerformer($data['performer']);
		$entry->setStartTime(new \DateTime($data['startTime']['date']));
		$entry->setEndTime(new \DateTime($data['endTime']['date']));

		return $entry;

	}
} 