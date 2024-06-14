<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande', name: 'order')]
    public function index(Cart $cart, Request $request, UrlGeneratorInterface $urlGenerator): Response
    {
        // 1. Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // 2. Vérification si l'utilisateur est connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // 3. Débogage : Vérification s'il y a des adresses associées à l'utilisateur
        $addresses = $user->getAddresses();
        if ($addresses->isEmpty()) {
            // Redirection vers la création d'adresse
            return new RedirectResponse($urlGenerator->generate('account_address_add'));
        }

        // 4. Création du formulaire en passant l'utilisateur au formulaire OrderType
        $form = $this->createForm(OrderType::class, null, [
            'user' => $user
        ]);

        // 5. Rendu de la vue en passant le formulaire à Twig
        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getFull()
        ]);
    }

    #[Route('/commande/recapitulatif', name: 'order_recap',)]
    public function recap(Cart $cart, Request $request, UrlGeneratorInterface $urlGenerator): Response
    {
        // 1. Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // 2. Vérification si l'utilisateur est connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // 4. Création du formulaire en passant l'utilisateur au formulaire OrderType
        $form = $this->createForm(OrderType::class, null, [
            'user' => $user
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //dd($form->getData());

            $date = new DateTimeImmutable();
            $delivery = $form->get('addresses')->getData();
            $delivery_content = $delivery->getFirstname() . ' ' . $delivery->getLastname();
            if ($delivery->getCompany()) {
                $delivery_content .= '<br>' . $delivery->getCompany();
            }
            $delivery_content .= '<br>' . $delivery->getAddress();
            $delivery_content .= '<br>' . $delivery->getCity();
            //dd($delivery_content);

            // Enregistrer ma commande Order()
            $order = new Order();
            $order->setUser($this->getUser());
            $order->setCreatedAt($date);
            $order->setDelivery($delivery_content);
            $order->setIsPaid(0);

            $this->entityManager->persist($order);


            // Enregistrer mes produits OrderDetails()
            foreach ($cart->getFull() as $product) {
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order);
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);
                $this->entityManager->persist($orderDetails); // Persister chaque OrderDetails
            }

            $this->entityManager->flush(); // Flusher une seule fois après la boucle

            return $this->render('order/add.html.twig', [
                'cart' => $cart->getFull(),
                'delivery' => $delivery_content
            ]);
        }
        return $this->redirectToRoute('cart');
    }
}
