<?php


namespace App\Form;


use App\Entity\Product;
use App\Entity\VatClass;
use App\Repository\VatClassRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    /**
     * @var VatClassRepository
     */
    private $vatClassRepository;

    public function __construct( VatClassRepository $vatClassRepository )
    {
        $this->vatClassRepository = $vatClassRepository;
    }


    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {

        $formBuilder
            ->add('name', TextType::class)
            ->add('barcode', TextType::class, [
                'disabled' => $options['is_edit']
            ])
            ->add('cost', MoneyType::class)
            ->add('vatClass', EntityType::class, [
                'class' => VatClass::class,
                'choices' => $this->vatClassRepository->findAll(),
                'choice_value' => function (VatClass $entity = null) {
                    return $entity ? $entity->getPercent() : '';
                }
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