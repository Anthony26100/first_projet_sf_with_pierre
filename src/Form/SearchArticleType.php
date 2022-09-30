<?php

namespace App\Form;

use App\Entity\User;
use App\Data\SearchData;
use App\Entity\Categorie;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SearchArticleType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('query', TextType::class, [
      'label' => false,
      'required' => false,
      'attr' => [
        'placeholder' => 'Rechercher',
      ],
    ])
      ->add('categories', EntityType::class, [
        'label' => false,
        'required' => false,
        'class' => Categorie::class,
        'query_builder' => function (EntityRepository $er) {
          return $er->createQueryBuilder('c')
            ->andWhere('c.enable = true')
            ->orderBy('c.titre', 'ASC');
        },
        'choice_label' => 'titre',
        'expanded' => true, // Va rajouter plusieurs checkbox
        'multiple' => true,
      ])
      ->add('auteur', EntityType::class, [
        'label' => false,
        'required' => false,
        'class' => User::class,
        'query_builder' => function (EntityRepository $er) {
          return $er->createQueryBuilder('u')
            ->innerJoin('u.articles', 'a')
            ->orderBy('u.nom', 'ASC');
        },
        'expanded' => true,
        'multiple' => true,
      ])
      ->add('active', ChoiceType::class, [
        'label' => false,
        'required' => false,
        'choices' => [
          'Oui' => true,
          'Non' => false,
        ],
        'expanded' => true, // Va rajouter plusieurs checkbox
        'multiple' => true,
      ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([
      'data_class' => SearchData::class,
      'method' => 'GET',
      'csrf_protection' => false,
      'translation_domain' => 'forms',
    ]);
  }

  // gerer les préfix de l'url 
  public function getBlockPrefix(): string
  {
    return '';
  }
}
