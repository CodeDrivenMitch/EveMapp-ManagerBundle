<?php
namespace EveMapp\ManagerBundle\Controller;


use Imagick;
use ImagickPixel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Tempest;

/**
 * Controller for all Heat Map Actions. Makes use of the Tempest Library to generate the heat map images.
 *
 * Class HeatMapController
 * @package EveMapp\ManagerBundle\Controller
 */
class HeatMapController extends Controller
{
	const SAVE_DIR = "/var/www/html/web/bundles/manager/images/heatmap";

	public function getAction(Request $request, $eventId, $zoom, $day, $hour, $minutes)
	{
		// Require heat map library
		require_once('lib/Tempest.php');
		$heatMapData = array();

		// If event_id = -1, request comes from the editor. Get the session eventId var
		if($eventId == -1) {
			$eventId = $request->getSession()->get("edit_map_event");
		}

		// Get the event and its bounds
		$event = $this->getDoctrine()->getRepository("ManagerBundle:Event")->find($eventId);
		$bounds = $this->get('manager_o2a')->mapBoundsToArray($event->getBounds());


		// Get directory and file name for this heat map
		// We don't want to generate things twice
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

		// Well, that's not the case.
		// Check if directory exists and otherwise make it
		if (!is_dir($directory)) {
			mkdir($directory . '/', 0777, true);
		}

		// Calculate some scaling and set dates
		$degreePerPixel = ($bounds['xmax'] - $bounds['xmin']) / 650;
		$now = clone $event->getStartDate()->setTime($hour, $minutes, 0);;
		$past = clone $now;

		$now->modify(sprintf("+%d days", ($day - 1)));
		$past->modify(sprintf("+%d days", ($day - 1)));
		$past->modify("-5 minutes");


		// Get Relevant Location Entries
		$query = $this->getDoctrine()->getManager()->createQueryBuilder();
		$query->add('select', 'l')
			->add('from', 'ManagerBundle:LocationEntry l')
			->add('where', 'l.date < :end AND l.date > :start')
			->setParameter('end', $now)
			->setParameter('start', $past);
		$result = $query->getQuery()->getArrayResult();

		// If nothing return the blank.
		// Otherwise the Tempest library would throw an exception
		if(count($result) == 0) {
			return new BinaryFileResponse($this->getBlankForZoom($eventId, $bounds['zoom'], $zoom));
		}

		// Loop through antries and at them on the heatmap
		foreach ($result as $res) {
			$yoffset = (($res['latitude'] - $bounds['ymin']) / ($degreePerPixel * 0.5)) * pow(2, $zoom - $bounds['zoom']);
			$xoffset = (($res['longitude'] - $bounds['xmin']) / ($degreePerPixel)) * pow(2, $zoom - $bounds['zoom']);

			array_push($heatMapData, array($xoffset, $yoffset));
		}

		// Let Tempest do the work
		$heatmap = new Tempest(array(
			'input_file' => $this->getBlankForZoom($eventId, $bounds['zoom'], $zoom),
			'output_file' => $filename,
			'coordinates' => $heatMapData,
			'opacity' => 100
		));

		/**
		 * The next call creates the HeatMap, using a MODIFIED version of the Tempest library.
		 * The change makes it transparent, as opposed to opaque
		 */
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