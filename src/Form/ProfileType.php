<?php

namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;


class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['data'];     
        $constraintsImage =[
                    new Image([
                        'mimeTypes' => ['image/jpeg','image/png','image/gif','image/svg+xml','image/webp'],
                        'mimeTypesMessage' => 'Veuillez sélectionner une image'
                    ])
                ];
        
                if(!$entity->getId()){
            array_push(
                    $constraintsImage, 
                    new NotBlank([
                'message' => 'Veuillez sélectionner une image'
            ])
        );
    }
        
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('pseudo')
            ->add('age')
            ->add('description')
            ->add('image', FileType::class, [
                'constraints' => $constraintsImage,
                'help' => 'Veuillez sélectionner une image au format JPG',
                'data_class' => null
                
            ])
               
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
