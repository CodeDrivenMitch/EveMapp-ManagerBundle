<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 5/23/14
 * Time: 4:48 PM
 */

namespace EveMapp\ManagerBundle\Controller;


use EveMapp\ManagerBundle\Entity\Event;
use EveMapp\ManagerBundle\Entity\EventBounds;
use EveMapp\ManagerBundle\Form\Type\EventBoundsType;
use EveMapp\ManagerBundle\Form\Type\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EventController extends Controller
{


	public function listAction() {
		$eventRepository = $this->getDoctrine()->getRepository("ManagerBundle:Event");
		$userId = $this->getUser()->getId();

		$events = $eventRepository->findBy(array('owner' => $userId));
		return $this->render('ManagerBundle:Events:list.html.twig', array('events' => $events));
	}

	public function showAction($id) {
		$eventRepository = $this->getDoctrine()->getRepository("ManagerBundle:Event");
		$event = $eventRepository->find($id);
		return $this->render('ManagerBundle:Events:show.html.twig', array('event' => $event));
	}

	public function editAction(Request $request, $id) {
		$em = $this->getDoctrine()->getManager();

		$repository = $this->getDoctrine()->getRepository("ManagerBundle:Event");
		$event = $repository->find($id);

		$bounds = $this->getDoctrine()->getRepository("ManagerBundle:EventBounds")->findOneByEventId($id);
		$event->setEventBounds($bounds);


		$event->setImage($event->getImage());

		$form = $this->createForm(new EventType(), $event);
		$form->handleRequest($request);

		if($form->isValid()) {
			$event = $form->getData();
			$event->getImage()->upload();
			$event->getEventBounds()->setZoom(19);

			$em->persist($event);
			$em->persist($event->getImage());
			$em->persist($event->getEventBounds()->setEventId($event->getId()));
			$em->flush();

			return $this->redirect($this->generateUrl('show_event', array('id' => $id)))->send();

		}

		return $this->render(
			'ManagerBundle:Events:form.html.twig',
			array('form' => $form->createView(), 'header' => "Edititing " . $event->getName())
		);

	}

	public function deleteAction($id) {
		$repository = $this->getDoctrine()->getRepository("ManagerBundle:Event");

		$event = $repository->find($id);

		if($event->getOwner() == $this->getUser()->getId()) {
			$em = $this->getDoctrine()->getManager();
			$em->remove($event);
			$em->flush();
			$this->addFlash('notice', 'Event successfully deleted!');
		} else {
			$this->addFlash('warning', 'Only the owner can delete an event!');

		}

		$this->redirect($this->generateUrl('event_list'))->send();


	}

	public function createAction(Request $request) {
			$em = $this->getDoctrine()->getManager();

		$form = $this->createForm(new EventType(), new Event());

		$form->handleRequest($request);

		if ($form->isValid()) {
			$event = $form->getData();
			$event->setOwner($this->getUser());
			$event->getImage()->upload();
			$em->persist($event);
			$em->persist($event->getImage());

			$em->flush();

			$this->addFlash('notice', 'Event ' . $event->getName() . " successfully created!");

			return $this->redirect("/")->send();
		}
		return $this->render(
			'ManagerBundle:Events:form.html.twig',
			array('form' => $form->createView(), 'header' => "Creating new event")
		);
	}

	public function editMapAction(Request $request, $id) {
		$repository = $this->getDoctrine()->getRepository("ManagerBundle:Event");
		$event = $repository->find($id);
		if(!$this->getUser()) {
			$this->addFlash("notice", "You must be logged in!");
			$this->redirect($this->generateUrl('event_list'))->send();
		}
		if($this->getUser()->getId() != $event->getOwner()->getId()) {
			$this->addFlash('notice', 'You can only edit the map of your own event!');
			$this->redirect($this->generateUrl('event_list'))->send();
		}
		if(!$event) {
			$this->addFlash('notice', 'This event does not exist!');
			$this->redirect($this->generateUrl('event_list'))->send();
		}


		$request->getSession()->set("edit_map_event", $id);

		return $this->render('ManagerBundle:Editor:edit-map-event.html.twig', array('event' => $event));
	}

	private function addFlash($type, $msg) {
		$this->get('session')->getFlashBag()->add($type, $msg);
	}
} 