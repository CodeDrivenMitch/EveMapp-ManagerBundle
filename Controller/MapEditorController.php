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
use EveMapp\ManagerBundle\Form\Type\MapObjectImageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MapEditorController extends Controller {

	public function getBoundsAction(Request $request) {
		$session = $request->getSession();

		$eventId = $session->get("edit_map_event");
		$repository = $this->getDoctrine()->getRepository("ManagerBundle:EventBounds");

		$bounds = $repository->findOneByEventId($eventId);

		$data = array();
		if($bounds) {
			$data['bounds'] = array(
				'xmin' => $bounds->getLatLow(),
				'xmax' => $bounds->getLatHigh(),
				'ymin' =>$bounds->getLngLow(),
				'ymax' => $bounds ->getLngHigh()
				);
			$data['zoom'] = $bounds->getZoom();
		} else return new Response("false");

		$repository = $this->getDoctrine()->getRepository("ManagerBundle:MapObject");

		$objects = $repository->findByEventId($eventId);

		$data['objects'] = array();
		foreach($objects as $object) {
			array_push($data['objects'], array(
				'width' => $object->getWidth(),
				'height' => $object->getHeight(),
				'object_id' => $object->getObjectId(),
				'angle' => $object->getAngle(),
				'image_url' => $object->getUrl(),
				'lat' => $object->getLat(),
				'lng' => $object->getLng(),
				'object_type' => $object->getType()
			));
		}

		return new JsonResponse($data);
	}

	public function getSubtoolAction(Request $request, $type) {
		switch($type) {
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

	public function getUploadedImagesAction(Request $request) {
		$images = $this->getImages($request->getSession()->get("edit_map_event"));
		return $this->render('ManagerBundle:Editor:infoToolUploadedImages.html.twig', array(
			'images' => $images
		));
	}

	private function getImages($eventId) {
		$repository = $this->getDoctrine()->getRepository("ManagerBundle:MapObjectImage");
		return $repository->findByEventId($eventId);

	}

	public function saveAction(Request $request) {
		$data = json_decode($request->request->get('saveData', "false"), true);
		$repository = $this->getDoctrine()->getRepository("ManagerBundle:MapObject");
		$em = $this->getDoctrine()->getManager();

		// Execute deletes
		foreach($data['deleted'] as $del) {
			$oldObject = $repository->findOneBy(array(
				"eventId" => $request->getSession()->get("edit_map_event"),
				"objectId" => $del
			));
			if($oldObject) {
				$em->remove($oldObject);

			}
		}

		// update where needed
		foreach($data['objects'] as $object) {
			$newObject = new MapObject();
			$oldObject = $repository->findOneBy(array(
				"eventId" => $request->getSession()->get("edit_map_event"),
				"objectId" => $object['object_id']
			));

			if($oldObject) {
				$newObject = $oldObject;
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
				->setUrl($object['image_url']);
			$em->persist($newObject);

		}

		$em->flush();

		return new Response("true");
	}

	public function uploadImageAction(Request $request) {
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

	public function deleteImageAction(Request $request, $id) {
		$em = $this->getDoctrine()->getManager();
		$repository = $this->getDoctrine()->getRepository("ManagerBundle:MapObjectImage");

		$image = $repository->find($id);
		if($image->getEventId() == $request->getSession()->get("edit_map_event")) {
			$em->remove($image);
			$em->flush();
			return new Response("true");
		}
		return new Response("false");
	}
} 