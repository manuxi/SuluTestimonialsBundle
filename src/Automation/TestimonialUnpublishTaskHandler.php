<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialUnpublishedEvent;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Search\Event\TestimonialPublishedEvent as SearchPublishedEvent;
use Manuxi\SuluTestimonialsBundle\Search\Event\TestimonialUnpublishedEvent as SearchUnpublishedEvent;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestimonialUnpublishTaskHandler implements AutomationTaskHandlerInterface
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
        $this->dispatcher->dispatch(new SearchUnpublishedEvent($entity));

        $entity->setPublished(false);
        $repository->save($entity);

        $this->domainEventCollector->collect(
            new TestimonialUnpublishedEvent($entity, $workload)
        );

        $this->dispatcher->dispatch(new SearchPublishedEvent($entity));
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
        return TaskHandlerConfiguration::create($this->translator->trans("sulu_testimonials.unpublish", [], 'admin'));
    }
}
