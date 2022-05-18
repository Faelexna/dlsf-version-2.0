<?php

namespace Cis\DlsfBundle\Form\Type;

use App\Entity\Dlsf\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

class CategoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['academic_year', 'finance_only']);

        $resolver->setDefaults([
            'class' => Category::class,
            'label' => 'Category',
            'placeholder' => 'Please select',
            'choice_label' => 'name',
            'query_builder' => function(Options $options) {
                $em = $options['em'];
                $qb = $em
                    ->createQueryBuilder()
                    ->select('c')
                    ->from(Category::class, 'c')
                    ->where('c.deletedOn is null')
                    ->andWhere('c.academicYear = :academic_year')
                    ->setParameter('academic_year', $options['academic_year'])
                    ->orderBy('c.name', 'ASC')
                ;

                if (isset($options['finance_only'])) {
                    $qb
                        ->andWhere('c.financeOnly = :finance_only')
                        ->setParameter('finance_only', $options['finance_only'])
                    ;
                }

                if (isset($options['internal_only'])) {
                    $qb
                        ->andWhere('c.internalOnly = :internal_only')
                        ->setParameter('internal_only', $options['internal_only'])
                    ;
                }
                return $qb;
            }
        ]);

    }
    
    public function getParent()
    {
        return EntityType::class;
    }
}
