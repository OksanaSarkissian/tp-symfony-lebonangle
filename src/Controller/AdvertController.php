<?php

namespace App\Controller;

use DateTime;
use Twig\Environment;
use App\Entity\Advert;
use App\Form\AdvertType;
use App\Model\StateEnum;
use App\Repository\AdvertRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Void_;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AdvertController extends AbstractController
{
    #[Route('/advert', name: 'app_advert_index', methods: ['GET'])]
    public function index(Request $request, Environment $twig, AdvertRepository $advertRepository): Response
     {
       $offset = max(0, $request->query->getInt('offset', 0));
       $paginator = $advertRepository->getAdvertPaginator($offset);

        return $this->render('advert/index.html.twig', [
            'adverts' => $paginator,
           'previous' => $offset - AdvertRepository::PAGINATOR_PER_PAGE,
           'next' => min(count($paginator), $offset + AdvertRepository::PAGINATOR_PER_PAGE),
        ]);
    }

    #[Route('/advert/new', name: 'app_advert_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $advert = (new Advert())->setCreatedAt(new \DateTimeImmutable(''));
        $advert->setState('Draft');
        $form = $this->createForm(AdvertType::class, $advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($advert);
            $entityManager->flush();

            $mailerController = new MailerController();
            $mailerController->sendEmailCreation($mailer, $advert);


            return $this->redirectToRoute('app_advert_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('advert/new.html.twig', [
            'advert' => $advert,
            'form' => $form,
        ]);
    }

    #[Route('/advert/{id}', name: 'app_advert_show', methods: ['GET'])]
    public function show(Advert $advert): Response
    {
        return $this->render('advert/show.html.twig', [
            'advert' => $advert,
        ]);
    }

    #[Route('/admin/advert/publish/{id}', name: 'app_advert_publish', methods: ['GET','POST'])]
    public function publish(Advert $advert, EntityManagerInterface $entityManager, WorkflowInterface $advertStateMachine, MailerInterface $mailer): Response
    {
        if ($advertStateMachine->can($advert, 'publish')) {
            $advertStateMachine->apply($advert, 'publish');
            $advert->setPublishedAt(new \DateTimeImmutable());
            $entityManager->flush();
            $mailerController = new MailerController();
            $mailerController->sendEmail($mailer, $advert);
        }
        return $this->redirectToRoute('app_advert_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin/advert/reject/{id}', name: 'app_advert_reject', methods: ['GET','POST'])]
    public function reject(Advert $advert, EntityManagerInterface $entityManager, WorkflowInterface $advertStateMachine): Response
    {
        if ($advertStateMachine->can($advert, 'reject')) {
            $advertStateMachine->apply($advert, 'reject');
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_advert_index', [], Response::HTTP_SEE_OTHER);
    }

}
