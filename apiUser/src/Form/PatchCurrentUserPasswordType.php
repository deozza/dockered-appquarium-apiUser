<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

use App\Document\User;

class PatchCurrentUserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $option)
    {
		$builder->add('newPassword', TextType::class, [
			"constraints"=>[
				new Assert\NotBlank(),
				new Assert\Length(['min'=>1, 'max'=>255])
			]
		])
		;
		$builder->add('repeatPassword', TextType::class, [
			"constraints"=>[
				new Assert\NotBlank(),
				new Assert\Length(['min'=>1, 'max'=>255])
			]
		])
		;
		$builder->add('plainPassword', TextType::class, [
                "constraints"=>[
                    new Assert\NotBlank(),
                    new Assert\Length(['min'=>1, 'max'=>255])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>User::class,
            'csrf_protection'=>false
        ]);
    }
}