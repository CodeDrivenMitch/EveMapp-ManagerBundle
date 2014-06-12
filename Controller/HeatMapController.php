<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 6/11/14
 * Time: 11:54 AM
 */

namespace EveMapp\ManagerBundle\Controller;


use Imagick;
use ImagickPixel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tempest;

class HeatMapController extends Controller
{
	const SAVE_DIR = "/var/www/html/web/bundles/manager/images/heatmap";

	public function getAction(Request $request, $eventId, $zoom, $day, $hour, $minutes)
	{
		// Some sanity checks
		if($zoom < 12) {
			throw new \Exception("Calm down johnny, zoom level should be higher than 11!");
		}

		// Require heat map library
		require_once('lib/Tempest.php');
		$heatMapData = array();

		if($eventId == -1) {
			$eventId = $request->getSession()->get("edit_map_event");
		}

		$event = $this->getDoctrine()->getRepository("ManagerBundle:Event")->find($eventId);
		$bounds = $this->get('manager_o2a')->mapBoundsToArray($event->getBounds());


		$directory = sprintf(
			"%s/%d/%d/%d/%d",
			$this::SAVE_DIR,
			$eventId,
			$day,
			$hour,
			$minutes,
			$zoom
		);
		$filename = $directory . '/' . $zoom . ".png";

		/// return image if it exists
		if (file_exists($filename)) {
			return new BinaryFileResponse($filename);
		}

		// Check if directory exists
		if (!is_dir($directory)) {
			mkdir($directory . '/', 0777, true);
		}

		$degreePerPixel = ($bounds['xmax'] - $bounds['xmin']) / 650;

		$now = clone $event->getStartDate()->setTime($hour, $minutes, 0);;
		$past = clone $now;

		$now->modify(sprintf("+%d days", ($day - 1)));
		$past->modify(sprintf("+%d days", ($day - 1)));
		$past->modify("-5 minutes");


		$query = $this->getDoctrine()->getManager()->createQueryBuilder();
		$query->add('select', 'l')
			->add('from', 'ManagerBundle:LocationEntry l')
			->add('where', 'l.date < :end AND l.date > :start')
			->setParameter('end', $now)
			->setParameter('start', $past);


		$result = $query->getQuery()->getArrayResult();

		if(count($result) == 0) {
			return new BinaryFileResponse($this->getBlankForZoom($eventId, $bounds['zoom'], $zoom));
		}

		foreach ($result as $res) {
			$yoffset = (($res['latitude'] - $bounds['ymin']) / ($degreePerPixel * 0.5)) * pow(2, $zoom - $bounds['zoom']);
			$xoffset = (($res['longitude'] - $bounds['xmin']) / ($degreePerPixel)) * pow(2, $zoom - $bounds['zoom']);

			array_push($heatMapData, array($xoffset, $yoffset));
		}

		//return new JsonResponse($result);


		$heatmap = new Tempest(array(
			'input_file' => $this->getBlankForZoom($eventId, $bounds['zoom'], $zoom),
			'output_file' => $filename,
			'coordinates' => $heatMapData,
			'opacity' => 100
		));

		$heatmap->render();

		return new BinaryFileResponse($filename);
	}

	private function getBlankForZoom($eventId, $oZoom, $zoom)
	{
		$filename = $this::SAVE_DIR . '/' . $eventId . '/blanks/';

		if (!is_dir($filename)) {
			mkdir($filename, 0777, true);
		}


		$filename = $filename . $zoom . '.png';

		if (!file_exists($filename)) {
			$width = 650 * pow(2, $zoom - $oZoom);
			$height = 400 * pow(2, $zoom - $oZoom);
			$img = new Imagick();
			$img->newimage($width, $height, new ImagickPixel('none'));
			$img->setImageFormat('png');
			$img->writeimage($filename);
			$img->destroy();
		}

		return $filename;
	}


} 