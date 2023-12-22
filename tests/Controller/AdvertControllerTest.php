<?php

namespace App\Test\Controller;

use App\Repository\CategoryRepository;
use DateTime;
use App\Entity\Advert;
use DateTimeImmutable;
use App\Entity\AdminUser;
use App\Entity\Category;
use App\Repository\AdvertRepository;
use App\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdvertControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private AdminUserRepository $adminUserRepository;
    private AdvertRepository $repository;
    private CategoryRepository $categoryRepository;
    private Category $testCategory;
    private string $path = '/advert/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->adminUserRepository = static::getContainer()->get('doctrine')->getRepository(AdminUser::class);
        $testUser = $this->adminUserRepository->findOneBy(['username' => 'admin']); 
        
        $this->client->loginUser($testUser);

        $this->categoryRepository = static::getContainer()->get('doctrine')->getRepository(Category::class);
        $this->testCategory = $this->categoryRepository->findOneBy(['name' => 'testing']);

        
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Advert::class);
        
        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndexWhileConnected(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        // self::assertPageTitleContains('Advert index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNewWhileConnected(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        
        $this->client->request('GET', sprintf('%snew', $this->path));

        
        $this->client->submitForm('Save', [
            'advert[title]' => 'Testing',
            'advert[context]' => 'Testing',
            'advert[author]' => 'Testing',
            'advert[email]' => 'Testing',
            'advert[price]' => 10,
            'advert[category]' => $this->testCategory->getId(),
        ]);
        
        self::assertResponseStatusCodeSame(303);
        // self::assertResponseRedirects('/advert');

        // self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShowWhileConnected(): void
    {
        
        $fixture = new Advert();
        $fixture->setTitle('My Title');
        $fixture->setContext('My Title');
        $fixture->setAuthor('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPrice(10);
        $fixture->setCreatedAt( (new DateTimeImmutable()));
        $fixture->setPublishedAt(null);
        $fixture->setCategory($this->testCategory);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Advert');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testIndexNotConnected(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Advert index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNewNotConnected(): void
    {        
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'advert[title]' => 'Testing',
            'advert[context]' => 'Testing',
            'advert[author]' => 'Testing',
            'advert[email]' => 'Testing',
            'advert[price]' => 10,
            'advert[category]' => $this->testCategory->getId(),
        ]);
        
        // self::assertResponseRedirects('/advert');
    }

    public function testShowNotConnected(): void
    {
        
        $fixture = new Advert();
        $fixture->setTitle('My Title');
        $fixture->setContext('My Title');
        $fixture->setAuthor('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPrice(10);
        $fixture->setCreatedAt( (new DateTimeImmutable()));
        $fixture->setPublishedAt(null);
        $fixture->setCategory($this->testCategory);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Advert');

        // Use assertions to check that the properties are properly displayed.
    }

}
