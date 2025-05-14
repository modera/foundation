<?php

namespace Modera\SecurityBundle\Tests\Unit\PasswordStrength;

use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\PasswordStrength\BadPasswordException;
use Modera\SecurityBundle\PasswordStrength\Mail\MailServiceInterface;
use Modera\SecurityBundle\PasswordStrength\PasswordConfigInterface;
use Modera\SecurityBundle\PasswordStrength\PasswordManager;
use Modera\SecurityBundle\PasswordStrength\StrongPassword;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserPasswordEncoderDummy implements UserPasswordHasherInterface
{
    public array $mapping = [];

    public function hashPassword(PasswordAuthenticatedUserInterface $user, string $plainPassword): string
    {
        return $this->mapping[$plainPassword];
    }

    public function isPasswordValid(PasswordAuthenticatedUserInterface $user, string $plainPassword): bool
    {
        return true;
    }

    public function needsRehash(PasswordAuthenticatedUserInterface $user): bool
    {
        return false;
    }
}

class PasswordManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testHasPasswordAlreadyBeenUsedWithinLastRotationPeriod(): void
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(90)
        ;

        $encoderDummy = new UserPasswordEncoderDummy();
        $encoderDummy->mapping = [
            'foo' => 'encoded-foo',
            'bar' => 'encoded-bar',
            'baz' => 'encoded-baz',
            'yoyo' => 'encoded-yoyo',
        ];

        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn([])
        ;

        $pm = new PasswordManager(
            $passwordConfigMock,
            $encoderDummy,
            $validatorMock,
            \Phake::mock(MailServiceInterface::class),
        );

        $user = new User();
        $this->assertFalse($pm->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, 1234));

        $user = new User();
        $user->setMeta([
            'modera_security' => [
                'used_passwords' => [
                    $this->createTimeWithDaysAgo(200) => 'encoded-foo',
                    $this->createTimeWithDaysAgo(91) => 'encoded-bar',
                    $this->createTimeWithDaysAgo(50) => 'encoded-baz',
                    $this->createTimeWithDaysAgo(10) => 'encoded-yoyo',
                ],
            ],
        ]);

        $this->assertFalse($pm->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, 'foo'));
        $this->assertFalse($pm->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, 'bar'));
        $this->assertTrue($pm->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, 'baz'));
        $this->assertTrue($pm->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, 'yoyo'));
    }

    public function testIsItTimeToRotatePassword(): void
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(90)
        ;

        $encoderDummy = new UserPasswordEncoderDummy();
        $encoderDummy->mapping = [
            'foo' => 'encoded-foo',
            'bar' => 'encoded-bar',
            'baz' => 'encoded-baz',
            'yoyo' => 'encoded-yoyo',
        ];

        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn([])
        ;

        $pm = new PasswordManager(
            $passwordConfigMock,
            $encoderDummy,
            $validatorMock,
            \Phake::mock(MailServiceInterface::class),
        );

        $this->assertTrue($pm->isItTimeToRotatePassword(new User()));

        $user = new User();
        $user->setMeta([
            'modera_security' => [
                'used_passwords' => [],
            ],
        ]);

        $this->assertTrue($pm->isItTimeToRotatePassword($user));

        $user = new User();
        $user->setMeta([
            'modera_security' => [
                'used_passwords' => [
                    $this->createTimeWithDaysAgo(200) => 'encoded-foo',
                    $this->createTimeWithDaysAgo(100) => 'encoded-bar',
                ],
            ],
        ]);

        $this->assertTrue($pm->isItTimeToRotatePassword($user));

        $user = new User();
        $user->setMeta([
            'modera_security' => [
                'used_passwords' => [
                    $this->createTimeWithDaysAgo(95) => 'encoded-foo',
                    $this->createTimeWithDaysAgo(60) => 'encoded-bar',
                ],
            ],
        ]);

        $this->assertFalse($pm->isItTimeToRotatePassword($user));
    }

    public function testValidatePassword(): void
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        $encoderDummy = new UserPasswordEncoderDummy();
        $validatorMock = \Phake::mock(ValidatorInterface::class);

        $constraintViolationList = \Phake::mock(ConstraintViolationListInterface::class);

        \Phake::when($validatorMock)
            ->validate('foo123', $this->isInstanceOf(StrongPassword::class))
            ->thenReturn($constraintViolationList)
        ;

        $pm = new PasswordManager(
            $passwordConfigMock,
            $encoderDummy,
            $validatorMock,
            \Phake::mock(MailServiceInterface::class),
        );

        $this->assertEquals($constraintViolationList, $pm->validatePassword('foo123'));
    }

    public function testEncodeAndSetPasswordHappyPath(): void
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(null)
        ;

        $encoderDummy = new UserPasswordEncoderDummy();
        $encoderDummy->mapping = [
            'foo' => 'encoded-foo',
            'bar' => 'encoded-bar',
            'baz' => 'encoded-baz',
            'yoyo' => 'encoded-yoyo',
        ];

        $constraintViolationList = \Phake::mock(ConstraintViolationListInterface::class);

        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn($constraintViolationList)
        ;

        $pm = new PasswordManager(
            $passwordConfigMock,
            $encoderDummy,
            $validatorMock,
            \Phake::mock(MailServiceInterface::class),
        );

        $user = new User();
        $pm->encodeAndSetPassword($user, 'foo');

        $meta = $user->getMeta();
        $this->assertArrayHasKey('modera_security', $meta);
        $this->assertArrayHasKey('used_passwords', $meta['modera_security']);
        $this->assertEquals(1, \count($meta['modera_security']['used_passwords']));
        $this->assertLessThan(10, \array_keys($meta['modera_security']['used_passwords'])[0] - \time());
        $usedPasswords = \array_values($meta['modera_security']['used_passwords']);
        $this->assertEquals('encoded-foo', $usedPasswords[0]);
    }

    public function testEncodeAndSetPasswordForcePasswordRotationTracesRemoved(): void
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(null)
        ;

        $encoderDummy = new UserPasswordEncoderDummy();
        $encoderDummy->mapping = [
            'foo' => 'encoded-foo',
            'bar' => 'encoded-bar',
            'baz' => 'encoded-baz',
            'yoyo' => 'encoded-yoyo',
        ];

        $constraintViolationList = \Phake::mock(ConstraintViolationListInterface::class);

        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn($constraintViolationList)
        ;

        $pm = new PasswordManager(
            $passwordConfigMock,
            $encoderDummy,
            $validatorMock,
            \Phake::mock(MailServiceInterface::class),
        );

        $user = new User();
        $user->setMeta([
            'modera_security' => [
                'force_password_rotation' => true,
            ],
        ]);
        $pm->encodeAndSetPassword($user, 'foo');

        $meta = $user->getMeta();
        $this->assertArrayHasKey('modera_security', $meta);
        $this->assertArrayHasKey('used_passwords', $meta['modera_security']);
        $this->assertEquals(1, \count($meta['modera_security']['used_passwords']));
        $this->assertLessThan(10, \array_keys($meta['modera_security']['used_passwords'])[0] - \time());
        $usedPasswords = \array_values($meta['modera_security']['used_passwords']);
        $this->assertEquals('encoded-foo', $usedPasswords[0]);
        $this->assertArrayNotHasKey('force_password_rotation', $meta['modera_security']);
    }

    public function testEncodeAndSetPasswordRotationCheckFail(): void
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(99)
        ;

        $encoderDummy = new UserPasswordEncoderDummy();
        $encoderDummy->mapping = [
            'foo' => 'encoded-foo',
            'bar' => 'encoded-bar',
            'baz' => 'encoded-baz',
        ];

        $constraintViolationList = \Phake::mock(ConstraintViolationListInterface::class);

        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn($constraintViolationList)
        ;

        $pm = new PasswordManager(
            $passwordConfigMock,
            $encoderDummy,
            $validatorMock,
            \Phake::mock(MailServiceInterface::class),
        );

        $user = new User();
        $user->setMeta([
            'modera_security' => [
                'used_passwords' => [
                    $this->createTimeWithDaysAgo(125) => 'encoded-baz',
                    $this->createTimeWithDaysAgo(100) => 'encoded-bar',
                    $this->createTimeWithDaysAgo(25) => 'encoded-foo',
                ],
            ],
        ]);

        $thrownException = null;
        try {
            $pm->encodeAndSetPassword($user, 'foo');
        } catch (BadPasswordException $e) {
            $thrownException = $e;
        }
        $this->assertEquals(
            'Given password cannot be used because it has been already used in last 99 days.',
            $thrownException->getMessage()
        );
        $this->assertEquals(3, \count($user->getMeta()['modera_security']['used_passwords']));

        $pm->encodeAndSetPassword($user, 'bar');
        $this->assertEquals(2, \count($user->getMeta()['modera_security']['used_passwords']));
    }

    public function testEncodeAndSetPasswordValidationFail(): void
    {
        $this->expectException(BadPasswordException::class);
        $this->expectExceptionMessage('error-msg1');

        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(null)
        ;

        $encoderDummy = new UserPasswordEncoderDummy();
        $encoderDummy->mapping = [
            'foo' => 'encoded-foo',
        ];

        $violation = \Phake::mock(ConstraintViolation::class);
        \Phake::when($violation)
            ->getMessage()
            ->thenReturn('error-msg1')
        ;

        $constraintViolationList = new ConstraintViolationList([$violation]);
        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate('foo', $this->isInstanceOf(StrongPassword::class))
            ->thenReturn($constraintViolationList)
        ;

        $pm = new PasswordManager(
            $passwordConfigMock,
            $encoderDummy,
            $validatorMock,
            \Phake::mock(MailServiceInterface::class),
        );

        $user = new User();
        $pm->encodeAndSetPassword($user, 'foo');
    }

    private function assertGeneratePassword(int $minLength, bool $letterRequired): void
    {
        $encoderDummy = new UserPasswordEncoderDummy();
        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn([])
        ;

        $pm = new PasswordManager(
            $this->createPasswordConfigMock($minLength, true, $letterRequired),
            $encoderDummy,
            $validatorMock,
            \Phake::mock(MailServiceInterface::class),
        );

        $password = $pm->generatePassword();

        switch ($letterRequired) {
            case PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL:
                $pattern = '/[A-Za-z]/';
                break;
            case PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_AND_NON_CAPITAL:
                $pattern = '/(?=.*[A-Z])(?=.*[a-z])/';
                break;
            case PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL:
                $pattern = '/[A-Z]/';
                break;
            case PasswordConfigInterface::LETTER_REQUIRED_TYPE_NON_CAPITAL:
                $pattern = '/[a-z]/';
                break;
        }

        $this->assertNotNull($password);
        $this->assertTrue(strlen($password) == $minLength);
        $this->assertMatchesRegularExpression('/[0-9]/', $password);
        $this->assertMatchesRegularExpression($pattern, $password);
    }

    public function testGeneratePassword(): void
    {
        $this->assertGeneratePassword(6, PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL);
        $this->assertGeneratePassword(8, PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_AND_NON_CAPITAL);
        $this->assertGeneratePassword(10, PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL);
        $this->assertGeneratePassword(12, PasswordConfigInterface::LETTER_REQUIRED_TYPE_NON_CAPITAL);
    }

    public function testEncodeAndSetPasswordAndThenEmailIt(): void
    {
        $mailServiceMock = \Phake::mock(MailServiceInterface::class);

        $constraintViolationList = \Phake::mock(ConstraintViolationListInterface::class);

        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn($constraintViolationList)
        ;

        $pm = new PasswordManager(
            \Phake::mock(PasswordConfigInterface::class),
            \Phake::mock(UserPasswordEncoderDummy::class),
            $validatorMock,
            $mailServiceMock,
        );

        $user = new User();

        $pm->encodeAndSetPasswordAndThenEmailIt($user, 'foobar');

        $this->assertArrayHasKey('modera_security', $user->getMeta());
        $meta = $user->getMeta()['modera_security'];
        $this->assertArrayHasKey('used_passwords', $meta);
        $this->assertEquals(1, \count($meta['used_passwords']));
        $this->assertArrayHasKey('force_password_rotation', $meta);

        \Phake::verify($mailServiceMock)
            ->sendPassword($user, 'foobar')
        ;
    }

    public function testIsItTimeToRotatePasswordAfterItHasBeenEmailed(): void
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(90)
        ;
        $encoderDummy = new UserPasswordEncoderDummy();
        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn([])
        ;

        $pm = new PasswordManager(
            $passwordConfigMock,
            $encoderDummy,
            $validatorMock,
            \Phake::mock(MailServiceInterface::class),
        );

        $user = new User();
        $user->setMeta([
            'modera_security' => [
                'used_passwords' => [
                    \time() => '1234',
                ],
                'force_password_rotation' => true,
            ],
        ]);

        $this->assertTrue($pm->isItTimeToRotatePassword($user));

        $user->setMeta([
            'modera_security' => [
                'used_passwords' => [
                    \time() => '1234',
                ],
            ],
        ]);

        $this->assertFalse($pm->isItTimeToRotatePassword($user));
    }

    private function createPasswordConfigMock(int $minLength, bool $isNumberRequired, bool $letterRequired): PasswordConfigInterface
    {
        $pc = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($pc)
            ->getMinLength()
            ->thenReturn($minLength)
        ;
        \Phake::when($pc)
            ->isNumberRequired()
            ->thenReturn($isNumberRequired)
        ;
        \Phake::when($pc)
            ->isLetterRequired()
            ->thenReturn(false !== $letterRequired)
        ;
        \Phake::when($pc)
            ->getLetterRequiredType()
            ->thenReturn($letterRequired)
        ;
        \Phake::when($pc)
            ->getRotationPeriodInDays()
            ->thenReturn(90)
        ;

        return $pc;
    }

    private function createTimeWithDaysAgo(int $days): int
    {
        $now = new \DateTime('now');
        $now->modify(\sprintf('-%s day', $days));

        return $now->getTimestamp();
    }
}
