<?php

namespace Modera\BackendSecurityBundle\PasswordStrength\Mail;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Modera\FoundationBundle\Translation\T;
use Modera\BackendLanguagesBundle\Entity\UserSettings;
use Modera\SecurityBundle\PasswordStrength\Mail\MailServiceInterface;
use Modera\SecurityBundle\Entity\User;

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

    /**
     * {@inheritdoc}
     */
    public function sendPassword(User $user, $plainPassword)
    {
        $locale = $this->getLocale($user);

        $subject = T::trans('Your password', array(), 'mail', $locale);
        $body = T::trans('Your new password is: %plainPassword%', array('%plainPassword%' => $plainPassword), 'mail', $locale);

        $email = (new Email())
            ->from($this->mailSender)
            ->to($user->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            ->html($body)
        ;

        $this->mailer->send($email);
    }

    private function getLocale(User $user): string
    {
        /* @var UserSettings $settings */
        $settings = $this->em->getRepository(UserSettings::class)->findOneBy(array('user' => $user->getId()));
        if ($settings && $settings->getLanguage() && $settings->getLanguage()->isEnabled()) {
            return $settings->getLanguage()->getLocale();
        }

        return $this->defaultLocale;
    }
}
