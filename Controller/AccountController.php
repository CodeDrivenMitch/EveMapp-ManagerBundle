<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 5/22/14
 * Time: 6:40 PM
 */

namespace EveMapp\ManagerBundle\Controller;


use EveMapp\ManagerBundle\Form\Model\Registration;
use EveMapp\ManagerBundle\Form\Type\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends Controller
{
	public function registerAction()
	{
		$registration = new Registration();
		$form = $this->createForm(new RegistrationType(), $registration, array(
			'action' => $this->generateUrl('account_create'),
		));

		return $this->render(
			'ManagerBundle:Account:register.html.twig',
			array('form' => $form->createView())
		);
	}

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

			return $this->redirect("/");
		}

		return $this->render(
			'ManagerBundle:Account:register.html.twig',
			array('form' => $form->createView())
		);
	}
}