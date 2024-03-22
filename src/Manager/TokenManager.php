<?php
namespace App\Manager;

use App\Entity\AccessToken;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;

class TokenManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generateToken(): AccessToken
    {
        $client = new Client([
            'base_uri' => 'https://staging.billing-easy.net/shap/api/v1/merchant/',
        ]);
        $data = array(
            'api_id' => 'e80390f1f2f4436',
            'api_secret' => 'ade3c8-d784d4-99e989-c4ff4e-731f31'
        );
        $data_string = json_encode($data);

        $res = $client->post('auth', [
            'body' => $data_string,
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
        $accesstoken = json_decode($res->getBody()->getContents());
        $token = new AccessToken();
        $token->setValue($accesstoken->access_token); // Generate a random token value
        $token->setExpiresAt(new DateTimeImmutable('+2 hours')); // Set expiration time to 2 hours
        $this->entityManager->persist($token);
        $this->entityManager->flush();
        return $token;
    }

    public function getTokenByValue(string $value): ?AccessToken
    {
        return $this->entityManager->getRepository(AccessToken::class)->findOneBy(['value' => $value]);
    }
}