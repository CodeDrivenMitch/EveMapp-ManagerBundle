<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 5/23/14
 * Time: 10:15 PM
 */

namespace EveMapp\ManagerBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventBoundsType extends AbstractType {

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName()
	{
		return 'EventBounds';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'EveMapp\ManagerBundle\Entity\EventBounds',
			'cascade_validation' => true
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('latLow', 'hidden');
		$builder->add('latHigh', 'hidden');
		$builder->add('lngLow', 'hidden');
		$builder->add('lngHigh', 'hidden');
	}
}