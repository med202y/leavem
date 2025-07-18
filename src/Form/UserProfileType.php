<?php
namespace App\Form;

use App\Entity\User;
use App\Enum\Departement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class UserProfileType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
$builder
->add('prenom', TextType::class)
->add('nom', TextType::class)
->add('email', EmailType::class)
->add('CIN', TextType::class)
->add('CNSS', TextType::class)
->add('departement', EnumType::class, [
'class' => Departement::class,
'choice_label' => fn($choice) => $choice->value,
])
->add('SalaireM', NumberType::class)
->add('Date_dembauche', DateTimeType::class, [
'widget' => 'single_text',
])
->add('Qualificationsprofessionnelles', TextareaType::class, [
'required' => false,
])
->add('solde_conge', NumberType::class)
->add('Date_douverture_droit_au_conge', DateTimeType::class, [
'widget' => 'single_text',
])
->add('Nbr_de_jours_travaille', NumberType::class)
->add('jourdeconge_travail', NumberType::class)
->add('Observations', TextareaType::class, [
'required' => false,
])
->add('plainPassword', PasswordType::class, [
'required' => false,
'mapped' => false,
'label' => 'Nouveau mot de passe (laisser vide si inchangé)',
]);
}

public function configureOptions(OptionsResolver $resolver): void
{
$resolver->setDefaults([
'data_class' => User::class,
]);
}
}
