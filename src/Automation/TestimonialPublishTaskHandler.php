<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialPublishedEvent;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Search\Event\TestimonialPublishedEvent as TestimonialPublishedEventForSearch;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestimonialPublishTaskHandler implements AutomationTaskHandlerInterface
{

    public function __construct(
        private readonly EntityManagerInterface        $entityManager,
        private readonly TranslatorInterface           $translator,
        private readonly DomainEventCollectorInterface $domainEventCollector,
        private readonly EventDispatcherInterface      $dispatcher
    ) {}

    public function handle($workload): void
    {
        if (!\is_array($workload)) {
            return;
        }
        $class = $workload['class'];
        $repository = $this->entityManager->getRepository($class);
        $entity = $repository->findById((int)$workload['id'], $workload['locale']);
        if ($entity === null) {
            return;
        }

        $entity->setPublished(true);

        $this->domainEventCollector->collect(
            new TestimonialPublishedEvent($entity, $workload)
        );

        $repository->save($entity);

        $this->dispatcher->dispatch(new TestimonialPublishedEventForSearch($entity));

    }

    public function configureOptionsResolver(OptionsResolver $optionsResolver): OptionsResolver
    {
        return $optionsResolver->setRequired(['id', 'locale'])
            ->setAllowedTypes('id', 'string')
            ->setAllowedTypes('locale', 'string');
    }

    public function supports(string $entityClass): bool
    {
        return $entityClass === Testimonial::class || \is_subclass_of($entityClass, Testimonial::class);
    }

    public function getConfiguration(): TaskHandlerConfiguration
    {
        return TaskHandlerConfiguration::create($this->translator->trans("sulu_testimonials.publish", [], 'admin'));
    }
}
