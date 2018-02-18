<?php
/*
 * (c) 2017: 975l <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    //Builds the form
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $disabled = $options['data']->getAction() == 'delete' ? true : false;
        $requiredImage = $options['data']->getPicture() !== null ? false : true;

        $builder
            ->add('title', TextType::class, array(
                'label' => 'label.title',
                'disabled' => $disabled,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'label.title',
                )))
            ->add('slug', TextType::class, array(
                'label' => 'label.semantic_url',
                'disabled' => $disabled,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'text.semantic_url',
                )))
        ;
        if ($disabled === false) {
            $builder
                ->add('picture', FileType::class, array(
                    'label' => 'label.picture',
                    'required' => $requiredImage,
                    ))
            ;
        }
        $builder
            ->add('startDate', DateType::class, array(
                'label' => 'label.start_date',
                'disabled' => $disabled,
                'required' => true,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false,
                'attr' => array(
                    'class' => 'js-datepicker',
                    'data-date-format' => 'dd-mm-yyyy',
                    'placeholder' => 'label.start_date',
                )))
            ->add('startTime', TimeType::class, array(
                'label' => 'label.start_time',
                'disabled' => $disabled,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'label.start_time',
                )))
            ->add('endDate', DateType::class, array(
                'label' => 'label.end_date',
                'disabled' => $disabled,
                'required' => true,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false,
                'attr' => array(
                    'class' => 'js-datepicker',
                    'data-date-format' => 'dd-mm-yyyy',
                    'placeholder' => 'label.end_date',
                )))
            ->add('endTime', TimeType::class, array(
                'label' => 'label.end_time',
                'disabled' => $disabled,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'label.end_time',
                )))
            ->add('place', TextType::class, array(
                'label' => 'label.place',
                'disabled' => $disabled,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'label.place',
                )))
        ;
        if ($disabled === false) {
            $builder
                ->add('description', TextareaType::class, array(
                    'label' => 'label.description',
                    'disabled' => $disabled,
                    'required' => false,
                    'attr' => array(
                        'class' => 'tinymce',
                        'cols' => 100,
                        'rows' => 15,
                        'placeholder' => 'label.description',
                    )))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'c975L\EventsBundle\Entity\Event',
            'intention'  => 'eventForm',
            'translation_domain' => 'events',
        ));
    }
}
