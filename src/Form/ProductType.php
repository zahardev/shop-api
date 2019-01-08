<?php


namespace App\Form;


use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{

    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {

        $formBuilder
            ->add('name', TextType::class)
            ->add('barcode', TextType::class, [
                'disabled' => $options['is_edit']
            ])
            ->add('cost', MoneyType::class)
            ->add('vatClass', ChoiceType::class, [
                'choices'  => array(6, 21),
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Product::class,
            'is_edit' => false,
            'csrf_protection' => false,
        ));
    }
}