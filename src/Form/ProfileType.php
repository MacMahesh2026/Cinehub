<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Lets a signed-in customer update their display name and, optionally, their
 * password. The email address is fixed and cannot be changed here.
 */
final class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Full name',
                // Keep empty input as "" so it hits the NotBlank constraint
                // instead of the non-nullable User::setName(string).
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(message: 'Name is required.'),
                    new Assert\Length(max: 255),
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'WhatsApp number',
                'required' => false,
                // Empty input becomes null (the column is nullable).
                'empty_data' => null,
                'attr' => [
                    'autocomplete' => 'tel',
                    'placeholder' => '+919876543210',
                ],
                'help' => 'Optional. Include the country code, e.g. +919876543210.',
                'constraints' => [
                    new Assert\Regex(
                        pattern: '/^\+[1-9]\d{6,14}$/',
                        message: 'Enter the number in international format, e.g. +919876543210.',
                    ),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                // Not mapped: the controller hashes it into User::$password.
                'mapped' => false,
                'required' => false,
                'first_options' => [
                    'label' => 'New password',
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        // Only enforce a minimum length when a new password is given.
                        new Assert\Callback(static function (?string $value, ExecutionContextInterface $context): void {
                            if (null !== $value && '' !== $value && \strlen($value) < 8) {
                                $context->buildViolation('Password must be at least 8 characters.')->addViolation();
                            }
                        }),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirm new password',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'The password fields must match.',
            ])
            ->add('save', SubmitType::class, ['label' => 'Save changes']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
