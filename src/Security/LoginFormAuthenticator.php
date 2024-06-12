<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse; // Importation de la classe RedirectResponse pour rediriger l'utilisateur
use Symfony\Component\HttpFoundation\Request; // Importation de la classe Request pour manipuler les requêtes HTTP
use Symfony\Component\HttpFoundation\Response; // Importation de la classe Response pour retourner des réponses HTTP
use Symfony\Component\Routing\Generator\UrlGeneratorInterface; // Importation de l'interface UrlGeneratorInterface pour générer des URLs
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface; // Importation de l'interface TokenInterface pour les jetons d'authentification
use Symfony\Component\Security\Core\Security; // Importation de la classe Security pour les services de sécurité
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator; // Importation de la classe AbstractLoginFormAuthenticator pour les authentificateurs de formulaire de connexion
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge; // Importation de la classe CsrfTokenBadge pour le badge de protection CSRF
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge; // Importation de la classe RememberMeBadge pour le badge de "Se souvenir de moi"
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge; // Importation de la classe UserBadge pour le badge d'utilisateur
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials; // Importation de la classe PasswordCredentials pour les informations d'identification de mot de passe
use Symfony\Component\Security\Http\Authenticator\Passport\Passport; // Importation de la classe Passport pour le passeport d'authentification
use Symfony\Component\Security\Http\Util\TargetPathTrait; // Importation du trait TargetPathTrait pour manipuler les chemins cibles

// Définition de la classe LoginFormAuthenticator qui hérite d'AbstractLoginFormAuthenticator
class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait; // Utilisation du trait TargetPathTrait

    // Définition d'une constante pour la route de connexion
    public const LOGIN_ROUTE = 'app_login';

    // Constructeur pour injecter le service UrlGeneratorInterface
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    // Méthode pour authentifier l'utilisateur
    public function authenticate(Request $request): Passport
    {
        // Récupère l'email envoyé dans la requête
        $email = $request->request->get('email', '');

        // Stocke l'email dans la session pour réutilisation
        $request->getSession()->set(Security::LAST_USERNAME, $email);

        // Retourne un nouveau passeport d'authentification avec les informations de l'utilisateur et les badges
        return new Passport(
            new UserBadge($email), // Badge utilisateur
            new PasswordCredentials($request->request->get('password', '')), // Informations d'identification de mot de passe
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')), // Badge de protection CSRF
                new RememberMeBadge(), // Badge de "Se souvenir de moi"
            ]
        );
    }

    // Méthode appelée en cas de succès de l'authentification
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Si une URL cible a été enregistrée, redirige l'utilisateur vers cette URL
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Sinon, redirige l'utilisateur vers la page de compte
        return new RedirectResponse($this->urlGenerator->generate('account')); // Redirection vers la page 'account' après connexion réussie
    }

    // Méthode pour obtenir l'URL de connexion
    protected function getLoginUrl(Request $request): string
    {
        // Génère et retourne l'URL de la route de connexion
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
