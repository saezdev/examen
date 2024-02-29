<?php

namespace App\Form;

use App\Entity\Mesa;
use App\Entity\Sindicato;
use App\Entity\Voto;
use App\Validator\PositiveInteger;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VotosSindicalesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $votos = $options['votos'];
        $sindicatos = $options['sindicatos'];
        $mesa_id = $options['mesa_id'];


        // Recorremos todos los sindicatos
        foreach ($sindicatos as $index => $value) {

            // Recorremos todos los votos
            foreach ($votos as $votos_value) {

                // Si el sindicato del voto es igual al sindicato...
                if ($votos_value->getSindicato() == $value) {
                    $fieldName = 'sindicato_' . $index; // Utiliza el índice del bucle para generar un nombre único
                    $builder->add($fieldName, TextType::class, [
                        'label' => $value->getSindicato(),
                        'data' => $votos_value->getVotos(),
                        'constraints' => [new PositiveInteger()]
                    ]);
                }
            }
        }

        $builder->add('submit', SubmitType::class);





    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 'data_class' => Voto::class,
        ]);
        $resolver->setRequired('votos');
        $resolver->setRequired('sindicatos');
        $resolver->setRequired('mesa_id');
    }
}
