<?php

namespace Modera\BackendSecurityBundle\PasswordStrength\Mail;

use Doctrine\ORM\EntityManagerInterface;
use Modera\BackendLanguagesBundle\Entity\UserSettings;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Entity\UserInterface;
use Modera\SecurityBundle\PasswordStrength\Mail\MailServiceInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2022 Modera Foundation
 */
class DefaultMailService implements MailServiceInterface
{
    private EntityManagerInterface $em;

    private MailerInterface $mailer;

    private string $defaultLocale = 'en';

    private string $mailSender = 'no-reply@no-reply';

    public function __construct(EntityManagerInterface $em, MailerInterface $mailer)
    {
        $this->em = $em;
        $this->mailer = $mailer;
    }

    public function setDefaultLocale(string $locale): void
    {
        $this->defaultLocale = $locale;
    }

    public function setMailSender(string $mailSender): void
    {
        $this->mailSender = $mailSender;
    }

    public function sendPassword(UserInterface $user, string $plainPassword): void
    {
        if (!$user->getEmail()) {
            return;
        }

        $locale = $this->getLocale($user);

        $subject = T::trans('Your password', [], 'mail', $locale);
        $body = T::trans('Your new password is: %plainPassword%', ['%plainPassword%' => $plainPassword], 'mail', $locale);

        $email = (new Email())
            ->from($this->mailSender)
            ->to($user->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            ->html($body)
        ;

        $this->mailer->send($email);
    }

    private function getLocale(UserInterface $user): string
    {
        /** @var ?UserSettings $settings */
        $settings = $this->em->getRepository(UserSettings::class)->findOneBy(['user' => $user->getId()]);
        if ($settings && $settings->getLanguage() && $settings->getLanguage()->isEnabled()) {
            return $settings->getLanguage()->getLocale();
        }

        return $this->defaultLocale;
    }
}
