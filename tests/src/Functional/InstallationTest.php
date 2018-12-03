<?php

namespace Drupal\Tests\contenta_jsonapi\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use GuzzleHttp\Client;

/**
 * Tests that installation finished correctly and known resources are available.
 *
 * @group ContentaInstaller
 */
class InstallationTest extends BrowserTestBase {

  public $profile = 'contenta_jsonapi';

  /**
   * @var Client
   */
  private $httpClient;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Set up a HTTP client that accepts relative URLs.
    $this->httpClient = new Client(['http_errors' => FALSE]);
  }

  public function testLandingPage() {
    $url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
    debug($url);
    $this->getSession()->visit($url);
    $this->assertEquals(200, $this->getSession()->getStatusCode());
  }

  public function testKnownResources() {
    $url = Url::fromRoute('jsonapi.resource_list', [], ['absolute' => TRUE])
      ->toString();
    debug($url);
    $response = $this->httpClient->request('GET', $url);
    $body = $response->getBody()->getContents();
    $output = Json::decode($body);
    $resources = array_keys($output['links']);
    $expected_resources = [
      'commentTypes',
      'files',
      'imageStyles',
      'images',
      'articles',
      'pages',
      'recipes',
      'tutorials',
      'contentTypes',
      'categories',
      'tags',
      'vocabularies',
      'roles',
      'users',
    ];
    array_walk(
      $expected_resources, function ($resource) use ($resources) {
        $this->assertContains($resource, $resources);
      }
    );
  }

  public function testRpcMethod() {
    $url = Url::fromRoute('jsonrpc.handler', [], ['absolute' => TRUE])->toString();
    debug($url);
    $response = $this->httpClient->request(
      'GET',
      $url,
      [
        'query' => [
          'query' => '{"jsonrpc":"2.0","method":"jsonapi.metadata","id":"cms-meta"}'
        ],
      ]);
    $body = $response->getBody()->getContents();
    $output = Json::decode($body);
    $this->assertEquals('/api', $output['result']['prefix']);
    $this->assertEquals('/api', $output['result']['openApi']['basePath']);
    $response = $this->httpClient->request(
      'POST',
      $url,
      ['body' => '{"jsonrpc":"2.0","method":"jsonapi.metadata","id":"cms-meta"}']
    );
    $body = $response->getBody()->getContents();
    $output = Json::decode($body);
    $this->assertEquals('/api', $output['result']['prefix']);
    $this->assertEquals('/api', $output['result']['openApi']['basePath']);
  }

  public function testJsonApiEntryPoint() {
    $url = Url::fromRoute('jsonapi.resource_list', [], ['absolute' => TRUE])
      ->toString();
    debug($url);
    $response = $this->httpClient->request(
      'GET',
      $url,
      [
        'query' => [
          'query' => '{"jsonrpc":"2.0","method":"jsonapi.metadata","id":"cms-meta"}'
        ],
        'headers' => ['Accept' => 'application/vnd.api+json'],
      ]);
    $this->assertSame(200, $response->getStatusCode());
    $body = $response->getBody()->getContents();
    $output = Json::decode($body);
    $this->assertArrayHasKey('self', $output);
    $this->assertArrayHasKey('node--recipe', $output);
  }

  public function testOpenApi() {
    $url = Url::fromRoute('contenta_enhancements.api', [], ['absolute' => TRUE])->toString();
    debug($url);
    $this->getSession()->visit($url);
    $page = $this->getSession()->getPage();
    debug($page->getText());
    $this->assertNotEmpty($page->find('css', 'a[href="#tag/Content-Recipe"]'));
  }

}
