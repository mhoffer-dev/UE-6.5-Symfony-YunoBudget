<?php

namespace App\Tests\Controller;

use App\Entity\MoyenPaiement;
use App\Repository\MoyenPaiementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MoyenPaiementControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<MoyenPaiement> $moyenPaiementRepository */
    private EntityRepository $moyenPaiementRepository;
    private string $path = '/moyen/paiement/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->moyenPaiementRepository = $this->manager->getRepository(MoyenPaiement::class);

        foreach ($this->moyenPaiementRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('MoyenPaiement index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'moyen_paiement[nom]' => 'Testing',
            'moyen_paiement[numeroMasque]' => 'Testing',
            'moyen_paiement[type]' => 'Testing',
            'moyen_paiement[libelleBanque]' => 'Testing',
            'moyen_paiement[utilisateur]' => 'Testing',
        ]);

        self::assertResponseRedirects('/moyen/paiement');

        self::assertSame(1, $this->moyenPaiementRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new MoyenPaiement();
        $fixture->setNom('My Title');
        $fixture->setNumeroMasque('My Title');
        $fixture->setType('My Title');
        $fixture->setLibelleBanque('My Title');
        $fixture->setUtilisateur('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('MoyenPaiement');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new MoyenPaiement();
        $fixture->setNom('Value');
        $fixture->setNumeroMasque('Value');
        $fixture->setType('Value');
        $fixture->setLibelleBanque('Value');
        $fixture->setUtilisateur('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'moyen_paiement[nom]' => 'Something New',
            'moyen_paiement[numeroMasque]' => 'Something New',
            'moyen_paiement[type]' => 'Something New',
            'moyen_paiement[libelleBanque]' => 'Something New',
            'moyen_paiement[utilisateur]' => 'Something New',
        ]);

        self::assertResponseRedirects('/moyen/paiement');

        $fixture = $this->moyenPaiementRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getNumeroMasque());
        self::assertSame('Something New', $fixture[0]->getType());
        self::assertSame('Something New', $fixture[0]->getLibelleBanque());
        self::assertSame('Something New', $fixture[0]->getUtilisateur());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new MoyenPaiement();
        $fixture->setNom('Value');
        $fixture->setNumeroMasque('Value');
        $fixture->setType('Value');
        $fixture->setLibelleBanque('Value');
        $fixture->setUtilisateur('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/moyen/paiement');
        self::assertSame(0, $this->moyenPaiementRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
