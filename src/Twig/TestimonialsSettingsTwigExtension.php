<?php

namespace Manuxi\SuluTestimonialsBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;

use Manuxi\SuluTestimonialsBundle\Entity\TestimonialsSettings;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TestimonialsSettingsTwigExtension extends AbstractExtension
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('load_testimonials_settings', [$this, 'loadTestimonialsSettings']),
        ];
    }

    public function loadTestimonialsSettings(): TestimonialsSettings
    {
        $testimonialsSettings = $this->entityManager->getRepository(TestimonialsSettings::class)->findOneBy([]) ?? null;

        return $testimonialsSettings ?: new TestimonialsSettings();
    }
}