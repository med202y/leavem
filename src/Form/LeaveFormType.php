<?php
namespace App\Form;

use App\Entity\Leave;
use App\Enum\LeaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class LeaveFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('leaveType', ChoiceType::class, [
                'label' => 'Type de congé',
                'choices' => LeaveType::cases(),
                'choice_label' => function(LeaveType $choice) {
                    return $choice->value;
                },
                'choice_value' => function(?LeaveType $choice) {
                    return $choice?->value;
                },
                'attr' => [
                    'class' => 'form-input'
                ]
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
                'attr' => [
                    'class' => 'form-input'
                ]
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'attr' => [
                    'class' => 'form-input'
                ]
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Motif',
                'required' => false,
                'attr' => [
                    'class' => 'form-input',
                    'rows' => 4,
                    'placeholder' => 'Décrivez brièvement le motif de votre demande...'
                ]
            ])
            ->add('proof', FileType::class, [
                'label' => 'Justificatif (PDF, JPG, PNG, etc.)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-input',
                    'accept' => '.pdf,.jpg,.jpeg,.png,.gif'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'image/jpeg',
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
            'data_class' => Leave::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'leave_form',
        ]);
    }
}