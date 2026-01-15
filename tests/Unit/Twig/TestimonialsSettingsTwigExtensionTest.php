<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Twig;

use Doctrine\ORM\EntityRepository;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialsSettings;
use Manuxi\SuluTestimonialsBundle\Twig\TestimonialsSettingsTwigExtension;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class TestimonialsSettingsTwigExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testLoadTestimonialsSettings(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $repository = $this->prophesize(EntityRepository::class);

        $settings = new TestimonialsSettings();
        $repository->findOneBy([])->willReturn($settings);

        $entityManager->getRepository(TestimonialsSettings::class)->willReturn($repository->reveal());

        $extension = new TestimonialsSettingsTwigExtension($entityManager->reveal());

        $result = $extension->loadTestimonialsSettings();

        $this->assertSame($settings, $result);
    }
}
