<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 6/10/14
 * Time: 6:51 PM
 */

namespace EveMapp\ManagerBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MapEditorTemplateController extends Controller
{

	/**
	 * Returns template based on entry type for the new rows.
	 * @param Request $request
	 * @param $type string Type of entry
	 * @throws \Exception
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function entryRowAction(Request $request, $type)
	{
		$index = $request->get("index");

		switch ($type) {
			case 'price':
				return $this->render('ManagerBundle:MapObjectInfo:templatePrice.html.twig', array(
					'i' => $index
				));
			case 'time':
				return $this->render('ManagerBundle:MapObjectInfo:templateTime.html.twig', array(
					'i' => $index,
					'date' => new \DateTime()
				));
		}

		throw new \Exception("Type not implemented!");
	}

	public function objectInfoAction($id)
	{
		$object = $this->getDoctrine()->getRepository("ManagerBundle:MapObject")->find($id);

		if (!$object) {
			throw new \Exception("Object does not exist!");
		}

		$template = "";
		$data = null;


		switch ($this->get('map_object_type_resolver')->getEntryType($object->getType())) {
			case 'Prices':
				$template = 'ManagerBundle:MapObjectInfo:objectInfoPrice.html.twig';
				$data = $object->getPriceEntries();
				break;
			case 'Timetable':
				$template = 'ManagerBundle:MapObjectInfo:objectInfoTime.html.twig';
				$data = $object->getLineUpEntries();
				break;
		}

		if ($template == "") {
			return new Response("Not implemented");
		}

		return $this->render($template, array(
			'description' => $object->getDescription(),
			'entries' => $data
		));
	}

} 