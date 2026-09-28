<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\TaskHandler;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluTestimonialsBundle\Domain\Event\TestimonialPublishedEvent;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Repository\TestimonialRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Handles automation tasks for testimonial workflow transitions.
 */
class TestimonialPublishTaskHandler implements AutomationTaskHandlerInterface
{
    public const TITLE = 'sulu_content.task_handler.publish';

    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
        private readonly ContentWorkflowInterface $contentWorkflow,
        private readonly EntityManagerInterface $entityManager,
        private readonly DomainEventCollectorInterface $domainEventCollector,
    ) {
    }

    public function configureOptionsResolver(OptionsResolver $optionsResolver): OptionsResolver
    {
        return $optionsResolver
            ->setRequired(['class', 'id', 'locale'])
            ->setAllowedTypes('class', 'string')
            ->setAllowedTypes('id', 'string')
            ->setAllowedTypes('locale', 'string');
    }

    public function supports(string $entityClass): bool
    {
        return Testimonial::class === $entityClass;
    }

    public function getConfiguration(): TaskHandlerConfiguration
    {
        return TaskHandlerConfiguration::create(self::TITLE);
    }

    /**
     * @param array{id: string, locale: string} $workload
     */
    public function handle($workload)
    {
        $testimonial = $this->testimonialRepository->findById($workload['id']);

        if (null === $testimonial) {
            return;
        }

        $this->contentWorkflow->apply(
            $testimonial,
            ['locale' => $workload['locale']],
            WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH,
        );

        $this->entityManager->flush();
        $this->domainEventCollector->collect(new TestimonialPublishedEvent($testimonial, []));
    }
}
