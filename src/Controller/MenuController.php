<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\BookingRepository;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @method getDoctrine()
 */
class MenuController extends AbstractController
{
    #[Route('/menu/add', name: 'menu_add')]
    public function addMenu(Request $request, EntityManagerInterface $entityManager): Response
    {
        $menu = new Menu();

        // Créez le formulaire
        $form = $this->createForm(MenuType::class, $menu);

        // Traitez la requête HTTP
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de l'image si elle est fournie
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('menu_images_directory'), // Définissez ce paramètre dans config/services.yaml
                    $newFilename
                );
                $menu->setImage($newFilename);
            }

            // Sauvegarder dans la base de données
            $entityManager->persist($menu);
            $entityManager->flush();

            $this->addFlash('success', 'Le menu a été ajouté avec succès.');

            return $this->redirectToRoute('menu_add');
        }

        // Affichez le formulaire dans le template
        return $this->render('menu/addMenu.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/menu-list', name: 'menu_list')]
    public function listMenu(MenuRepository $menuRepository): Response
    {
        // Récupère toutes les réservations depuis la base de données
        $menus = $menuRepository->findAll();

        // Passez la variable 'bookings' à votre template
        return $this->render('menu/adminMenuList.html.twig', [
            'menus' => $menus,
        ]);
    }
    #[Route('/menu_delete/{id}', name: 'menu_delete', methods: ['POST', 'DELETE'])]
    public function deleteBooking($id, MenuRepository $menuRepository, EntityManagerInterface $em): Response
    {
        $menu = $menuRepository->find($id);

        if ($menu) {
            $em->remove($menu);
            $em->flush();
        }

        return $this->redirectToRoute('menu_list');
    }


}
