<?php

namespace PierreGranger\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PierreGranger\ApidaeException;
use PierreGranger\ApidaeMembres;

class ApidaeMembresTest extends TestCase
{
    private const PROJET_ID = '370';

    private const API_KEY = 'testApiKey123';

    public function testConstructSetsCredentialsAndTimeout(): void
    {
        $membres = new ApidaeMembres($this->baseParams());

        $this->assertSame(self::PROJET_ID, $this->getProtectedProperty($membres, 'projet_consultation_projetId'));
        $this->assertSame(self::API_KEY, $this->getProtectedProperty($membres, 'projet_consultation_apiKey'));
        $this->assertSame(30, $this->getProtectedProperty($membres, 'timeout'));
    }

    #[DataProvider('invalidConstructorParamsProvider')]
    public function testConstructThrowsOnInvalidParams(array $params, string $message): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($message);

        new ApidaeMembres($params);
    }

    public static function invalidConstructorParamsProvider(): array
    {
        return [
            'missing projetId' => [
                ['projet_consultation_apiKey' => self::API_KEY],
                'missing projet_consultation_projetId',
            ],
            'invalid projetId' => [
                [
                    'projet_consultation_projetId' => 'abc',
                    'projet_consultation_apiKey' => self::API_KEY,
                ],
                'missing projet_consultation_projetId',
            ],
            'missing apiKey' => [
                ['projet_consultation_projetId' => self::PROJET_ID],
                'missing projet_consultation_apiKey',
            ],
            'invalid apiKey' => [
                [
                    'projet_consultation_projetId' => self::PROJET_ID,
                    'projet_consultation_apiKey' => 'key with spaces',
                ],
                'missing projet_consultation_apiKey',
            ],
        ];
    }

    public function testGetMembresBuildsPostRequest(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '[{"id":1}]',
        ]);

        $filter = ['idProjet' => 370];
        $result = $membres->getMembres($filter);

        $this->assertSame([['id' => 1]], $result);
        $this->assertSame('/api/v002/membre/get-membres/', $membres->lastRequestPath);
        $this->assertSame(1, $membres->lastRequestParams['POST']);

        $query = json_decode(
            substr($membres->lastRequestParams['POSTFIELDS'], strlen('query=')),
            true
        );
        $this->assertSame(self::PROJET_ID, $query['projetId']);
        $this->assertSame(self::API_KEY, $query['apiKey']);
        $this->assertSame($filter, (array) $query['filter']);
    }

    public function testGetMembresEncodesArrayResponseFields(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '[]',
        ]);

        $membres->getMembres(['idProjet' => 370], ['PROJETS']);

        $query = json_decode(
            substr($membres->lastRequestParams['POSTFIELDS'], strlen('query=')),
            true
        );
        $this->assertSame(['PROJETS'], $query['responseFields']);
    }

    public function testGetMembresKeepsStringResponseFields(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '[]',
        ]);

        $membres->getMembres(['idProjet' => 370], '["UTILISATEURS"]');

        $query = json_decode(
            substr($membres->lastRequestParams['POSTFIELDS'], strlen('query=')),
            true
        );
        $this->assertSame(['UTILISATEURS'], $query['responseFields']);
    }

    public function testGetFilleulsBuildsDefaultFilter(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '[]',
        ]);

        $membres->getFilleuls(1157);

        $query = json_decode(
            substr($membres->lastRequestParams['POSTFIELDS'], strlen('query=')),
            true
        );
        $this->assertSame(1157, $query['filter']['idParrain']);
        $this->assertSame(['Contributeur Généraliste'], $query['filter']['types']);
        $this->assertSame(['UTILISATEURS'], $query['responseFields']);
    }

    public function testGetFilleulsAcceptsCustomTypes(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '[]',
        ]);

        $membres->getFilleuls(1157, ['Type A', 'Type B']);

        $query = json_decode(
            substr($membres->lastRequestParams['POSTFIELDS'], strlen('query=')),
            true
        );
        $this->assertSame(['Type A', 'Type B'], $query['filter']['types']);
    }

    public function testGetUserByIdBuildsGetRequest(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '{"id":42,"mail":"user@example.com"}',
        ]);

        $result = $membres->getUserById(42);

        $this->assertSame(['id' => 42, 'mail' => 'user@example.com'], $result);
        $this->assertSame(
            '/api/v002/utilisateur/get-by-id/42?projetId=' . self::PROJET_ID . '&apiKey=' . self::API_KEY,
            $membres->lastRequestPath
        );
        $this->assertSame([], $membres->lastRequestParams);
    }

    public function testGetUserDispatchesByType(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '{"id":42}',
        ]);

        $byId = $membres->getUser(42);
        $this->assertSame(['id' => 42], $byId);

        $membres->mockRequestResult = [
            'code' => 200,
            'body' => '{"id":7,"mail":"user@example.com"}',
        ];
        $byMail = $membres->getUser('user@example.com');
        $this->assertSame(['id' => 7, 'mail' => 'user@example.com'], $byMail);

        $this->assertFalse($membres->getUser('invalid-login'));
    }

    public function testGetUtilisateurDelegatesToGetUser(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '{"id":99}',
        ]);

        $this->assertSame(['id' => 99], $membres->getUtilisateur(99));
    }

    public function testGetUserByMailThrowsOnInvalidEmail(): void
    {
        $membres = $this->createMembresWithMock();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid mail_user for getUserByMail');

        $membres->getUserByMail('not-an-email');
    }

    public function testGetUserByMailBuildsGetRequest(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '{"id":7}',
        ]);

        $mail = 'user@example.com';
        $result = $membres->getUserByMail($mail);

        $this->assertSame(['id' => 7], $result);
        $this->assertStringStartsWith('/api/v002/utilisateur/get-by-mail/' . $mail . '?', $membres->lastRequestPath);
    }

    public function testGetUsersByMemberBuildsGetRequest(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '[{"id":1}]',
        ]);

        $result = $membres->getUsersByMember(1157);

        $this->assertSame([['id' => 1]], $result);
        $this->assertSame(
            '/api/v002/utilisateur/get-by-membre/1157?projetId=' . self::PROJET_ID . '&apiKey=' . self::API_KEY,
            $membres->lastRequestPath
        );
    }

    public function testGetMembreByIdEncodesResponseFields(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '{"id":1157}',
        ]);

        $result = $membres->getMembreById(1157, ['PROJETS']);

        $this->assertSame(['id' => 1157], $result);
        $this->assertStringContainsString('responseFields=%5B%22PROJETS%22%5D', $membres->lastRequestPath);
    }

    public function testGetMembreDelegatesToGetMembreById(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '{"id":1157}',
        ]);

        $this->assertSame(['id' => 1157], $membres->getMembre(1157));
    }

    public function testGetAllUtilisateursTemporarilyIncreasesTimeout(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '[]',
        ]);

        $membres->getAllUtilisateurs();

        $this->assertSame(15, $this->getProtectedProperty($membres, 'timeout'));
        $this->assertSame(
            '/api/v002/utilisateur/get-all-utilisateurs/?projetId=' . self::PROJET_ID . '&apiKey=' . self::API_KEY,
            $membres->lastRequestPath
        );
    }

    public function testApidaeCurlReturnsFalseOn204(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 204,
            'body' => '',
        ]);

        $this->assertFalse($membres->getUserById(42));
    }

    public function testApidaeCurlThrowsOnInvalidHttpCode(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 404,
            'body' => 'not found',
        ]);

        $this->expectException(ApidaeException::class);
        $this->expectExceptionCode(ApidaeException::INVALID_HTTPCODE);
        $this->expectExceptionMessage('utilisateur/get-by-id');

        $membres->getUserById(42);
    }

    public function testDroitsReturnsTrueWhenMembreIsAllowed(): void
    {
        $membres = $this->createMembresWithMock();

        $utilisateur = ['id' => 1, 'membre' => ['id' => 1157]];

        $this->assertTrue($membres->droits($utilisateur, ['membres' => [1157, 2000]]));
    }

    public function testDroitsReturnsFalseWhenMembreIsNotAllowed(): void
    {
        $membres = $this->createMembresWithMock();

        $utilisateur = ['id' => 1, 'membre' => ['id' => 999]];

        $this->assertFalse($membres->droits($utilisateur, ['membres' => [1157]]));
    }

    public function testDroitsChecksFilleuls(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '[1157,2000]',
        ]);

        $utilisateur = ['id' => 1, 'membre' => ['id' => 1157]];

        $this->assertTrue($membres->droits($utilisateur, ['filleuls' => 1]));
        $this->assertFalse($membres->droits(['id' => 1, 'membre' => ['id' => 999]], ['filleuls' => 1]));
    }

    public function testDroitsChecksPermissions(): void
    {
        $membres = $this->createMembresWithMock([
            'code' => 200,
            'body' => '{"id":1,"permissions":["ADMIN","EDIT"]}',
        ]);

        $utilisateur = ['id' => 1, 'membre' => ['id' => 1157]];

        $this->assertTrue($membres->droits($utilisateur, ['permissions' => ['ADMIN']]));
        $this->assertFalse($membres->droits($utilisateur, ['permissions' => ['DELETE']]));
    }

    private function createMembresWithMock(?array $mockRequestResult = null): ApidaeMembresTestable
    {
        $membres = new ApidaeMembresTestable($this->baseParams());
        $membres->mockRequestResult = $mockRequestResult;

        return $membres;
    }

    private function baseParams(): array
    {
        return [
            'projet_consultation_projetId' => self::PROJET_ID,
            'projet_consultation_apiKey' => self::API_KEY,
            'timer' => false,
        ];
    }

    private function getProtectedProperty(object $object, string $property): mixed
    {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setAccessible(true);

        return $reflection->getValue($object);
    }
}
