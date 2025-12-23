<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Trash;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Manuxi\SuluTestimonialsBundle\Trash\TestimonialsTrashItemHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TestimonialsTrashItemHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testStore(): void
    {
        $trashItemRepository = $this->prophesize(TrashItemRepositoryInterface::class);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $doctrineRestoreHelper = $this->prophesize(DoctrineRestoreHelperInterface::class);
        $domainEventCollector = $this->prophesize(DomainEventCollectorInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $trashItemHandler = new TestimonialsTrashItemHandler(
            $trashItemRepository->reveal(),
            $entityManager->reveal(),
            $doctrineRestoreHelper->reveal(),
            $domainEventCollector->reveal(),
            $eventDispatcher->reveal()
        );

        $resource = $this->prophesize(Testimonial::class);
        $resource->getId()->willReturn(1);

        $dimensionContent = $this->prophesize(TestimonialDimensionContent::class);
        $dimensionContent->getLocale()->willReturn('en');
        $dimensionContent->getTitle()->willReturn('Test Title');
        $dimensionContent->getText()->willReturn('Test Text');
        $dimensionContent->getDate()->willReturn(new \DateTimeImmutable());
        $dimensionContent->getRating()->willReturn(5);
        $dimensionContent->getSource()->willReturn('Source');
        $dimensionContent->getWorkflowPlace()->willReturn('published');
        $dimensionContent->getWorkflowPublished()->willReturn(new \DateTimeImmutable());
        $dimensionContent->getImage()->willReturn(null);
        $dimensionContent->getContact()->willReturn(null);
        $dimensionContent->getWebsite()->willReturn(null);
        $dimensionContent->getShowContact()->willReturn(true);
        $dimensionContent->getShowOrganisation()->willReturn(false);
        $dimensionContent->getShowDate()->willReturn(false);
        $dimensionContent->getCreated()->willReturn(new \DateTimeImmutable());
        $dimensionContent->getCreator()->willReturn(null);

        $resource->getDimensionContents()->willReturn(
            new ArrayCollection([$dimensionContent->reveal()])
        );

        $trashItem = $this->prophesize(TrashItemInterface::class);
        $trashItem->getResourceId()->willReturn('1');
        $trashItem->getResourceTitle()->willReturn('Test Title');
        $trashItem->getResourceKey()->willReturn(Testimonial::RESOURCE_KEY);
        $trashItem->getResourceSecurityContext()->willReturn(Testimonial::SECURITY_CONTEXT);

        $trashItemRepository->create(
            Testimonial::RESOURCE_KEY,
            '1',
            'Test Title',
            Argument::any(), // data
            Argument::any(), // restoreType
            Argument::any(), // options
            Testimonial::SECURITY_CONTEXT,
            null,
            null
        )->willReturn($trashItem->reveal());

        $item = $trashItemHandler->store($resource->reveal(), ['locale' => 'en']);

        $this->assertInstanceOf(TrashItemInterface::class, $item);
        $this->assertSame('1', $item->getResourceId());
        $this->assertSame('Test Title', $item->getResourceTitle());
        $this->assertSame(Testimonial::RESOURCE_KEY, $item->getResourceKey());
        $this->assertSame(Testimonial::SECURITY_CONTEXT, $item->getResourceSecurityContext());
    }

    public function testGetResourceKey(): void
    {
        $trashItemRepository = $this->prophesize(TrashItemRepositoryInterface::class);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $doctrineRestoreHelper = $this->prophesize(DoctrineRestoreHelperInterface::class);
        $domainEventCollector = $this->prophesize(DomainEventCollectorInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $trashItemHandler = new TestimonialsTrashItemHandler(
            $trashItemRepository->reveal(),
            $entityManager->reveal(),
            $doctrineRestoreHelper->reveal(),
            $domainEventCollector->reveal(),
            $eventDispatcher->reveal()
        );

        $this->assertSame(Testimonial::RESOURCE_KEY, $trashItemHandler::getResourceKey());
    }
}
