<?php

namespace App\Tests\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CategorieControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Categorie> $categorieRepository */
    private EntityRepository $categorieRepository;
    private string $path = '/categorie/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->categorieRepository = $this->manager->getRepository(Categorie::class);

        foreach ($this->categorieRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Categorie index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'categorie[nom]' => 'Testing',
            'categorie[type]' => 'Testing',
            'categorie[couleur]' => 'Testing',
            'categorie[utilisateur]' => 'Testing',
        ]);

        self::assertResponseRedirects('/categorie');

        self::assertSame(1, $this->categorieRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Categorie();
        $fixture->setNom('My Title');
        $fixture->setType('My Title');
        $fixture->setCouleur('My Title');
        $fixture->setUtilisateur('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Categorie');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Categorie();
        $fixture->setNom('Value');
        $fixture->setType('Value');
        $fixture->setCouleur('Value');
        $fixture->setUtilisateur('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'categorie[nom]' => 'Something New',
            'categorie[type]' => 'Something New',
            'categorie[couleur]' => 'Something New',
            'categorie[utilisateur]' => 'Something New',
        ]);

        self::assertResponseRedirects('/categorie');

        $fixture = $this->categorieRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getType());
        self::assertSame('Something New', $fixture[0]->getCouleur());
        self::assertSame('Something New', $fixture[0]->getUtilisateur());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Categorie();
        $fixture->setNom('Value');
        $fixture->setType('Value');
        $fixture->setCouleur('Value');
        $fixture->setUtilisateur('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/categorie');
        self::assertSame(0, $this->categorieRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
