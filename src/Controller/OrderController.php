<?php

namespace App\Controller;

use App\Form\OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/commande', name: 'order')]
    public function index(): Response
    {
        // 1. Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // 2. Vérification si l'utilisateur est connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // 3. Débogage : Vérification s'il y a des adresses associées à l'utilisateur
        if ($user->getAddresses()->isEmpty()) {
            throw $this->createNotFoundException('Aucune adresse trouvée pour cet utilisateur.');
        }

        // 4. Création du formulaire en passant l'utilisateur au formulaire OrderType
        $form = $this->createForm(OrderType::class, null, [
            'user' => $user
        ]);

        // 5. Rendu de la vue en passant le formulaire à twig
        return $this->render('order/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
