<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 5/22/14
 * Time: 6:45 PM
 */

namespace EveMapp\ManagerBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WebUserType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('email', 'email');
		$builder->add('plainPassword', 'repeated', array(
			'first_name'  => 'password',
			'second_name' => 'confirm',
			'type'        => 'password',
		));

		$builder->add('username', 'text');
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'EveMapp\ManagerBundle\Entity\WebUser',
			'csrf_protection' => false
		));
	}

	public function getName()
	{
		return 'user';
	}
}