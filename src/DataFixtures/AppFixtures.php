<?php

namespace App\DataFixtures;

use App\Entity\Item;
use App\Entity\User;
use App\Entity\UserItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('test@gmail.com');
        $user->setBalance(100);
        $manager->persist($user);

        $item = new Item();
        $item->setName('product1');
        $item->setCost(13);
        $manager->persist($item);

        $manager->flush();
    }
}
