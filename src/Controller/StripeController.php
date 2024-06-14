<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session/{reference}', name: 'stripe_create_session')]
    public function index(Cart $cart, EntityManagerInterface $entityManager, $reference)
    {
        $line_items = [];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000';

        // Récupérer l'ordre par référence
        $order = $entityManager->getRepository(Order::class)->findOneBy(['reference' => $reference]);

        if (!$order) {
            return new JsonResponse(['error' => 'Order not found']);
        }

        // Construire les items de ligne pour Stripe
        foreach ($cart->getFull() as $product) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product['product']->getPrice() * 100, // en centimes
                    'product_data' => [
                        'name' => $product['product']->getName(),
                        'images' => [$YOUR_DOMAIN . "/uploads/images/" . $product['product']->getIllustration()]
                    ],
                ],
                'quantity' => $product['quantity'],
            ];
        }

        // Configuration de Stripe avec la clé API
        Stripe::setApiKey('sk_test_51M9VdzKUFOQ5BxyPrON0C9sP1XRURoTUivBaPKaJrdT1FaV2HWFC8ab9FhTNzAFcODeK1H4cvjQaFqhdM9jOcmyP00pa2yPZHY');

        // Création de la session Stripe
        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $line_items,
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
        ]);

        // Enregistrer l'ID de session Stripe dans l'ordre
        $order->setStripeSessionId($checkout_session->id);
        $entityManager->flush(); // Enregistrer dans la base de données

        // Retourner l'ID de la session Stripe en réponse
        return new JsonResponse(['id' => $checkout_session->id]);
    }
}
