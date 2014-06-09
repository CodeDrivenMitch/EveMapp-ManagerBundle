<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 6/8/14
 * Time: 10:29 PM
 */

namespace EveMapp\ManagerBundle\Services;


use Symfony\Component\Validator\Constraints\DateTime;

class ObjectToArrayService
{

	private $typeResolver;

	public function __construct(MapObjectEntryTypeResolver $resolver) {
		$this->typeResolver = $resolver;

	}
	public function mapObjectsToArray($objects)
	{
		$data = array();

		foreach ($objects as $object) {

			$entries = array();

			// Determine which property this mapObject should have and act accordingly
			switch ($this->typeResolver->getEntryType($object->getType())) {
				case 'prices':
					foreach ($object->getPriceEntries() as $entry) {
						array_push($entries, array(
							'id' => $entry->getId(),
							'name' => $entry->getName(),
							'price' => $entry->getPrice()
						));
					}

					// Add an empty row, so it gets shown upon opening the editor
					if (count($entries) == 0) {
						array_push($entries, array(
							'id' => -1,
							'name' => '',
							'price' => 0
						));
					}
					break;

				case 'times':
					foreach($object->getLineUpEntries() as $entry) {
						array_push($entries, array(
							'id' => $entry->getId(),
							'performer' => $entry->getPerformer(),
							'startTime' => $entry->getStartTime(),
							'endTime' => $entry->getEndTime()
						));
					}

					if (count($entries) == 0) {
						array_push($entries, array(
							'id' => -1,
							'performer' => '',
							'startTime' => new \DateTime(),
							'endTime' => new \DateTime()
						));
					}
					break;

			}


			array_push($data, array(
				'width' => $object->getWidth(),
				'height' => $object->getHeight(),
				'id' => $object->getObjectId(),
				'angle' => $object->getAngle(),
				'image_url' => $object->getUrl(),
				'lat' => $object->getLat(),
				'lng' => $object->getLng(),
				'type' => $object->getType(),
				'table_id' => $object->getId(),
				'desc' => $object->getDescription(),
				'entries' => $entries

			));
		}

		return $data;
	}

	public function mapBoundsToArray($bounds) {
		return array(
			'xmin' => $bounds->getLatLow(),
			'xmax' => $bounds->getLatHigh(),
			'ymin' => $bounds->getLngLow(),
			'ymax' => $bounds->getLngHigh()
		);
	}
}