<?php

namespace Cis\DlsfBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Petroc\CoreBundle\Form\Type\ToggleType;

class EditCategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $formEvent) {
                $form = $formEvent->getForm();
                $command = $formEvent->getData();

                $form
                    ->add('name', TextType::class, [
                        'required' => true,
                        'label' => 'Name'
                    ])
                    ->add('internalOnly', ToggleType::class, [
                        'required' => false,
                        'label' => 'Internal Only?'
                    ])
                    ->add('mealType', ChoiceType::class, [
                        'choices' => [
                            'LUNCH' => 'LUNCH'
                        ],
                        'label' => 'Meal Type',
                        'placeholder' => 'Not Applicable'
                    ])
                ;
            })
        ;
    }

}
