<?php

namespace App\Controller;

use App\Entity\User; // Importation de l'entité User
use App\Form\RegisterType; // Importation du formulaire d'inscription
use Symfony\Component\Form\FormInterface; // Importation de l'interface FormInterface de Symfony
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Importation de la classe AbstractController de Symfony
use Symfony\Component\HttpFoundation\Request; // Importation de la classe Request pour manipuler les requêtes HTTP
use Symfony\Component\HttpFoundation\Response; // Importation de la classe Response pour retourner des réponses HTTP
use Symfony\Component\Routing\Annotation\Route; // Importation de la classe Route pour définir les routes
use Doctrine\ORM\EntityManagerInterface; // Importation de l'interface EntityManager pour les opérations sur la base de données
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Importation de l'interface UserPasswordHasher pour hacher les mots de passe

class RegisterController extends AbstractController
{
    // Définition de la route pour la page d'inscription
    #[Route('/inscription', name: 'register')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher): Response
    {
        // Création d'un nouvel utilisateur
        $user = new User();

        // Création du formulaire d'inscription et liaison avec l'utilisateur
        $form = $this->createForm(RegisterType::class, $user);

        // Traitement de la requête HTTP pour remplir le formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère les données du formulaire
            $user = $form->getData();

            // Hachage du mot de passe avant de le stocker
            $password = $hasher->hashPassword($user, $user->getPassword());
            // dd($password); // Décommenter cette ligne pour déboguer et afficher le mot de passe haché

            // Met à jour le mot de passe de l'utilisateur avec le mot de passe haché
            $user->setPassword($password);

            // Persiste l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirige l'utilisateur vers la page 'account' après une inscription réussie
            return $this->redirectToRoute('account');
        }

        // Rend le template 'register/index.html.twig' en passant le formulaire à la vue
        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
