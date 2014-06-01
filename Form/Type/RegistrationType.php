<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 5/22/14
 * Time: 6:40 PM
 */

namespace EveMapp\ManagerBundle\Form\Type;


use EveMapp\ManagerBundle\Form\Type\WebUserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('user', new WebUserType());
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'EveMapp\ManagerBundle\Form\Model\Registration',
			'cascade_validation' => true,
			'csrf_protection' => true,
            'csrf_field_name' => '_token'

		));
	}

	public function getName()
	{
		return 'registration';
	}
}