<?php

namespace Cis\DlsfBundle\Form\Type;

use App\Entity\Dlsf\EvidenceType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvidenceSeenType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => EvidenceType::class,
            'label' => 'EvidenceSeen',
            'placeholder' => 'Please select',
            'choice_label' => 'description',
            'query_builder' =>function (EntityRepository $er) {
                return $er->createQueryBuilder('e')
                    ->where('e.deletedOn is null')
					->andWhere('e.type = :type')
                    ->andWhere('e.academicYear = :year')
                    ->setParameter('year', '2021')
                    ->setParameter('type', EvidenceType::EVIDENCE_TYPE_CODE)
                    ->orderBy('e.description', 'ASC')
                ;
            },
        ));
    }
    
    public function getParent()
    {
        return EntityType::class;
    }
}