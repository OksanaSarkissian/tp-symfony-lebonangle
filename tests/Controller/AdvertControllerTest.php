<?php

namespace App\Test\Controller;

use DateTime;
use App\Entity\Advert;
use DateTimeImmutable;
use App\Entity\AdminUser;
use App\Repository\AdvertRepository;
use App\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdvertControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private AdminUserRepository $repository;
    private AdvertRepository $advertRepository;
    private string $path = '/advert/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(AdminUser::class);
        $testUser = $this->repository->findOneBy(['username' => 'admin']); 
        $this->client->loginUser($testUser);
       
        foreach ($this->repository->findAll() as $object) {
            if ($object !== $testUser) {
                $this->manager->remove($object);
            }
        }
        $this->advertRepository = static::getContainer()->get('doctrine')->getRepository(Advert::class);

        foreach ($this->advertRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndexWhileConnected(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Advert index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNewWhileConnected(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'advert[title]' => 'Testing',
            'advert[context]' => 'Testing',
            'advert[author]' => 'Testing',
            'advert[email]' => 'Testing',
            'advert[price]' => 10,
            'advert[category]' => 2,
        ]);

        self::assertResponseRedirects('/advert');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
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
        $fixture->setCategory(null);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Advert');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEditWhileConnected(): void
    {
        
        $fixture = new Advert();
        $fixture->setTitle('My Title');
        $fixture->setContext('My Title');
        $fixture->setAuthor('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPrice(10);
        $fixture->setCreatedAt( (new DateTimeImmutable()));
        $fixture->setPublishedAt(null);
        $fixture->setCategory(null);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'advert[title]' => 'Something New',
            'advert[context]' => 'Something New',
            'advert[author]' => 'Something New',
            'advert[email]' => 'Something New',
            'advert[price]' => 10,
            'advert[category]' =>2,
        ]);

        self::assertResponseRedirects('/advert');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getContext());
        self::assertSame('Something New', $fixture[0]->getAuthor());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame(10, $fixture[0]->getPrice());
        self::assertSame('Something New', $fixture[0]->getCategory());
    }

    public function testRemoveWhileConnected(): void
    {
        

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Advert();
        $fixture->setTitle('My Title');
        $fixture->setContext('My Title');
        $fixture->setAuthor('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPrice(10);
        $fixture->setCreatedAt( (new DateTimeImmutable()));
        $fixture->setPublishedAt(null);
        $fixture->setCategory(null);

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/advert');
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

        $this->client->submitForm('Save', [
            'advert[title]' => 'Testing',
            'advert[context]' => 'Testing',
            'advert[author]' => 'Testing',
            'advert[email]' => 'Testing',
            'advert[price]' => 10,
            'advert[category]' => 2,
        ]);
        
        self::assertResponseStatusCodeSame(303);
        self::assertResponseRedirects('/advert');
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
        $fixture->setCategory(null);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Advert');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEditNotConnected(): void
    {
        
        $fixture = new Advert();
        $fixture->setTitle('My Title');
        $fixture->setContext('My Title');
        $fixture->setAuthor('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPrice(10);
        $fixture->setCreatedAt( (new DateTimeImmutable()));
        $fixture->setPublishedAt(null);
        $fixture->setCategory(null);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'advert[title]' => 'Something New',
            'advert[context]' => 'Something New',
            'advert[author]' => 'Something New',
            'advert[email]' => 'Something New',
            'advert[price]' => 10,
            'advert[category]' =>2,
        ]);

        self::assertResponseRedirects('/advert');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getContext());
        self::assertSame('Something New', $fixture[0]->getAuthor());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getPrice());
        self::assertSame('Something New', $fixture[0]->getCategory());
    }

    public function testRemoveNotConnected(): void
    {
        

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Advert();
        $fixture->setTitle('My Title');
        $fixture->setContext('My Title');
        $fixture->setAuthor('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPrice(10);
        $fixture->setCreatedAt( (new DateTimeImmutable()));
        $fixture->setPublishedAt(null);
        $fixture->setCategory(null);

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/advert');
    }
}
