<?php
namespace App\Form;

use App\Entity\Conge;
use App\Enum\typeconge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CongeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
                'attr' => [
                    'class' => 'form-input'
                ]
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'attr' => [
                    'class' => 'form-input'
                ]
            ])
            ->add('motif', TextareaType::class, [
                'required' => false,
                'label' => 'Motif',
                'attr' => [
                    'class' => 'form-input',
                    'rows' => 4
                ]
            ])
            ->add('typeConge', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn($e) => ucfirst($e->value), typeconge::cases()),
                    typeconge::cases()
                ),
                'choice_value' => fn($enum) => $enum?->value,
                'label' => 'Type de congé',
                'attr' => [
                    'class' => 'form-input'
                ]
            ])
            ->add('justificatif', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Justificatif (PDF, JPG, PNG, etc.)',
                'attr' => [
                    'class' => 'form-input'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPG, PNG ou GIF valide',
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conge::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'conge_form',
        ]);
    }
}