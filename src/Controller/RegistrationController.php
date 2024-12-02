<?php 

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class RegistrationController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $plainPassword = $request->request->get('plainPassword');
            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');

            if (!$email || !$plainPassword || !$firstName || !$lastName) {
                $this->addFlash('error', 'All fields are required.');
                return $this->redirectToRoute('app_register');
            }

            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Registration successful. You can now log in.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig');
    }
}
