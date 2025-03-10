<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Domain\Event\Settings;

class ModifiedEvent extends AbstractEvent
{
    public function getEventType(): string
    {
        return 'modified';
    }
}
