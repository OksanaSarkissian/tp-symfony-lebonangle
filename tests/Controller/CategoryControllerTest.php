<?php

namespace App\Test\Controller;

use App\Entity\Category;
use App\Entity\AdminUser;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private CategoryRepository $repository;
    private string $path = '/admin/category/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Category::class);

        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->adminUserRepository = static::getContainer()->get('doctrine')->getRepository(AdminUser::class);
        $testUser = $this->adminUserRepository->findOneBy(['username' => 'admin']); 
        
        $this->client->loginUser($testUser);

        foreach ($this->repository->findAll() as $object) {
            if ($object->getName() !== 'testing') {
                $this->manager->remove($object);
            }
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        // self::assertPageTitleContains('Category index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'category[name]' => 'Testing',
        ]);

        self::assertResponseRedirects('/admin/category/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        
        $fixture = new Category();
        $fixture->setName('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Category');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        
        $fixture = new Category();
        $fixture->setName('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'category[name]' => 'Something New',
        ]);

        self::assertResponseRedirects('/admin/category/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[1]->getName());
    }
}
