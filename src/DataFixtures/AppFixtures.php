<?php

namespace App\DataFixtures;

use App\Entity\AdminUser;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {
        
    }
    public function load(ObjectManager $manager): void
    {
        
        // $product = new Product();
        // $manager->persist($product);
        $userTest = new AdminUser();
        $userTest->setUsername('admin');
        $userTest->setEmail('test@gmail.com');
        $userTest->setPlainPassword('admin');
        $userTest->setPassword('$2y$13$roezssXp53Qn2zYnruCsa.8ZdTt/vEoQHrNQewLPhoxtCckY3GJ3i');
        $manager->persist($userTest);
        
        $category = new Category();
        $category->setName('testing');
        $manager->persist($category);

        $manager->flush();
    }
}
