<?php

namespace App\Tests\Controller;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TransactionControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Transaction> $transactionRepository */
    private EntityRepository $transactionRepository;
    private string $path = '/transaction/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->transactionRepository = $this->manager->getRepository(Transaction::class);

        foreach ($this->transactionRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Transaction index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'transaction[montant]' => 'Testing',
            'transaction[dateTransaction]' => 'Testing',
            'transaction[libelleTransaction]' => 'Testing',
            'transaction[utilisateur]' => 'Testing',
            'transaction[categorie]' => 'Testing',
            'transaction[moyenPaiement]' => 'Testing',
        ]);

        self::assertResponseRedirects('/transaction');

        self::assertSame(1, $this->transactionRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Transaction();
        $fixture->setMontant('My Title');
        $fixture->setDateTransaction('My Title');
        $fixture->setLibelleTransaction('My Title');
        $fixture->setUtilisateur('My Title');
        $fixture->setCategorie('My Title');
        $fixture->setMoyenPaiement('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Transaction');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Transaction();
        $fixture->setMontant('Value');
        $fixture->setDateTransaction('Value');
        $fixture->setLibelleTransaction('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setCategorie('Value');
        $fixture->setMoyenPaiement('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'transaction[montant]' => 'Something New',
            'transaction[dateTransaction]' => 'Something New',
            'transaction[libelleTransaction]' => 'Something New',
            'transaction[utilisateur]' => 'Something New',
            'transaction[categorie]' => 'Something New',
            'transaction[moyenPaiement]' => 'Something New',
        ]);

        self::assertResponseRedirects('/transaction');

        $fixture = $this->transactionRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getMontant());
        self::assertSame('Something New', $fixture[0]->getDateTransaction());
        self::assertSame('Something New', $fixture[0]->getLibelleTransaction());
        self::assertSame('Something New', $fixture[0]->getUtilisateur());
        self::assertSame('Something New', $fixture[0]->getCategorie());
        self::assertSame('Something New', $fixture[0]->getMoyenPaiement());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Transaction();
        $fixture->setMontant('Value');
        $fixture->setDateTransaction('Value');
        $fixture->setLibelleTransaction('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setCategorie('Value');
        $fixture->setMoyenPaiement('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/transaction');
        self::assertSame(0, $this->transactionRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
