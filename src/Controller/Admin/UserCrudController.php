<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;


class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setSearchFields(['id', 'username', 'roles', 'firstname', 'lastname', 'email']);
    }


    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $username = TextField::new('username', 'Pseudo');
        $firstname = TextField::new('firstname', 'Prénom');
        $lastname = TextField::new('lastname', 'Nom');
        $email = EmailField::new('email');
        $roles = ChoiceField::new('roles')->autocomplete()->allowMultipleChoices()->setChoices(["Utilisateur" => "ROLE_USER", "Administrateur" => "ROLE_ADMIN"]);

        //Could not load type...: class does not exist
//        $password = TextField::new('password', 'Mot de passe')->setFormType(password_hash('MotDePasse123', PASSWORD_ARGON2I));

        if (Crud::PAGE_INDEX === $pageName){
            return [$id, $username, $firstname, $lastname, $email, $roles];
        }

        return [
            $username, $firstname, $lastname, $email, $roles,
//            $password
        ];

//        return [
//            IdField::new('id'),
////            TextField::new('title'),
////            TextEditorField::new('description'),
//        ];
    }

}
