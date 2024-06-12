<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePhoneType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountPhoneController extends AbstractController
{
    #[Route('/compte/changer-telephone', name: 'account_phone')]
    public function changePhone(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie si un utilisateur est connecté, sinon renvoie une exception
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour changer votre numéro de téléphone.');
        }

        // Crée un formulaire de changement de numéro de téléphone et le lie à l'utilisateur
        $form = $this->createForm(ChangePhoneType::class, $user);
        $form->handleRequest($request); // Traite la requête HTTP pour remplir le formulaire

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère le nouveau numéro de téléphone saisi par l'utilisateur
            $newPhone = $form->get('new_phone')->getData();

            // Met à jour le numéro de téléphone de l'utilisateur
            $user->setPhone($newPhone);

            // Persiste l'utilisateur en base de données avec le nouveau numéro de téléphone
            $entityManager->persist($user);
            $entityManager->flush();

            // Ajoute un message de succès après le changement de numéro de téléphone
            $this->addFlash('success', 'Votre numéro de téléphone a été changé avec succès.');

            // Redirige vers la même page après le changement de numéro de téléphone
            return $this->redirectToRoute('account_phone');
        }

        // Rend le template 'account/phone.html.twig' en passant le formulaire à la vue
        return $this->render('account/phone.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
