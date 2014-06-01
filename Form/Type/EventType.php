<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 5/23/14
 * Time: 6:55 PM
 */

namespace EveMapp\ManagerBundle\Form\Type;


use EveMapp\ManagerBundle\Entity\EventBounds;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventType extends AbstractType
{

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName()
	{
		return 'event';
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', 'text');
		$builder->add('startDate', 'datetime', array(
			'widget' => 'single_text',
			'format' => 'yyyy/MM/dd HH:mm',
			'attr' => array('class' => 'date')
		));
		$builder->add('endDate', 'datetime', array(
			'widget' => 'single_text',
			'format' => 'yyyy/MM/dd HH:mm',
			'attr' => array('class' => 'date')
		));
		$builder->add('description', 'text');
		$builder->add('bounds', new EventBoundsType());
		$builder->add('image', new ImageType());
		$builder->add('submit', 'submit');
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'EveMapp\ManagerBundle\Entity\Event'
		));
	}
}