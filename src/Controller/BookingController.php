<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method getDoctrine()
 */
class BookingController extends AbstractController
{
    #[Route('/', name: 'ma_page')]
    public function index(BookingRepository $bookingRepository): Response
    {
        // Récupère toutes les réservations depuis la base de données
        $bookings = $bookingRepository->findAll();

        // Passez les réservations au template index.html.twig
        return $this->render('index.html.twig', [
            'bookings' => $bookings,
        ]);
    }



    #[Route('/book-a-table', name: 'book_a_table', methods: ['POST'])]
    public function createBooking(Request $request, EntityManagerInterface $em): Response
    {
        // Create a new Booking instance
        $booking = new Booking();

        // Get data from the request
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $phone = $request->request->get('phone');
        $date = $request->request->get('date');
        $time = $request->request->get('time');
        $people = $request->request->get('people');
        $message = $request->request->get('message', null);

        // Server-side validation
        if (!$name || !$email || !$phone || !$date || !$time || !$people) {
            return $this->json([
                'status' => 'error',
                'message' => 'All required fields must be filled out.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Set entity fields
        try {
            $booking->setName($name);
            $booking->setEmail($email);
            $booking->setPhone($phone);
            $booking->setDate(new \DateTime($date));
            $booking->setTime(new \DateTime($time));
            $booking->setPeople((int)$people);
            $booking->setMessage($message);

            // Save the booking to the database
            $em->persist($booking);
            $em->flush();

            // Return success response
            return $this->json([
                'status' => 'success',
                'message' => 'Your booking has been saved. We will contact you soon.'
            ], Response::HTTP_CREATED);

            // Optionally, you can redirect to a list page here
            // return $this->redirectToRoute('booking_list');
        } catch (\Exception $e) {
            return $this->json([
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
        return $this->render('booking_list.html.twig', [
            'bookings' => $bookings,
        ]);
    }


}