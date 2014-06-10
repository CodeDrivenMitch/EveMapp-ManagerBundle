<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 6/10/14
 * Time: 7:39 PM
 */

namespace EveMapp\ManagerBundle\Controller;


use EveMapp\ManagerBundle\Entity\MapObjectImage;
use EveMapp\ManagerBundle\Form\Type\MapObjectImageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MapEditorImageController extends Controller
{

	/**
	 * @param Request $request
	 * @param $id
	 * @return Response
	 */
	public function deleteAction(Request $request, $id)
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

	/**
	 * @param Request $request
	 * @return Response
	 */
	public function uploadAction(Request $request)
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

	/**
	 * @param Request $request
	 * @return Response
	 */
	public function getUploadsAction(Request $request)
	{
		$images = $this->getImages($request->getSession()->get("edit_map_event"));
		return $this->render('ManagerBundle:Editor:infoToolUploadedImages.html.twig', array(
			'images' => $images
		));
	}

	/**
	 * @param $eventId
	 * @return mixed
	 */
	private function getImages($eventId)
	{
		$repository = $this->getDoctrine()->getRepository("ManagerBundle:MapObjectImage");
		return $repository->findByEventId($eventId);
	}

} 