<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Document\Credentials;

use Symfony\Component\Validator\Constraints as Assert;

class PostTokenType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $option)
  {
    $builder->add('login', TextType::class, [
        'constraints'=>[
            new Assert\NotBlank(),
            new Assert\Length(['min'=>1, 'max'=>255])
        ]
    ]);
    $builder->add('password', TextType::class, [
          'constraints'=>[
              new Assert\NotBlank(),
              new Assert\Length(['min'=>1, 'max'=>255])
          ]
    ]);
      $builder->add('rememberMe', TextType::class, [
          'constraints'=>[
              new Assert\Choice([
                  'choices' =>[ 'false', 'true',],
                  'strict' => true
              ])
          ]
      ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([
      'data_class'=>Credentials::class,
      'csrf_protection'=>false
    ]);
  }
}
