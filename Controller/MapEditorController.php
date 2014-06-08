<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 5/26/14
 * Time: 3:12 PM
 */

namespace EveMapp\ManagerBundle\Controller;


use EveMapp\ManagerBundle\Entity\MapObject;
use EveMapp\ManagerBundle\Entity\MapObjectImage;
use EveMapp\ManagerBundle\Entity\MapObjectPrice;
use EveMapp\ManagerBundle\Form\Type\MapObjectImageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MapEditorController extends Controller
{

	public function getBoundsAction(Request $request)
	{
		$session = $request->getSession();

		$eventId = $session->get("edit_map_event");
		$event = $this->getDoctrine()->getRepository("ManagerBundle:Event")->find($eventId);

		$bounds = $event->getBounds();


		$data = array();
		if ($bounds) {
			$data['bounds'] = array(
				'xmin' => $bounds->getLatLow(),
				'xmax' => $bounds->getLatHigh(),
				'ymin' => $bounds->getLngLow(),
				'ymax' => $bounds->getLngHigh()
			);
		} else return new Response("false");

		$repository = $this->getDoctrine()->getRepository("ManagerBundle:MapObject");

		$objects = $repository->findByEventId($eventId);

		$data['objects'] = array();
		foreach ($objects as $object) {

			$entries = array();

			// Determine which property this mapObject should have and act accordingly
			switch ($this->getObjectInfoByType($object->getType())) {
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

			}


			array_push($data['objects'], array(
				'width' => $object->getWidth(),
				'height' => $object->getHeight(),
				'object_id' => $object->getObjectId(),
				'angle' => $object->getAngle(),
				'image_url' => $object->getUrl(),
				'lat' => $object->getLat(),
				'lng' => $object->getLng(),
				'object_type' => $object->getType(),
				'table_id' => $object->getId(),
				'object_info' => array(
					'desc' => $object->getDescription(),
					'entries' => $entries
				)
			));


		}

		return new JsonResponse($data);
	}

	private function getObjectInfoByType($objectType)
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

	public function getSubtoolAction(Request $request, $type)
	{
		switch ($type) {
			case "createToolButton":
				return $this->render('ManagerBundle:Editor:createToolSubChoice.html.twig', array());
				break;
			case "infoToolButton":


				$form = $this->createForm(new MapObjectImageType, new MapObjectImage());
				return $this->render('ManagerBundle:Editor:infoToolSubChoice.html.twig', array(
					'form' => $form->createView()));
		}

		return new Response("false");
	}

	public function getUploadedImagesAction(Request $request)
	{
		$images = $this->getImages($request->getSession()->get("edit_map_event"));
		return $this->render('ManagerBundle:Editor:infoToolUploadedImages.html.twig', array(
			'images' => $images
		));
	}

	private function getImages($eventId)
	{
		$repository = $this->getDoctrine()->getRepository("ManagerBundle:MapObjectImage");
		return $repository->findByEventId($eventId);

	}

	public function saveAction(Request $request)
	{
		$data = json_decode($request->request->get('saveData', "false"), true);
		$repository = $this->getDoctrine()->getRepository("ManagerBundle:MapObject");
		$repositoryPrices = $this->getDoctrine()->getRepository("ManagerBundle:MapObjectPrice");
		$em = $this->getDoctrine()->getManager();

		// Execute deletes
		foreach ($data['deleted'] as $del) {
			$oldObject = $repository->findOneBy(array(
				"eventId" => $request->getSession()->get("edit_map_event"),
				"objectId" => $del
			));
			if ($oldObject) {
				$em->remove($oldObject);

			}
		}

		// update where needed
		foreach ($data['objects'] as $object) {
			$newObject = new MapObject();
			$oldObject = $repository->findOneBy(array(
				"eventId" => $request->getSession()->get("edit_map_event"),
				"objectId" => $object['object_id']
			));


			if ($oldObject) {
				$newObject = $oldObject;
			}

			foreach ($object['object_info']['entries'] as $entry) {

				switch ($this->getObjectInfoByType($object['object_type'])) {
					case 'prices':

						$priceEntry = $repositoryPrices->find($entry['id']);

						if (!$priceEntry) {
							$priceEntry = new MapObjectPrice();
						}

						if ($entry['name'] != "") {
							$priceEntry->setName($entry['name']);
							$priceEntry->setPrice($entry['price']);

							$priceEntry->setMapObject($newObject);
							$newObject->addPriceEntry($priceEntry);
						} else {
							if ($priceEntry) {
								$newObject->removePriceEntry($priceEntry);
								$em->remove($priceEntry);
							}
						}
						break;
				}

			}

			$newObject
				->setEventId($request->getSession()->get("edit_map_event"))
				->setObjectId($object['object_id'])
				->setAngle($object['angle'])
				->setHeight($object['height'])
				->setWidth($object['width'])
				->setLat($object['lat'])
				->setLng($object['lng'])
				->setType($object['object_type'])
				->setUrl($object['image_url'])
				->setDescription($object['object_info']['desc']);
			$em->persist($newObject);

			foreach ($newObject->getPriceEntries() as $priceEntry) {
				$em->persist($priceEntry);
			}

		}

		$em->flush();

		return new Response("true");
	}

	public function uploadImageAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(new MapObjectImageType(), new MapObjectImage());
		$repository = $this->getDoctrine()->getRepository("ManagerBundle:MapObjectImage");

		$form->handleRequest($request);
		$form->getData()->setEventId($request->getSession()->get("edit_map_event"));


		if ($form->isValid()) {

			$image = $form->getData();
			$em->persist($image);
			$em->flush();
			return new Response($repository->find($image->getId())->getWebPath());

		}
		return new Response("false");


	}

	public function deleteImageAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$repository = $this->getDoctrine()->getRepository("ManagerBundle:MapObjectImage");

		$image = $repository->find($id);
		if ($image->getEventId() == $request->getSession()->get("edit_map_event")) {
			$em->remove($image);
			$em->flush();
			return new Response("true");
		}
		return new Response("false");
	}

	public function showObjectInfoAction(Request $request)
	{
		$objectType = $request->get("object_type", null);
		$objectInfo = $request->get("object_info", array());

		if ($objectType == null) {
			return new Response("false");
		}

		switch ($this->getObjectInfoByType($objectType)) {
			case 'prices':
				return $this->showObjectWithPrices($objectInfo);
				break;
			default:
				return new Response("This one is not implemented yet!");
				break;
		}


	}

	private function showObjectWithPrices($info)
	{
		return $this->render('ManagerBundle:MapObjectInfo:prices.html.twig', array(
			'info' => $info
		));
	}

	public function mapObjectEditorAction()
	{
		return $this->render('ManagerBundle:MapObjectInfo:editPrices.html.twig');
	}

	public function  priceEntryDeleteAction(Request $request)
	{
		$data = json_decode($request->get('value'), true);
		$entry = $this->getDoctrine()->getRepository("ManagerBundle:MapObjectPrice")->find($data['id']);

		if ($entry) {
			$em = $this->getDoctrine()->getManager();
			$em->remove($entry);
			$em->flush();
		}

		return new Response('true');
	}

	public function  priceEntrySaveAction(Request $request)
	{
		$data = json_decode($request->get('value'), true);
		$em = $this->getDoctrine()->getManager();

		if ($data['id'] == -1) {

			$entry = new MapObjectPrice();
			$entry->setName($data['name']);
			$entry->setPrice($data['price']);

			$object = $this->getDoctrine()->getRepository("ManagerBundle:MapObject")->find($data['object_id']);
			if ($object) {
				$entry->setMapObject($object);
				$object->addPriceEntry($entry);

				$em->persist($entry);
				$em->persist($object);
				$em->flush();

				return new Response($entry->getId());
			}
		}

		return new Response('false');
	}
} 