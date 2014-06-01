<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 5/31/14
 * Time: 8:57 AM
 */

namespace EveMapp\ManagerBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class MapObjectImageType extends AbstractType {

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName()
	{
		return 'mapObjectImage';
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('file');
		$builder->add('type', 'hidden');
		$builder->add('upload', 'submit', array(
			'attr' => array('class' => 'image_upload_button'),
		));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'EveMapp\ManagerBundle\Entity\MapObjectImage'
		));
	}
}