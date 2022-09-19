<?php

namespace App\Admin;

use App\Entity\Author;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\File;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


final class BookAdmin extends AbstractAdmin
{
    
    private $params;
    private $slugger;

    public function __construct(ParameterBagInterface $params, SluggerInterface $slugger)
    {
        $this->params = $params;
        $this->slugger = $slugger;
    }
    
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('title', TextType::class);
        $form->add('description', TextareaType::class);
            
        $form->add('cover', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/gif',
                            'image/jpeg',
                            'image/png',
                            'image/svg+xml'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid imgae',
                    ])
                ],
        ]);
        $form->add('authors', EntityType::class, [
                'class' => Author::class,
                'choice_label' => 'name',
                'multiple' => true,
            ]);
        $form->add('publishYear', NumberType::class);
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid->add('id');
        $datagrid->add('title');
        $datagrid->add('description');
        $datagrid->add('authors');
        $datagrid->add('publish_year', DateRangeFilter::class);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id');
        $list->add('cover',null,['template' => 'Admin/list_image.html.twig']);
        $list->add('title', null, ['editable' => true]);
        $list->add('authors', FieldDescriptionInterface::TYPE_MANY_TO_MANY, ['editable' => true, 'multiple' => true, 'associated_property' => 'name']);
        $list->add('publish_year', null, ['editable' => true]);
        $list->add(ListMapper::NAME_ACTIONS, null, [
            'actions' => [
                'edit' => [],
            ]
        ]);
        
        
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('id');
        $show->add('title');
        $show->add('cover',null,['template' => 'Admin/show_image.html.twig']);
        $show->add('authors');
        $show->add('publishYear');
    }
    
    
    
    public function preUpdate($book): void
    {
        $newField = $this->getForm()->get('cover')->getData();
        if($newField !== null){
            $oldFile = str_replace('/public/uploads/','',$book->getCover());
            $safeFilename = $this->slugger->slug($book->getTitle());
            $newFilename = $safeFilename.'-'.uniqid().'.'.$newField->guessExtension();
            try{
                $newField->move(
                    $this->params->get('book_cover_directory'),
                    $newFilename
                );
                //Delete the old file
                if(trim($oldFile) != "" && file_exists($this->params->get('book_cover_directory').$oldFile)){
                    unlink($this->params->get('book_cover_directory').$oldFile);
                }
                //Save new as cover
                $book->setCover('/public/uploads/' . $newFilename);
            }catch(FileException $e){
                  
            }
        }
    }
}