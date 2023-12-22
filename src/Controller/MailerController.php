<?php
// src/Controller/MailerController.php
namespace App\Controller;

use App\Entity\Advert;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MailerController extends AbstractController
{
    #[Route('/email')]
    public function sendEmail(MailerInterface $mailer, Advert $advert): Response
    {
        $email = (new NotificationEmail())
            ->from('hello@example.com')
            ->to($advert->getEmail())
            ->htmlTemplate('emails/advertPublishedNotification.html.twig')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('An advert has been published!')
            ->text('You\'ve received this mail bc your advert on le bon angle has been published :)')
            ->html('<p>You\'ve received this mail bc your advert on le bon angle has been published :)</p>')
            ->context(['advert' => $advert]);

        $mailer->send($email);

        return new Response('success');
    }

    public function sendEmailCreation(MailerInterface $mailer, Advert $advert): Response
    {
        $email = (new NotificationEmail())
            ->from('hello@example.com')
            ->to('admin@lebonangle.com')
            ->htmlTemplate('emails/advertCreatedNotification.html.twig')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Your advert has been created!')
            ->text('You\'ve received this mail bc your advert on le bon angle has been published :)')
            ->html('<p>You\'ve received this mail bc your advert on le bon angle has been published :)</p>')
            ->context(['advert' => $advert]);

        $mailer->send($email);

        return new Response('success');
    }
}