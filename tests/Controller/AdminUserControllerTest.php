<?php

namespace App\Test\Controller;

use App\Entity\AdminUser;
use App\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminUserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private AdminUserRepository $repository;
    private string $path = '/admin/user/';
    private EntityManagerInterface $manager  ;

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
    }

    public function testIndexWhileConnected(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('AdminUser index');

        self::assertSame( 1, count($this->repository->findAll()));
        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testIndexNotConnected(): void
    {
        $this->client->request('GET', '/logout');
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(302);
        self::assertPageTitleContains('Redirecting to /');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNewWhileConnected(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'admin_user[username]' => 'Testing',
            'admin_user[email]' => 'Testing',
            'admin_user[plainPassword]' => 'Testing',
        ]);

        self::assertResponseRedirects('/admin/user/');

        self::assertSame( $originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testNewNotConnected(): void
    {
        $this->client->request('GET', '/logout');
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(302);

        self::assertPageTitleContains('Redirecting to /');
        self::assertResponseRedirects('/');
    }

    public function testShowWhileConnected(): void
    {
        $fixture = new AdminUser();
        $fixture->setUsername('username');
        $fixture->setEmail('email@email.com');
        $fixture->setPlainPassword('Password');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        // self::assertPageTitleContains('AdminUser');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testShowNotConnected(): void
    {
        $this->client->request('GET', '/logout');

        $fixture = new AdminUser();
        $fixture->setUsername('username');
        $fixture->setEmail('email@email.com');
        $fixture->setPlainPassword('Password');
        
        $this->manager->persist($fixture);
        $this->manager->flush();
        
        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(302);
        self::assertPageTitleContains('Redirecting to /');
    }

    public function testEditWhileConnected(): void
    {
        $fixture = new AdminUser();
        $fixture->setUsername('userCreate');
        $fixture->setEmail('create@mail.fr');
        $fixture->setPlainPassword('Password');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Save', [
            'admin_user[username]' => 'Testing',
            'admin_user[email]' => 'Testing',
            'admin_user[plainPassword]' => 'Testing',
        ]);

        self::assertResponseRedirects('/admin/user/');

        $fixture = $this->repository->findAll();

        self::assertSame('Testing', $fixture[1]->getUsername());
        self::assertSame('Testing', $fixture[1]->getEmail());
    }

    public function testEditNotConnected(): void
    {
        $this->client->request('GET', '/logout');

        $fixture = new AdminUser();
        $fixture->setUsername('userCreate');
        $fixture->setEmail('create@mail.fr');
        $fixture->setPlainPassword('Password');
        
        $this->manager->persist($fixture);
        $this->manager->flush();
        
        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));
        
        self::assertResponseStatusCodeSame(302);
        self::assertPageTitleContains('Redirecting to /');

        $fixture = $this->repository->findAll();

        self::assertResponseStatusCodeSame(302);
    }

    public function testRemoveWhileConnected(): void
    {
        $originalNumObjectsInRepository = 1;

        $fixture = new AdminUser();
        $fixture->setUsername('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPlainPassword('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame(2, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/admin/user/');
    }

    public function testRemoveNotConnected(): void
    {
        $this->client->request('GET', '/logout');
        
        $fixture = new AdminUser();
        $fixture->setUsername('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPlainPassword('My Title');
        
        $this->manager->flush();
        
        
        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(302);
        self::assertPageTitleContains('Redirecting to /');
    }
}
