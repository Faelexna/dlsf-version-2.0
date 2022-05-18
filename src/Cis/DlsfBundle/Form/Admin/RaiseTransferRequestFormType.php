<?php

namespace Cis\DlsfBundle\Form\Admin;

use App\Entity\Dlsf\Claim;
use App\Form\Dlsf\Type\FundingType;
use App\Form\Dlsf\Type\AgeCategoryType;
use Cis\DlsfBundle\Form\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RaiseTransferRequestFormType extends AbstractType
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
            ->add('category', Type\CategoryType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $formEvent) use ($orm) {
                $command = $formEvent->getData();
                $form = $formEvent->getForm();

                $options = [
                    'academicYear' => $command->getAcademicYear(),
                    'paymentType' => Claim::PAYMENT_TYPE_BUDGET_TRANSFER
                ];

                if (null !== $command->getAgeCategory()) {
                    $options['ageCategory'] = $command->getAgeCategory();
                }

                if (null !==$command->getFundingType()) {
                    $options['fundingType'] = $command->getFundingType();
                }

                if (null !== $command->getSite()) {
                    $options['site'] = $command->getSite();
                }

                if (null !== $command->getCategory()) {
                    $options['category'] = $command->getCategory();
                }

                $claims = $orm->getRepository(Claim::class)->findByPaymentTypeAwaitingProcessing($options);

                $command->setClaims($claims);

                foreach($claims as $claim) {
                    $toBePaid = chr(163) . sprintf('%01.2f',$claim->getApprovedAmount() - $claim->getPaidAmount());

                    $form
                        ->add('claim_amount_' . $claim->getId(), MoneyType::class, [
                            'label' => $claim->getApplicant()->__toString() . ' - ' . $claim->getCategory()->getName() . ' - ' . $toBePaid,
                            'required' => false
                        ])
                        ->add('claim_code_' . $claim->getId(), TextType::class, [
                            'label' =>  'Cost Centre',
                            'data' => $claim->getChequeRequest,
                            'required' => true
                        ])
                    ;
                }
            })
         ;   
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RaiseTransferRequestCommand::class
        ]);
    }
}
