<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Content\DataMapper;

use Manuxi\SuluTestimonialsBundle\Content\DataMapper\AutoAuthorDataMapper;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;
interface UserWithContact extends UserInterface
{
    public function getContact();
}

class AutoAuthorDataMapperTest extends TestCase
{
    use ProphecyTrait;

    public function testMap(): void
    {
        $security = $this->prophesize(Security::class);
        // Use the helper interface for the mock
        $user = $this->prophesize(UserWithContact::class);
        $contact = $this->prophesize(ContactInterface::class);

        $user->getContact()->willReturn($contact->reveal());
        $security->getUser()->willReturn($user->reveal());

        $mapper = new AutoAuthorDataMapper($security->reveal());

        $localizedObject = $this->prophesize(TestimonialDimensionContent::class);
        $unlocalizedObject = $this->prophesize(TestimonialDimensionContent::class);

        $localizedObject->getAuthor()->willReturn(null);
        $localizedObject->getAuthored()->willReturn(null);

        $localizedObject->setAuthor($contact->reveal())->shouldBeCalled();
        $localizedObject->setAuthored(Argument::type(\DateTimeImmutable::class))->shouldBeCalled();

        $mapper->map(
            $unlocalizedObject->reveal(),
            $localizedObject->reveal(),
            []
        );
    }
}
