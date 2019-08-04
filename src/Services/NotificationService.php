<?php

namespace App\Services;

use App\Entity\Order;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class NotificationService
{
    private $mailer;

    private $translator;

    private $parameterBag;

    private $twig;

    private $pdfHandler;

    public function __construct(
        \Swift_Mailer $mailer,
        TranslatorInterface $translator,
        ParameterBagInterface $parameterBag,
        Environment $twig,
        PdfHandlerService $pdfHandlerService
    ) {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
        $this->twig = $twig;
        $this->pdfHandler = $pdfHandlerService;
    }

    private function renderView(Order $order): string
    {
        return $this->twig->render('order/pdf.html.twig', [
            'order' => $order,
        ]);
    }

    private function createPdfAttachment(Order $order): \Swift_Attachment
    {
        return (new \Swift_Attachment())
            ->setFilename(sprintf('order-%s.pdf', $order->getCode()))
            ->setContentType('application/pdf')
            ->setBody($this->pdfHandler->createPdf(
                $this->renderView($order)
            ));
    }

    public function sendOrderByEmail(Order $order): void
    {
        $mailerParams = $this->parameterBag->get('mailer');

        $message = (new \Swift_Message(
            sprintf(
                $this->translator->trans('notifications.emails.order_created.subject'),
                $order->getCode()
            )))
            ->setTo('temporal@gmail.com')
            ->setBody($this->translator->trans('notifications.emails.order_created.body'))
            ->attach($this->createPdfAttachment($order));

        if ($mailerParams['from_address'] && $mailerParams['from_name']) {
            $message->setFrom($mailerParams['from_address'], $mailerParams['from_name']);
        }

        $this->mailer->send($message);
    }
}
