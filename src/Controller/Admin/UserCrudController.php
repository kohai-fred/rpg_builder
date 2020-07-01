<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setSearchFields(['id', 'username', 'roles', 'firstname', 'lastname', 'email']);
    }


    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $username = TextField::new('username');
        $firstname = TextField::new('firstname');
        $lastname = TextField::new('lastname');
        $email = EmailField::new('email');
        $roles = ChoiceField::new('roles')->autocomplete()->allowMultipleChoices()->setChoices(["User" => "ROLE_USER", "Admin" => "ROLE_ADMIN"]);

        //I don't know for algo in password_hash...
//        $password = TextField::new('password')->setFormType(password_hash('MotDePasse123', 'PASSWORD_ARGON2I'));

        if (Crud::PAGE_INDEX === $pageName){
            return [$id, $username, $firstname, $lastname, $email, $roles];
        }

        return [
            $username, $firstname, $lastname, $email, $roles,
        ];

//        return [
//            IdField::new('id'),
////            TextField::new('title'),
////            TextEditorField::new('description'),
//        ];
    }

}
