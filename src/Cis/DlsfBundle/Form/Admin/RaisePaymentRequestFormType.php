<?php

namespace Cis\DlsfBundle\Form\Admin;

use App\Entity\Dlsf\Claim;
use App\Form\Dlsf\Type\FundingType;
use App\Form\Dlsf\Type\AgeCategoryType;
use Cis\DlsfBundle\Form\Type as Type;
use Cis\DlsfBundle\CommandBus\Admin\RaisePaymentRequestCommand;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RaisePaymentRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $orm = $options['petroc_helpers']->getOrm();

        $builder
            ->add('ageCategory', AgeCategoryType::class, [
                'label' => 'Applicant Type',
                'placeholder' => 'All'
            ])
            ->add('fundingType', FundingType::class, [
                'label' => 'Funding Type'
            ])
            ->add('site', Type\SiteType::class)
            ->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $formEvent) use ($orm) {
                $command = $formEvent->getData();
                $form = $formEvent->getForm();

                $options = [
                    'academicYear' => $command->getAcademicYear(),
                    'paymentType' => Claim::PAYMENT_TYPE_BACS
                ];

                if (null !== $command->ageCategory) {
                    $options['ageCategory'] = $command->ageCategory;
                }

                if (null !==$command->fundingType) {
                    $options['fundingType'] = $command->fundingType;
                }

                if (null !== $command->site) {
                    $options['site'] = $command->site;
                }

                $form
                    ->add('category', Type\CategoryType::class, [
                        'academic_year' => $options['academicYear'],
                        'finance_only' => 0
                    ])
                ;

                if (null !== $command->category) {
                    $options['category'] = $command->category;
                }

                $claims = $orm->getRepository(Claim::class)->findByPaymentTypeAwaitingProcessing($options);

                $command->setClaims($claims);

                foreach($claims as $claim) {
                    $toBePaid = chr(163) . sprintf('%01.2f',$claim->getApprovedAmount() - $claim->getPaidAmount());

                    $form
                        ->add('claim_' . $claim->getId(), MoneyType::class, [
                            'label' => $claim->getApplicant()->__toString() . ' - ' . $claim->getCategory()->getName() . ' - ' . $toBePaid,
                            'required' => false
                        ])
                    ;
                }
            })
         ;   
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RaisePaymentRequestCommand::class
        ]);
    }
}
