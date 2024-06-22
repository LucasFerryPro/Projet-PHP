<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventFilterType;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\NotificationsService;
use App\Service\PlaceManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/event')]
class EventController extends AbstractController
{
    private NotificationsService $notificationService;

    public function __construct(NotificationsService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    #[Route('/', name: 'app_event_index', methods: ['GET', 'POST'])]
    public function index(EventRepository $eventRepository, Request $request): Response
    {
        $form = $this->createForm(EventFilterType::class);
        $form->handleRequest($request);

        $filters = $form->isSubmitted() && $form->isValid() ? $form->getData() : [];
        $itemsPerPage = 5;
        $page = $request->query->getInt('page', 1);

        $events = $eventRepository->findAllFiltered($this->getUser(), $filters, $itemsPerPage, $page);
        $maxPages = ceil(count($events) / $itemsPerPage);

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'maxPages' => $maxPages,
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/inscriptions', name: 'app_event_inscriptions', methods: ['GET'])]
    public function getEventsByUser(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findEventsByUserParticipating($this->getUser());

        return $this->render('event/inscriptions.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $creator = $this->getUser();
            $event->setCreator($creator);
            $event->addParticipant($creator);

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Event created successfully.');

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    #[IsGranted('view','event')]
    public function show(Event $event, PlaceManager $placeManager): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'numberOfRegistration' => $placeManager->getNumberOfRegistration($event),
            'canRegister' => $placeManager->canRegister($event),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit_or_delete','event')]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();


            // Envoi de l'email à tous les participants
            foreach ($event->getParticipants() as $participant) {
                $this->notificationService->sendEmail(
                    $participant->getEmail(),
                    "Event Updated: {$event->getTitle()}",
                    "Hello {$participant->getFirstname()}, the event '{$event->getTitle()}' has been updated."
                );
            }

            $this->addFlash('success', 'Event updated successfully.');

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    #[IsGranted('edit_or_delete', 'event')]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $eventName = $event->getTitle();
        $eventParticipants = $event->getParticipants()->toArray(); // Convertir en tableau pour préserver les données
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $event->getId(), $submittedToken)) {
            $entityManager->remove($event);
            $entityManager->flush();

            // Envoi de l'email à tous les participants
            foreach ($eventParticipants as $participant) {
                $this->notificationService->sendEmail(
                    $participant->getEmail(),
                    "Event Deleted: {$eventName}",
                    "Hello {$participant->getFirstname()}, the event '{$eventName}' has been deleted."
                );
            }

            $this->addFlash('success', 'Event deleted successfully.');
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/registration/{id}', name: 'app_event_registration', methods: ['GET'])]
    public function registrationEvent(Request $request, Event $event, EntityManagerInterface $entityManager):Response
    {
        $event->addParticipant($this->getUser());
        $entityManager->flush();

        // Envoi de l'email à l'utilisateur inscrit
        $this->notificationService->sendEmail(
            $this->getUser()->getEmail(),
            "Event Registration: {$event->getTitle()}",
            "Hello {$this->getUser()->getFirstname()}, you have successfully registered for the event '{$event->getTitle()}'."
        );

        // Envoi de l'email au créateur de l'événement
        $this->notificationService->sendEmail(
            $event->getCreator()->getEmail(),
            "New Participant: {$event->getTitle()}",
            "Hello {$event->getCreator()->getFirstname()}, {$this->getUser()->getFirstname()} {$this->getUser()->getLastname()} has registered for your event '{$event->getTitle()}'."
        );

        $this->addFlash('success', 'Registration successful.');

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/cancellation/{id}', name: 'app_event_cancellation', methods: ['GET'])]
    public function cancellationEvent(Request $request, Event $event, EntityManagerInterface $entityManager):Response
    {
        $event->removeParticipant($this->getUser());
        $entityManager->flush();

        // Envoi de l'email à l'utilisateur qui a annulé son inscription
        $this->notificationService->sendEmail(
            $this->getUser()->getEmail(),
            "Event Cancellation: {$event->getTitle()}",
            "Hello {$this->getUser()->getFirstname()}, you have canceled your registration for the event '{$event->getTitle()}'."
        );

        // Envoi de l'email au créateur de l'événement
        $this->notificationService->sendEmail(
            $event->getCreator()->getEmail(),
            "Participant Cancellation: {$event->getTitle()}",
            "Hello {$event->getCreator()->getFirstname()}, {$this->getUser()->getFirstname()} {$this->getUser()->getLastname()} has canceled their registration for your event '{$event->getTitle()}'."
        );

        $this->addFlash('success', 'Cancellation successful.');

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
