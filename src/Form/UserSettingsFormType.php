<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Team;
use App\Enum\Role;
use App\Enum\Departement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserSettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est obligatoire']),
                    new Assert\Length(['max' => 255])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire']),
                    new Assert\Length(['max' => 255])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'email est obligatoire']),
                    new Assert\Email(['message' => 'Format d\'email invalide'])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('department', ChoiceType::class, [
                'label' => 'Département',
                'choices' => $this->getDepartmentChoices(),
                'placeholder' => '— Sélectionner —',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('rolesArray', ChoiceType::class, array_merge([
                'label'    => 'Rôle(s)',
                'choices'  => $this->getRoleChoices(),
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ], $options['is_edit']
                  // no "data" on edit
                  ? []
                  // but default EMPLOYEE on create
                  : ['data' => [Role::EMPLOYEE]]))
            ->add('team', EntityType::class, [
                'label' => 'Équipe',
                'class' => Team::class,
                'choice_label' => 'name',
                'placeholder' => '— Aucune —',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('leaveBalance', NumberType::class, [
                'label'       => 'Solde de congé',
                'scale'       => 1,
                'data'        => $options['is_edit'] 
                    ? null // Let the entity data be used
                    : 25.0, // Default for new users
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le solde doit être positif ou nul'])
                ],
                'attr' => ['class' => 'form-control', 'step' => '0.1']
            ])
            ->add('hireDate', DateType::class, [
                'label' => 'Date d\'embauche',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ]);

        // Add password field only for new users or when explicitly updating password
        if (!$options['is_edit']) {
            $builder->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le mot de passe est obligatoire']),
                    new Assert\Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins 6 caractères'])
                ],
                'attr' => ['class' => 'form-control']
            ]);
        } else {
            $builder->add('plainPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe (laisser vide pour ne pas changer)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins 6 caractères'])
                ],
                'attr' => ['class' => 'form-control']
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false, // Flag to differentiate between create and edit forms
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'user_form', // Default, will be overridden in controller
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
    }

    private function getDepartmentChoices(): array
    {
        $choices = [];
        foreach (Departement::cases() as $department) {
            $choices[$department->name] = $department;
        }
        return $choices;
    }

    private function getRoleChoices(): array
    {
        $choices = [];
        foreach (Role::cases() as $role) {
            $choices[$role->name] = $role;
        }
        return $choices;
    }
}