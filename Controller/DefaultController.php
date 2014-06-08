<?php

namespace EveMapp\ManagerBundle\Controller;

use EveMapp\ManagerBundle\Form\Model\Registration;
use EveMapp\ManagerBundle\Form\Type\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

class DefaultController extends Controller
{
	public function indexAction(Request $request)
	{
		$session = $request->getSession();

		if ($this->get('security.context')->isGranted('ROLE_USER')) {
			$this->redirect($this->generateUrl('event_list'))->send();
		}

		if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(
				SecurityContextInterface::AUTHENTICATION_ERROR
			);
		} else {
			$error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
			$session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
		}
		$form = $this->createForm(new RegistrationType(), new Registration(), array(
			'action' => $this->generateUrl('account_create')
		));

		return $this->render('ManagerBundle:Default:login.html.twig',
			array('last_username' => $session->get(SecurityContextInterface::LAST_USERNAME),
				'error' => $error,
				'user' => $this->getUser(),
				'form' => $form->createView()));
	}

}
