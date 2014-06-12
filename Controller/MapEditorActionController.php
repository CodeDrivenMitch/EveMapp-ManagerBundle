<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 6/10/14
 * Time: 1:35 AM
 */

namespace EveMapp\ManagerBundle\Controller;


use EveMapp\ManagerBundle\Entity\MapObject;
use EveMapp\ManagerBundle\Entity\MapObjectLineUpEntry;
use EveMapp\ManagerBundle\Entity\MapObjectPrice;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MapEditorActionController extends Controller
{

	/**
	 * Creates a JSON response with all the maps parameters. This will be called when the map editor
	 * is loaded in the browser. This depends on the ObjectToArray Service, as does the Request equivalent.
	 * @param Request $request Request parameters and session
	 * @return \EveMapp\ManagerBundle\Controller\JsonResponse
	 * @throws \Exception
	 */
	public function loadAction(Request $request)
	{
		$session = $request->getSession();

		$eventId = $session->get("edit_map_event");
		$event = $this->getDoctrine()->getRepository("ManagerBundle:Event")->find($eventId);
		$objects = $this->getDoctrine()->getRepository("ManagerBundle:MapObject")->findByEventId($eventId);
		$bounds = $event->getBounds();


		$data = array(
			'bounds' => $this->get('manager_o2a')->mapBoundsToArray($bounds),
			'objects' => $this->get('manager_o2a')->mapObjectsToArray($objects),
			'dates' => array(
				'start' => $event->getStartDate(),
				'end' => $event->getEndDate()
			)
		);

		return new JsonResponse($data);

	}

	/**
	 * Saves or updates an object to the database, and returns the new or already existing ID.
	 * @param Request $request Request parameters and session
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function saveObjectAction(Request $request)
	{
		$data = $request->get('object', null);
		$em = $this->getDoctrine()->getManager();

		$mapObject = new MapObject();

		if($data['table_id'] != -1) {
			$mapObject = $em->getRepository("ManagerBundle:MapObject")->find($data['table_id']);

			if(!$mapObject) {
				throw new \Exception("Object does not exists, should not have an ID!");
			}
		}

		$mapObject = $this->get('manager_a2o')->arrayToMapObject($data, $mapObject);
		$mapObject->setEventId(intval($request->getSession()->get("edit_map_event")));
		$em->persist($mapObject);
		$em->flush();

		return new Response($mapObject->getId());
	}

	/**
	 * Deletes a MapObject from the database.
	 * @param Request $request Request parameters and session
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function deleteObjectAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$object = $em->getRepository("ManagerBundle:MapObject")->find($request->get('id'));

		if(!$object) {
			throw new \Exception('Object not found!');
		}

		$em->remove($object);
		$em->flush();

		return new Response('true');
	}

	/**
	 * Saves or updates an object entry to the database, and returns the new or existing ID.
	 * @param Request $request Request parameters and session
	 * @param $type String Type of the entry. Should be one of times or prices.
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function saveEntryAction(Request $request, $type)
	{
		$data = $this->getObjectDataFromRequest($request);

		switch ($type) {
			case 'price':
				return $this->savePriceEntry($data);
			case 'time':
				return $this->saveTimeEntry($data);
			default:
				throw new \Exception("Entry type is not implemented yet!");
		}
	}

	/**
	 * Reads the needed data from the request and decodes the JSON
	 * @param Request $request
	 * @param string $key
	 * @throws \Exception
	 * @return mixed
	 */
	private function getObjectDataFromRequest(Request $request, $key = 'entry')
	{
		$data = $request->get($key, null);

		if ($data == null) {
			throw new \Exception("Entry value should not be null!");
		}

		return json_decode($data, true);
	}

	private function savePriceEntry($data)
	{
		$em = $this->getDoctrine()->getManager();
		$entry = new MapObjectPrice();

		if ($data['id'] == -1) {

			// Create an ew entry

			$entry->setName($data['name']);
			$entry->setPrice($data['price']);

			// Find the object that it belongs to and add it
			$object = $this->getDoctrine()->getRepository("ManagerBundle:MapObject")->find($data['object_id']);
			if ($object) {
				$entry->setMapObject($object);
				$object->addPriceEntry($entry);

				// Persist
				$em->persist($entry);
				$em->persist($object);
				$em->flush();
			}
		} else {
			// Entry already has DB id, get it
			$entry = $this->getDoctrine()->getRepository("ManagerBundle:MapObjectPrice")->find($data['id']);

			// Should be found, otherwise it wouldn't have ID != -1
			if (!$entry) {
				throw new \Exception("Entry has not be found!");
			}

			// Update and save it
			$entry->setName($data['name']);
			$entry->setPrice($data['price']);
			$em->persist($entry);
			$em->flush();

		}
		return new Response($entry->getId());

	}

	private function saveTimeEntry($data)
	{

		$em = $this->getDoctrine()->getManager();
		$entry = new MapObjectLineUpEntry();

		if ($data['id'] == -1) {
			$entry = $this->get("manager_a2o")->arrayToPriceEntry($data, $entry);

			$object = $this->getDoctrine()->getRepository("ManagerBundle:MapObject")->find($data['object_id']);

			if ($object) {
				$entry->setMapObject($object);
				$object->addLineUpEntry($entry);

				$em->persist($entry);
				$em->persist($object);
				$em->flush();
			}
		} else {
			// Entry exists and should be updated
			$entry = $this->getDoctrine()->getRepository("ManagerBundle:MapObjectLineUpEntry")->find($data['id']);
			if (!$entry) {
				throw new \Exception("Entry has not be found!");
			}

			$entry = $this->get("manager_a2o")->arrayToPriceEntry($data, $entry);

			$em->persist($entry);
			$em->flush();

		}

		return new Response($entry->getId());
	}


	/**
	 * Deletes an object entry from the database.
	 * @param Request $request Request parameters and session
	 * @param $type String Type of the entry. Should be one of times or prices.
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function deleteEntryAction(Request $request, $type)
	{
		$data = $this->getObjectDataFromRequest($request);

		switch ($type) {
			case 'price':
				return $this->deletePriceEntry($data);
			case 'time':
				return $this->deleteTimeEntry($data);
			default:
				throw new \Exception("Entry type is not implemented yet!");
		}
	}

	private function deletePriceEntry($data)
	{
		$entry = $this->getDoctrine()->getRepository("ManagerBundle:MapObjectPrice")->find($data['id']);

		if ($entry) {
			$em = $this->getDoctrine()->getManager();
			$em->remove($entry);
			$em->flush();
		} else {
			throw new \Exception("Entry not found!");
		}

		return new Response('true');
	}

	private function deleteTimeEntry($data)
	{

		$entry = $this->getDoctrine()->getRepository("ManagerBundle:MapObjectLineUpEntry")->find($data['id']);

		if ($entry) {
			$em = $this->getDoctrine()->getManager();
			$em->remove($entry);
			$em->flush();
		} else {
			throw new \Exception("Entry not found!");
		}

		return new Response("true");
	}
} 