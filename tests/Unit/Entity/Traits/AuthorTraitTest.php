<?php

namespace Manuxi\SuluTestimonialsBundle\Tests\Unit\Entity\Traits;

use Manuxi\SuluTestimonialsBundle\Entity\Traits\AuthorTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class AuthorTraitTest extends SuluTestCase
{
    private MockObject $mock;
    private ObjectProphecy $user;

    protected function setUp(): void
    {
        $this->mock = $this->getMockForTrait(AuthorTrait::class);
        $this->user = $this->prophesize(Contact::class);
    }

    public function testSetAuthored(): void
    {
        $this->assertSame($this->mock, $this->mock->setAuthor($this->user->reveal()));
    }

    public function testGetAuthored(): void
    {
        $this->assertNull($this->mock->getAuthor());
        $this->mock->setAuthor($this->user->reveal());
        $this->assertSame($this->user->reveal(), $this->mock->getAuthor());
    }

}
