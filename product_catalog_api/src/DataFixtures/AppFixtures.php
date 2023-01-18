<?php

namespace App\DataFixtures;

use App\Factory\CategoryFactory;
use App\Factory\ImageFactory;
use App\Factory\ProductFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        CategoryFactory::new()->create(['name' => 'GPU']);
        CategoryFactory::new()->create(['name' => 'CPU']);
        CategoryFactory::new()->create(['name' => 'Motherboard']);
        CategoryFactory::new()->create(['name' => 'RAM']);
        CategoryFactory::new()->create(['name' => 'SSD']);

        ProductFactory::new()->createMany(25);

        ImageFactory::new()->createMany(30);

        $manager->flush();
    }
}
