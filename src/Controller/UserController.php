<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use FOS\UserBundle\Model\UserManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user", name="user_")
 * @IsGranted("ROLE_MANAGE_USERS")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(
        UserRepository $userRepo
    ): Response {
        $users = $userRepo->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/new", name="user_new")
     */
    public function new(
        Request $request,
        UserManagerInterface $userManager
    ): Response {
        $user = $userManager->createUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPlainPassword($user->getPassword());
            $userManager->updateUser($user);

            $this->addFlash('success', 'updated_successfully');

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{user}", name="user_edit")
     */
    public function edit(
        Request $request,
        UserManagerInterface $userManager,
        User $user
    ): Response {
        $user = $userManager->findUserByUsername($user->getUsername());
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPlainPassword($user->getPassword());
            $userManager->updateUser($user);
            $this->addFlash('success', 'updated_successfully');

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
