<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Tests\Functional\Ticket;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Tests\BaseTestCase;

class GH2936Test extends BaseTestCase
{
    public function testFetchDiscriminatedDocumentsIdentifiedByMap(): void
    {
        $baz = new GH2936BazDocument();

        $this->dm->persist($baz);

        $this->dm->flush();
        $this->dm->clear();

        self::assertCount(1, $this->dm->getRepository(GH2936Superclass::class)->findAll());

        self::assertCount(1, $this->dm->getRepository(GH2936BazDocument::class)->findAll());
    }
    public function testFetchingDiscriminatedDocumentsIdentifiedByValue(): void
    {
        $foo = new GH2936FooDocument();
        $bar = new GH2936BarDocument();

        $this->dm->persist($foo);
        $this->dm->persist($bar);

        $this->dm->flush();
        $this->dm->clear();

        self::assertCount(2, $this->dm->getRepository(GH2936Superclass::class)->findAll());

        self::assertCount(1, $this->dm->getRepository(GH2936FooDocument::class)->findAll());
        self::assertCount(1, $this->dm->getRepository(GH2936BarDocument::class)->findAll());
    }
}

#[ODM\MappedSuperclass]
#[ODM\DiscriminatorField('type')]
#[ODM\InheritanceType('SINGLE_COLLECTION')]
#[ODM\DiscriminatorMap(['baz' => GH2936BazDocument::class])]
class GH2936Superclass
{
    #[ODM\Id]
    public ?string $id;
}

#[ODM\Document]
#[ODM\DiscriminatorValue('foo')]
class GH2936FooDocument extends GH2936Superclass
{
}

#[ODM\Document]
#[ODM\DiscriminatorValue('bar')]
class GH2936BarDocument extends GH2936Superclass
{
}

#[ODM\Document]
class GH2936BazDocument extends GH2936Superclass
{
}
