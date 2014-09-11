<?php


namespace Wallabag\Bundle\CoreBundle\Admin;


use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class UserAdmin extends Admin {
    protected function configureFormFields(FormMapper $form)
    {
        $form->add("username")
            ->add("email");
    }

    protected function configureListFields(ListMapper $list)
    {
        $list->addIdentifier("id")
            ->add("username")
            ->add("email");
    }

} 