<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tag1 = $this->createTag1();
        $tag2 = $this->createTag2();
        $tag3 = $this->createTag3();

        $manager->persist($tag1);
        $manager->persist($tag2);
        $manager->persist($tag3);

        $manager->flush();
    }

    private function createTag1() : Tag
    {
        $tag = new Tag();

        $tag->setName("paris");

        return $tag;
    }

    private function createTag2() : Tag
    {
        $tag = new Tag();

        $tag->setName("marseille");

        return $tag;
    }

    private function createTag3() : Tag
    {
        $tag = new Tag();

        $tag->setName("strasbourg");

        return $tag;
    }


}
