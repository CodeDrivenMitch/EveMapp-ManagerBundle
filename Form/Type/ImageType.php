<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 5/29/14
 * Time: 6:23 PM
 */

namespace EveMapp\ManagerBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImageType extends AbstractType {

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName()
	{
		return 'image';
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('file');
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'EveMapp\ManagerBundle\Entity\Image'
		));
	}
}