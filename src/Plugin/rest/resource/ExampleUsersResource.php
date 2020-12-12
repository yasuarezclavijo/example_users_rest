<?php

namespace Drupal\example_users_rest\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Component\Serialization\Json;

/**
 * Example Users Resource.
 *
 * @RestResource(
 *   id = "example_users",
 *   label = @Translation("Example Users Resource"),
 *   uri_paths = {
 *     "canonical" = "/example-crud/data/{id}",
 *   }
 * )
 */
class ExampleUsersResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new ConfigRestResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->connection = \Drupal::database();
  }

  /**
   * Get records created in example_users table.
   *
   * @return Drupal\rest\ResourceResponse
   *   Response.
   */
  private function getRecords($id) {
    try {
      $query = $this->connection->select('example_users', 'eu');
      $query->fields('eu', [
        'id',
        'name',
        'identification',
        'birthdate',
        'position',
        'state',
      ]);
      if (is_numeric($id) and $id != "all") {
        $query->condition('id', $id, "=");
      }
      $result = $query->execute();
      $records = $result->fetchAll();
      $responseRecords = [];
      foreach ($records as $record) {
        $record->birthdate = date('Y-m-d', $record->birthdate);
        $responseRecords[] = (array) $record;
      }
      return $responseRecords;
    }
    catch (\Exception $e) {
      // Log the exception to watchdog.
      \Drupal::logger('example_users_rest')->error($e->getMessage());
    }
  }

  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Response.
   */
  public function get($id) {
    $responseRecords = $this->getRecords($id);
    $response = new ResourceResponse($responseRecords);
    $response->addCacheableDependency($responseRecords);
    return $response;
  }

  /**
   * Update function.
   *
   * @param int $id
   *   Identificator record.
   * @param object $data
   *   New data for record.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Response.
   */
  public function patch($id, $data) {
    $data = json_decode(Json::encode($data));
    $data->birthdate = strtotime($data->birthdate);
    try {
      $responseRecords = $this->getRecords($id);
      if (count($responseRecords) == 0) {
        $response = new ResourceResponse(NULL, 404);
      }
      else {
        $numUpdated = $this->connection->update('example_users')
          ->fields((array) $data)
          ->condition('id', $id, '=')
          ->execute();
        if ($numUpdated >= 1) {
          $responseRecords = $this->getRecords($id);
        }
        $response = new ResourceResponse($responseRecords);
      }
    }
    catch (\Exception $e) {
      // Log the exception to watchdog.
      \Drupal::logger('example_users_rest')->error($e->getMessage());
      $response = new ResourceResponse($e->getMessage(), 500);
    }

    $response->addCacheableDependency($responseRecords);
    return $response;
  }

}
