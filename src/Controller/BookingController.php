<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\User;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method getDoctrine()
 */
class BookingController extends AbstractController
{
    #[Route('/', name: 'ma_page')]
    public function index(MenuRepository $menuRepository): Response
    {
        // Récupère toutes les réservations depuis la base de données
        $menus = $menuRepository->findAll();

        // Passez la variable 'menus' à votre template
        return $this->render('index.html.twig', [
            'menus' => $menus,
        ]);
    }



    #[Route('/book-a-table', name: 'book_a_table', methods: ['POST'])]
    public function createBooking(Request $request, EntityManagerInterface $em, Security  $security): JsonResponse
    {

        $booking = new Booking();

        $name = $request->request->get('name');
        $phone = $request->request->get('phone');
        $date = $request->request->get('date');
        $time = $request->request->get('time');
        $people = $request->request->get('people');
        $message = $request->request->get('message', null);

        $user = $security->getUser();

        if ($user) {
            $email = $user->getEmail();
        } else {
            $email = $request->request->get('email');

            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$existingUser) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'The provided email address is not registered.'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        if (!$name || !$phone || !$date || !$time || !$people || !$email) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'All required fields must be filled out.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $booking->setName($name);
            $booking->setEmail($email);
            $booking->setPhone($phone);
            $booking->setDate(new \DateTime($date));
            $booking->setTime(new \DateTime($time));
            $booking->setPeople((int)$people);
            $booking->setMessage($message);

            $em->persist($booking);
            $em->flush();

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Your booking has been saved. We will contact you soon.'
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'An error occurred while saving your booking: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    // Route pour afficher la liste des réservations

    #[Route('/bookings', name: 'booking_list')]
    public function listBookings(BookingRepository $bookingRepository): Response
    {
        // Récupère toutes les réservations depuis la base de données
        $bookings = $bookingRepository->findAll();

        // Passez la variable 'bookings' à votre template
        return $this->render('booking/list.html.twig', [
            'bookings' => $bookings,
        ]);
    }

    #[Route('booking/edit-booking/{id}', name: 'edit_booking')]
    public function editBooking($id, BookingRepository $bookingRepository, Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer la réservation
        $booking = $bookingRepository->find($id);

        if (!$booking) {
            throw $this->createNotFoundException('Booking not found.');
        }

        // Pré-remplir le formulaire avec les données existantes
        if ($request->isMethod('POST')) {
            $booking->setName($request->request->get('name'));
            $booking->setEmail($request->request->get('email'));
            $booking->setPhone($request->request->get('phone'));
            $booking->setDate(new \DateTime($request->request->get('date')));
            $booking->setTime(new \DateTime($request->request->get('time')));
            $booking->setPeople((int)$request->request->get('people'));
            $booking->setMessage($request->request->get('message', null));

            $em->flush();

            return $this->redirectToRoute('booking_list');
        }

        // Afficher le formulaire avec les valeurs de l'entité existante
        return $this->render('booking/edit_booking.html.twig', [
            'booking' => $booking,
        ]);
    }
    #[Route('/{id}', name: 'delete_booking', methods: ['POST', 'DELETE'])]
    public function deleteBooking($id, BookingRepository $bookingRepository, EntityManagerInterface $em): Response
    {
        $booking = $bookingRepository->find($id);

        if ($booking) {
            $em->remove($booking);
            $em->flush();
        }

        return $this->redirectToRoute('booking_list');
    }



}