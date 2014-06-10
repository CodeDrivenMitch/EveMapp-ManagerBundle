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

	public function getSubtoolAction($type)
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

		switch ($this->get('map_object_type_resolver')->getEntryType($objectType)) {
			case 'prices':
				return $this->showObjectWithPrices($objectInfo);
				break;
			case 'times':
				return $this->showObjectWithTimes($objectInfo);
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

	private function showObjectWithTimes($info)
	{
		return $this->render('ManagerBundle:MapObjectInfo:times.html.twig', array(
			'info' => $info
		));
	}

	public function mapObjectEditorAction()
	{
		return $this->render('ManagerBundle:MapObjectInfo:editPrices.html.twig');
	}
} 