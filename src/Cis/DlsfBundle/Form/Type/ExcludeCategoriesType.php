<?php

namespace Cis\DlsfBundle\Form\Type;

use App\Entity\Dlsf\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

class ExcludeCategoriesType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['academic_year']);

        $resolver->setDefaults(array(
            'class' => Category::class,
            'label' => 'Exclude Applicants with Categories',
            'multiple' => true,
            'expanded' => true,
            'placeholder' => 'Please select',
            'choice_label' => 'name',
            'query_builder' =>function(Options $options) {
                return $options['em']
                    ->createQueryBuilder()
                    ->select('c')
                    ->from(Category::class, 'c')
                    ->where('c.deletedOn is null')
                    ->andWhere('c.academicYear = :academic_year')
                    ->andWhere('c.financeOnly = 0')
                    ->setParameter('academic_year', $options['academic_year'])
                    ->orderBy('c.name', 'ASC')
                ;
            },
        ));
    }
    
    public function getParent()
    {
        return EntityType::class;
    }
}