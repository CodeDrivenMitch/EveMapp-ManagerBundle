<?php
namespace EveMapp\ManagerBundle\Controller;

use EveMapp\ManagerBundle\Form\Model\Registration;
use EveMapp\ManagerBundle\Form\Type\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;


/**+
 * Class AccountController
 * @package EveMapp\ManagerBundle\Controller
 */
class AccountController extends Controller
{
	/**
	 * Controller which takes a submitted registration form and registers the user.
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function createAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		$form = $this->createForm(new RegistrationType(), new Registration());

		$form->handleRequest($request);

		if ($form->isValid()) {
			$user = $form->getData()->getUser();

			$factory = $this->get('security.encoder_factory');
			$encoder = $factory->getEncoder($user);
			$user->setPassword($encoder->encodePassword($user->plainPassword, null));

			$user->addRole($this->getDoctrine()->getRepository("ManagerBundle:Role")->find(1));
			$user->setIsActive(true);

			$em->persist($user);
			$em->flush();

			$this->get('session')->getFlashBag()->add('notice', 'Registration successful, you can login now!');
			return $this->redirect("/");
		}

		return $this->render(
			'ManagerBundle:Account:register.html.twig',
			array('form' => $form->createView())
		);
	}

	/**
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function loginAction(Request $request)
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
				'form' => $form->createView()));
	}
}