<?php

namespace App\Controller;

use App\Form\ChangePasswordType; // Importation du formulaire de changement de mot de passe
use Doctrine\ORM\EntityManagerInterface; // Importation de l'interface EntityManager pour les opérations sur la base de données
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Importation de la classe AbstractController de Symfony
use Symfony\Component\Form\FormError; // Importation de la classe FormError pour ajouter des erreurs au formulaire
use Symfony\Component\HttpFoundation\Request; // Importation de la classe Request pour manipuler les requêtes HTTP
use Symfony\Component\HttpFoundation\Response; // Importation de la classe Response pour retourner des réponses HTTP
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Importation de l'interface UserPasswordHasher pour hacher les mots de passe
use Symfony\Component\Routing\Annotation\Route; // Importation de la classe Route pour définir les routes
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface; // Importation de l'interface PasswordAuthenticatedUserInterface

class AccountPasswordController extends AbstractController
{
    #[Route('compte/modifier-mon-mot-de-passe', name: 'account_password')]
    public function index(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManager): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie si un utilisateur est connecté, sinon renvoie une exception
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour changer votre mot de passe.');
        }

        // Vérifie si l'utilisateur implémente l'interface PasswordAuthenticatedUserInterface
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new \LogicException('L\'utilisateur connecté ne supporte pas le changement de mot de passe.');
        }

        // Crée un formulaire de changement de mot de passe et le lie à l'utilisateur
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request); // Traite la requête HTTP pour remplir le formulaire

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère l'ancien mot de passe saisi par l'utilisateur
            $old_pwd = $form->get('old_password')->getData();

            // Vérifie si l'ancien mot de passe est correct
            if ($hasher->isPasswordValid($user, $old_pwd)) {
                // Récupère le nouveau mot de passe saisi par l'utilisateur
                $new_pwd = $form->get('new_password')->getData();
                // Hache le nouveau mot de passe
                $password = $hasher->hashPassword($user, $new_pwd);

                // Met à jour le mot de passe de l'utilisateur
                $user->setPassword($password);

                // Persiste l'utilisateur en base de données
                $entityManager->persist($user);
                $entityManager->flush();

                // Ajoute un message de succès après le changement de mot de passe
                $this->addFlash('success', 'Votre mot de passe a été changé avec succès.');

                // Redirige vers la même page après le changement de mot de passe
                return $this->redirectToRoute('account_password');
            } else {
                // Ajoute une erreur au champ 'old_password' du formulaire si l'ancien mot de passe est incorrect
                $form->get('old_password')->addError(new FormError('Ancien mot de passe incorrect.'));
            }
        }

        // Rend le template 'account/password.html.twig' en passant le formulaire à la vue
        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
