<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 6/11/14
 * Time: 11:54 AM
 */

namespace EveMapp\ManagerBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tempest;

class HeatMapController extends Controller
{
	const SAVE_DIR = "/var/www/html/web/bundles/manager/images/heatmap/";

	public function getAction()
	{
		// Require heat map library
		require_once('lib/Tempest.php');
		$heatMapData = array();

		$event = $this->getDoctrine()->getRepository("ManagerBundle:Event")->find(1);
		$bounds = $this->get('manager_o2a')->mapBoundsToArray($event->getBounds());

		$degreePerPixel = ($bounds['xmax'] - $bounds['xmin']) / 650;

		$now = new \DateTime();
		$past = new \DateTime();
		$past->modify("-1 day");

		$query = $this->getDoctrine()->getManager()->createQueryBuilder();
		$query->add('select', 'l')
			->add('from', 'ManagerBundle:LocationEntry l')
			->add('where', 'l.date < :end AND l.date > :start')
			->setParameter('end', $now)
			->setParameter('start', $past);




		$result = $query->getQuery()->getArrayResult();


		foreach ($result as $res) {
			$yoffset = ($res['latitude'] - $bounds['ymin']) / ($degreePerPixel * 0.5);
			$xoffset = ($res['longitude'] - $bounds['xmin']) / ($degreePerPixel);

			array_push($heatMapData, array($xoffset, $yoffset));
		}


		$heatmap = new Tempest(array(
			'input_file' => $this::SAVE_DIR . "1.png",
			'output_file' => $this::SAVE_DIR . "2.png",
			'coordinates' => $heatMapData,
			'opacity' => 50
		));

		$heatmap->render();

		return new Response('true');
	}

} 