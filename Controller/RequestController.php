<?php

namespace EveMapp\ManagerBundle\Controller;

use EveMapp\ManagerBundle\Entity\LocationEntry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use EveMapp\ManagerBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestController extends Controller
{

	public function registerAction()
	{
		$request = Request::createFromGlobals();
		$userId = $request->get("id", null);
		$friendList = $request->get("friends", null);
		$privacy = $request->get("privacysetting", null);

		if ($userId == null) return $this->throwParNotFoundException("id");
		if ($friendList == null) return $this->throwParNotFoundException("friends");
		if ($privacy == null) return $this->throwParNotFoundException("privacysetting");

		// Get doctrine manager
		$em = $this->getDoctrine()->getManager();


		$user = $this->getDoctrine()->getRepository("ManagerBundle:User")->find($userId);


		if ($user) {
			$user->setLastRegistered(new \DateTime());
			$user->setFriends(explode(",", $friendList));
			$user->setPrivacysetting($privacy);


		} else {
			$user = new User();
			$user->setFirstRegistered(new \DateTime());
			$user->setLastRegistered(new \DateTime());
			$user->setId($userId);
			$user->setPrivacysetting($privacy);
			$user->setFriends(explode(",", $friendList));
		}

		// persist and clean up
		$em->persist($user);
		$em->flush();
		return $this->createSuccessResponse();

	}

	/**
	 * TODO: Implement boundary check, not done yet because of testing reasons
	 * @param Request $request post request object
	 * @return Response json page
	 */

	public function locationSelfAction(Request $request)
	{
		// Get parameters
		$lat = $request->get("lat", null);
		$lng = $request->get("lng", null);
		$userId = $request->get("id", null);
		$eventId = $request->get("event_id", null);

		// Validate parameters
		if ($userId == null) return $this->throwParNotFoundException("id");
		if ($lat == null) return $this->throwParNotFoundException("lat");
		if ($lng == null) return $this->throwParNotFoundException("lng");
		if ($eventId == null) return $this->throwParNotFoundException("event_id");

		// Get Doctrine Entity Manager
		$em = $this->getDoctrine()->getManager();

		// Validate if user is registered
		if (!$this->getDoctrine()->getRepository("ManagerBundle:User")->find($userId))
			return $this->throwException("User not registered");

		// Retrieve the event and its bounds
		// $eventbounds = $this->getDoctrine()->getRepository("ManagerBundle:Event")->find($eventId)->getEventBounds();

		// Create new entry
		$entry = new LocationEntry();
		$entry->setLatitude(floatval($lat))
			->setLongitude(floatval($lng))
			->setUserId($userId)
			->setDate(new \DateTime())
			->setEventId($eventId);

		$em->persist($entry);
		$em->flush();

		return $this->createSuccessResponse();

	}


	/**
	 * Creates json with the location of other people, for use in the mobile app.
	 * Checks the user against the friends table to find whether location may be sent or not.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return JsonResponse|Response Location of other people
	 */
	public function locationOthersAction(Request $request)
	{
		$userId = $request->get("id", null);
		$eventId = $request->get("event_id", null);

		if ($userId == null) return $this->throwParNotFoundException("userId");
		if ($eventId == null) return $this->throwParNotFoundException("eventId");

		$userRepo = $this->getDoctrine()->getRepository("ManagerBundle:User");
		$self = $userRepo->find($userId);

		if (!$self) return $this->throwException("User not registered");

		$locRepo = $this->getDoctrine()->getRepository('ManagerBundle:LocationEntry');

		$entries = array();
		foreach ($self->getFriends() as $friendId) {
			$friend = $userRepo->find($friendId);

			if ($friend && $friend->getPrivacysetting() == "full") {
				$friendEntry = $locRepo->findOneBy(
					array('userId' => $friend->getId(),
						'eventId' => $eventId),
					array('date' => 'desc')

				);

				if ($friendEntry) {
					$jsonEntry = array(
						'userId' => $friendEntry->getUserId(),
						'latitude' => $friendEntry->getLatitude(),
						'longitude' => $friendEntry->getLongitude(),
						'date' => $friendEntry->getDate()
					);

					array_push($entries, $jsonEntry);
				}
			}
		}

		return new JsonResponse(array("friendData" => $entries));
	}

	/**
	 * Action where the mobile app requests the json of all possible events
	 *
	 * TODO: Place and time comparation
	 */
	public function getEventListAction()
	{
		$repo = $this->getDoctrine()->getRepository("ManagerBundle:Event");

		$events = $repo->findAll();
		$jsonEvents = array("events" => array());

		foreach ($events as $event) {
			array_push($jsonEvents['events'], array(
				"id" => $event->getId(),
				"name" => $event->getName(),
				"start_date" => $event->getStartDate(),
				"end_date" => $event->getEndDate(),
				"description" => $event->getDescription(),
				"image" => $event->getImage()->getWebPath()
			));

		}

		return new JsonResponse($jsonEvents);
	}

	public function getEventInformationAction($id) {
		// Get needed data
		$event = $this->getDoctrine()->getRepository("ManagerBundle:Event")->find($id);
		$eventBounds = $event->getBounds();
		$mapObjects = $this->getDoctrine()->getRepository("ManagerBundle:MapObject")
											->findByEventId($id);


		// Create the data
		$eventData = array(
			"bounds" => array(),
			"objects" => array(),
			"objectData" => array()
		);

		// Add bounds data
		array_push($eventData['bounds'], array(
			'xmin' => $eventBounds->getLatLow(),
			'xmax' => $eventBounds->getLatHigh(),
			'ymin' => $eventBounds->getLngLow(),
			'ymax' => $eventBounds->getLngHigh()
		));



		// Add mapobjects
		foreach($mapObjects as $mObject) {
			array_push($eventData['objects'], array(
				'id' => $mObject->getObjectId(),
				'lat' => $mObject->getLat(),
				'lng' => $mObject->getLng(),
				'type' => $mObject->getType(),
				'image_url' => $mObject->getUrl(),
				'width' => $mObject->getWidth(),
				'height' => $mObject->getHeight(),
				'angle' => $mObject->getAngle()
			));

		}



		return new JsonResponse($eventData);
	}

	/**
	 * Creates a simple page with the error
	 * @param $msg string Error message
	 * @return Response Error page
	 */
	private function throwException($msg)
	{
		return $this->render('ManagerBundle:Request:request.html.twig', array("error" => $msg));
	}

	private function throwParNotFoundException($par)
	{
		return $this->render('ManagerBundle:Request:request.html.twig', array("parameterNotFound" => $par));
	}

	private function createSuccessResponse()
	{
		return $this->render('ManagerBundle:Request:request.html.twig', array("res" => "true"));
	}
}