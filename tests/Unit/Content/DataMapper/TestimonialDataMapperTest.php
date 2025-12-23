<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Content\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluTestimonialsBundle\Content\DataMapper\TestimonialDataMapper;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\Media;

class TestimonialDataMapperTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $entityManager;
    private TestimonialDataMapper $mapper;

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->mapper = new TestimonialDataMapper($this->entityManager->reveal());
    }

    public function testMap(): void
    {
        $unlocalizedContent = $this->prophesize(TestimonialDimensionContent::class);
        $localizedContent = $this->prophesize(TestimonialDimensionContent::class);

        $data = [
            'title' => 'Test Title',
            'text' => 'Test Text',
            'rating' => '5',
            'source' => 'Google',
            'website' => 'https://google.com',
            'showOrganisation' => true,
            'showContact' => false,
            'showDate' => true,
            'date' => '2023-01-01',
            'image' => ['id' => 123],
            'contact' => ['id' => 456],
            'author' => ['id' => 789],
            'authored' => '2023-01-02T10:00:00',
        ];

        $media = $this->prophesize(Media::class);
        $this->entityManager->getReference(Media::class, 123)->willReturn($media->reveal());

        $contact = $this->prophesize(ContactInterface::class);
        $this->entityManager->getReference(ContactInterface::class, 456)->willReturn($contact->reveal());

        $author = $this->prophesize(ContactInterface::class);
        $this->entityManager->getReference(ContactInterface::class, 789)->willReturn($author->reveal());

        // Simple fields
        $unlocalizedContent->setTitle('Test Title')->shouldBeCalled();
        $localizedContent->setTitle('Test Title')->shouldBeCalled();

        $unlocalizedContent->setText('Test Text')->shouldBeCalled();
        $localizedContent->setText('Test Text')->shouldBeCalled();

        $unlocalizedContent->setRating('5')->shouldBeCalled();
        $localizedContent->setRating('5')->shouldBeCalled();

        $unlocalizedContent->setSource('Google')->shouldBeCalled();
        $localizedContent->setSource('Google')->shouldBeCalled();

        $unlocalizedContent->setWebsite('https://google.com')->shouldBeCalled();
        $localizedContent->setWebsite('https://google.com')->shouldBeCalled();

        $unlocalizedContent->setShowOrganisation(true)->shouldBeCalled();
        $localizedContent->setShowOrganisation(true)->shouldBeCalled();

        $unlocalizedContent->setShowContact(false)->shouldBeCalled();
        $localizedContent->setShowContact(false)->shouldBeCalled();

        $unlocalizedContent->setShowDate(true)->shouldBeCalled();
        $localizedContent->setShowDate(true)->shouldBeCalled();

        $unlocalizedContent->setDate(Argument::type(\DateTimeImmutable::class))->shouldBeCalled();
        $localizedContent->setDate(Argument::type(\DateTimeImmutable::class))->shouldBeCalled();

        // Relations
        $unlocalizedContent->setImage($media->reveal())->shouldBeCalled();
        $localizedContent->setImage($media->reveal())->shouldBeCalled();

        $unlocalizedContent->setContact($contact->reveal())->shouldBeCalled();
        $localizedContent->setContact($contact->reveal())->shouldBeCalled();

        $localizedContent->setAuthor($author->reveal())->shouldBeCalled();
        $localizedContent->setAuthored(Argument::type(\DateTimeImmutable::class))->shouldBeCalled();

        $this->mapper->map(
            $unlocalizedContent->reveal(),
            $localizedContent->reveal(),
            $data
        );
    }
}
