<?php

namespace Cis\DlsfBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EditEvidenceTypeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $formEvent) {
                $form = $formEvent->getForm();
                $command = $formEvent->getData();

                $form
                    ->add('description', TextType::class, [
                        'label' => 'Description',
                        'required' => true
                    ])
                    ->add('category', ChoiceType::class, [
                        'choices' => [
                            'O19' => 'O19',
                            'U19' => 'U19'
                        ],
                        'placeholder' => 'All',
                        'label' => 'Category'
                    ])
                ;
            })
        ;
    }
}
