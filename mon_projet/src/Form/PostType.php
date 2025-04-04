<?php
// src/Form/PostType.php
namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;  
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $imageChoices = [];

        // Récupérer les fichiers dans public/uploads/images
        $imageDir = __DIR__.'/../../public/uploads/images';
        $files = scandir($imageDir);
        foreach ($files as $file) {
            if (is_file($imageDir . '/' . $file)) {
                $imageChoices[$file] = $file;
            }
        }

        $builder
            ->add('titre', TextType::class)
            ->add('description', TextareaType::class)
            ->add('image', ChoiceType::class, [
                'choices' => $imageChoices,
                'placeholder' => 'Choisir une image',
            ]) 
            ->add('new_image', FileType::class, [
                'label' => 'Télécharger une nouvelle image',
                'required' => false,
                'mapped' => false,    // Ce champ n'est pas mappé à une propriété de l'entité
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}

