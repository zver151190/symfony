<?php

namespace App\Admin;

use App\Entity\Book;
use App\Form\BookType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\File;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;

final class AuthorAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('name', TextType::class);
        $form->add('books', EntityType::class, [
                'class' => Book::class,
                'choice_label' => 'title',
                'by_reference' => false,
                'multiple' => true,
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid->add('name');
        $datagrid->add('books');
    }


    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id');
        $list->add('name', null, ['editable' => true]);
        $list->add('books', null, ['editable' => true]);
        $list->add('totalBooks', null, ['editable' => false]);
        $list->add(ListMapper::NAME_ACTIONS, null, [
            'actions' => [
                'edit' => [],
            ]
        ]);
        
        
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('id');
        $show->add('name');
        $show->add('books');
        $show->add('totalBooks');
    }
}