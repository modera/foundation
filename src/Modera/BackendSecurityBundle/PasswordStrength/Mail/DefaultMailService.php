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
 * @copyright 2022 Modera Foundation
 */
class DefaultMailService implements MailServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private readonly string $defaultLocale = 'en',
        private readonly string $mailSender = 'no-reply@no-reply',
    ) {
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
