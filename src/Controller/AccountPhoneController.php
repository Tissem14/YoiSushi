<?php

namespace App\Controller;

use App\Form\ChangePhoneType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountPhoneController extends AbstractController
{
    #[Route('modifiermonnumérodetelephone', name: 'account_phone')]
    public function index(): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePhoneType::class, $user);
        return $this->render('account/phone.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
